<?php
/**
 * Azeu Water Station - Public Receipt Page
 * Accessible via token (?token=xxx)
 */
require_once 'config/database.php';
require_once 'config/functions.php';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: login.php');
    exit;
}

$token = sanitize($_GET['token']);

// Fetch order by receipt token
$order = db_fetch("SELECT o.*, u.full_name as customer_name, u.email, u.phone 
                   FROM orders o 
                   JOIN users u ON o.customer_id = u.id 
                   WHERE o.receipt_token = ?", [$token]);

if (!$order) {
    header('Location: errors/404.php');
    exit;
}

// Fetch order items
$items = db_fetch_all("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$station_address = get_setting('station_address') ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?php echo $order['id']; ?> - <?php echo htmlspecialchars($station_name); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/receipt.css">
</head>
<body>
    <div class="receipt-page">
        <div class="receipt-container" data-order-id="<?php echo $order['id']; ?>">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <div class="receipt-logo">
                    <span class="material-icons">water_drop</span>
                </div>
                <h1 class="receipt-station-name"><?php echo htmlspecialchars($station_name); ?></h1>
                <?php if ($station_address): ?>
                    <p class="receipt-station-address"><?php echo htmlspecialchars($station_address); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Receipt Body -->
            <div class="receipt-body">
                <!-- Order Information -->
                <div class="receipt-section">
                    <h3 class="receipt-section-title">Order Information</h3>
                    <div class="receipt-info-grid">
                        <div class="receipt-info-item">
                            <span class="receipt-info-label">Order ID</span>
                            <span class="receipt-info-value">#<?php echo $order['id']; ?></span>
                        </div>
                        <div class="receipt-info-item">
                            <span class="receipt-info-label">Status</span>
                            <span class="receipt-status <?php echo get_status_badge_class($order['status']); ?>">
                                <?php echo get_status_label($order['status']); ?>
                            </span>
                        </div>
                        <div class="receipt-info-item">
                            <span class="receipt-info-label">Order Date</span>
                            <span class="receipt-info-value"><?php echo format_date($order['order_date']); ?></span>
                        </div>
                        <div class="receipt-info-item">
                            <span class="receipt-info-label">Payment Type</span>
                            <span class="receipt-info-value"><?php echo strtoupper($order['payment_type']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="receipt-section">
                    <h3 class="receipt-section-title">Customer Information</h3>
                    <div class="receipt-info-grid">
                        <div class="receipt-info-item">
                            <span class="receipt-info-label">Name</span>
                            <span class="receipt-info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                        </div>
                        <div class="receipt-info-item">
                            <span class="receipt-info-label">Phone</span>
                            <span class="receipt-info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                        </div>
                    </div>
                    <?php if ($order['delivery_address']): ?>
                        <div class="receipt-info-item" style="margin-top: 12px;">
                            <span class="receipt-info-label">Delivery Address</span>
                            <span class="receipt-info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Order Items -->
                <div class="receipt-section">
                    <h3 class="receipt-section-title">Order Items</h3>
                    <table class="receipt-items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo format_currency($item['item_price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo format_currency($item['subtotal']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Summary -->
                    <div class="receipt-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo format_currency($order['subtotal']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span><?php echo format_currency($order['delivery_fee']); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Amount</span>
                            <span><?php echo format_currency($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code -->
                <div class="receipt-qr">
                    <div id="qr-code"></div>
                    <p class="receipt-qr-label">Scan to view receipt</p>
                </div>
            </div>
            
            <!-- Receipt Actions -->
            <div class="receipt-actions">
                <button id="download-pdf" class="btn btn-primary">
                    <span class="material-icons">picture_as_pdf</span>
                    Download PDF
                </button>
                <button id="download-image" class="btn btn-outline">
                    <span class="material-icons">image</span>
                    Download Image
                </button>
                <button onclick="window.print()" class="btn btn-outline">
                    <span class="material-icons">print</span>
                    Print
                </button>
            </div>
            
            <!-- Receipt Footer -->
            <div class="receipt-footer">
                <p>Thank you for your order!</p>
                <p style="margin-top: 8px; font-size: 0.8rem;">This is an official receipt from <?php echo htmlspecialchars($station_name); ?></p>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/global.js"></script>
    <script src="assets/js/components.js"></script>
    <script src="assets/js/receipt.js"></script>
</body>
</html>
