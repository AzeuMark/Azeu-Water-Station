<?php
/**
 * ============================================================================
 * AZEU WATER STATION - CREATE ORDER API
 * ============================================================================
 * 
 * Purpose: Create a new order (customer only)
 * Method: POST
 * Role: CUSTOMER
 * 
 * Request Body (JSON):
 * {
 *   "items": [{"inventory_id": 1, "quantity": 2}, ...],
 *   "delivery_type": "delivery" | "pickup",
 *   "payment_type": "cod" | "pickup" | "online",
 *   "address_id": 1 (required if delivery_type = "delivery"),
 *   "order_notes": "optional notes"
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "order_id": 123,
 *   "receipt_token": "abc123..."
 * }
 * 
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

header('Content-Type: application/json');
session_start();



require_once __DIR__ . '/../../config/request_logger.php';

log_api_entry('orders/create');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

// Auth check
require_role([ROLE_CUSTOMER]);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$items = $input['items'] ?? [];
$delivery_type = sanitize($input['delivery_type'] ?? '');
$payment_type = sanitize($input['payment_type'] ?? '');
$address_id = intval($input['address_id'] ?? 0);
$order_notes = sanitize($input['order_notes'] ?? '');

// Validate input
if (empty($items) || !is_array($items)) {
    json_response(['success' => false, 'message' => 'No items specified'], 400);
}

if (!in_array($delivery_type, [DEL_DELIVERY, DEL_PICKUP])) {
    json_response(['success' => false, 'message' => 'Invalid delivery type'], 400);
}

if (!in_array($payment_type, [PAY_COD, PAY_PICKUP, PAY_ONLINE])) {
    json_response(['success' => false, 'message' => 'Invalid payment type'], 400);
}

// Validate address for delivery
if ($delivery_type === DEL_DELIVERY && $address_id <= 0) {
    json_response(['success' => false, 'message' => 'Delivery address is required'], 400);
}

try {
    $pdo->beginTransaction();
    
    $customer_id = $_SESSION['user_id'];
    
    // Check customer status
    $customer = db_fetch("SELECT status, cancellation_count FROM users WHERE id = ?", [$customer_id]);
    
    if ($customer['status'] === ACCOUNT_FLAGGED) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Your account is flagged. Please contact support'], 403);
    }
    
    // Check cancellation limit
    $max_cancellation = intval(get_setting('max_cancellation') ?? 5);
    if ($customer['cancellation_count'] >= $max_cancellation) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'You have reached the maximum cancellation limit for this month'], 403);
    }
    
    // Get delivery address
    $delivery_address = null;
    if ($delivery_type === DEL_DELIVERY) {
        $address = db_fetch("SELECT full_address FROM customer_addresses WHERE id = ? AND customer_id = ?", 
            [$address_id, $customer_id]);
        
        if (!$address) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Invalid delivery address'], 400);
        }
        
        $delivery_address = $address['full_address'];
    }
    
    // Calculate totals
    $subtotal = 0;
    $order_items_data = [];
    
    foreach ($items as $item) {
        $inventory_id = intval($item['inventory_id'] ?? 0);
        $quantity = intval($item['quantity'] ?? 0);
        
        if ($inventory_id <= 0 || $quantity <= 0) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Invalid item or quantity'], 400);
        }
        
        // Get inventory item
        $inv_item = db_fetch("SELECT * FROM inventory WHERE id = ? AND status = ?", 
            [$inventory_id, INV_ACTIVE]);
        
        if (!$inv_item) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Item not available: ' . $inventory_id], 400);
        }
        
        // Check stock
        if ($inv_item['stock_count'] < $quantity) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => $inv_item['item_name'] . ' is out of stock'], 400);
        }
        
        $item_subtotal = $inv_item['price'] * $quantity;
        $subtotal += $item_subtotal;
        
        $order_items_data[] = [
            'inventory_id' => $inventory_id,
            'item_name' => $inv_item['item_name'],
            'item_icon' => $inv_item['item_icon'],
            'item_price' => $inv_item['price'],
            'quantity' => $quantity,
            'subtotal' => $item_subtotal
        ];
    }
    
    // Get delivery fee
    $delivery_fee = 0;
    if ($delivery_type === DEL_DELIVERY) {
        $delivery_fee = floatval(get_setting('delivery_fee') ?? 50.00);
    }
    
    $total_amount = $subtotal + $delivery_fee;
    
    // Generate receipt token
    $receipt_token = generate_token(64);
    
    // Create order
    $order_id = db_insert(
        "INSERT INTO orders (customer_id, payment_type, delivery_type, delivery_address, 
         order_notes, delivery_fee, subtotal, total_amount, receipt_token, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$customer_id, $payment_type, $delivery_type, $delivery_address, $order_notes, 
         $delivery_fee, $subtotal, $total_amount, $receipt_token, STATUS_PENDING]
    );
    
    if (!$order_id) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Failed to create order'], 500);
    }
    
    // Insert order items (stock is deducted only when order is confirmed, not at placement)
    foreach ($order_items_data as $item_data) {
        db_insert(
            "INSERT INTO order_items (order_id, inventory_id, item_name, item_icon, 
             item_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$order_id, $item_data['inventory_id'], $item_data['item_name'], 
             $item_data['item_icon'], $item_data['item_price'], 
             $item_data['quantity'], $item_data['subtotal']]
        );
    }
    
    // Notify staff/admins about new order
    $staff_admins = db_fetch_all(
        "SELECT id FROM users WHERE role IN ('staff', 'admin', 'super_admin') AND status = 'active'"
    );
    
    foreach ($staff_admins as $admin) {
        create_notification(
            $admin['id'],
            'New Order Placed',
            "Order #$order_id has been placed by " . $_SESSION['full_name'],
            'order_placed',
            $order_id
        );
    }
    
    // =====================================================================
    // AUTO-CONFIRM: If setting is enabled, auto-confirm the order
    // =====================================================================
    $auto_confirmed = false;
    $auto_assigned_rider = null;
    
    if (get_setting('auto_confirm_orders') == '1') {
        db_update("UPDATE orders SET status = ? WHERE id = ?", [STATUS_CONFIRMED, $order_id]);
        $auto_confirmed = true;
        
        // Deduct stock for each item when auto-confirmed
        foreach ($order_items_data as $item_data) {
            db_update(
                "UPDATE inventory SET stock_count = GREATEST(0, stock_count - ?) WHERE id = ?",
                [$item_data['quantity'], $item_data['inventory_id']]
            );
            // Mark out of stock if stock hits 0
            db_update(
                "UPDATE inventory SET status = ? WHERE id = ? AND stock_count = 0",
                [INV_OUT_OF_STOCK, $item_data['inventory_id']]
            );
        }
        
        // Notify customer
        create_notification(
            $customer_id,
            'Order Auto-Confirmed',
            "Your order #$order_id has been automatically confirmed",
            'order_confirmed',
            $order_id
        );
        
        // =================================================================
        // AUTO-ASSIGN RIDER: If setting is enabled and it's a delivery order
        // =================================================================
        if (get_setting('auto_assign_rider') == '1' && $delivery_type === DEL_DELIVERY) {
            $rider = db_fetch(
                "SELECT u.id, u.full_name,
                 (SELECT COUNT(*) FROM orders WHERE rider_id = u.id AND status IN ('assigned', 'on_delivery')) as active_count
                 FROM users u 
                 WHERE u.role = 'rider' AND u.status = 'active' AND u.is_available = 1
                 ORDER BY active_count ASC LIMIT 1"
            );
            
            if ($rider) {
                db_update(
                    "UPDATE orders SET rider_id = ?, status = ? WHERE id = ?",
                    [$rider['id'], STATUS_ASSIGNED, $order_id]
                );
                $auto_assigned_rider = $rider['full_name'];
                
                // Notify rider
                create_notification(
                    $rider['id'],
                    'New Delivery Assigned',
                    "You have been auto-assigned to deliver Order #$order_id",
                    'order_assigned',
                    $order_id
                );
                
                // Notify customer
                create_notification(
                    $customer_id,
                    'Rider Assigned',
                    "A rider ({$rider['full_name']}) has been assigned to your order #$order_id",
                    'order_assigned',
                    $order_id
                );
            }
        }
    }
    
    $pdo->commit();
    
    logger_info("Order created successfully", ['order_id' => $order_id, 'total' => $total_amount, 'auto_confirmed' => $auto_confirmed]);
    
    $response = [
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'receipt_token' => $receipt_token,
        'total_amount' => $total_amount
    ];
    
    if ($auto_confirmed) {
        $response['message'] = 'Order placed and automatically confirmed!';
    }
    if ($auto_assigned_rider) {
        $response['message'] = 'Order placed, confirmed, and rider ' . $auto_assigned_rider . ' assigned!';
    }
    
    json_response($response);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logger_exception($e);
    json_response(['success' => false, 'message' => 'An error occurred. Please try again'], 500);
}
