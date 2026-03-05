# 🔄 Database Reset & Seed Script

## 📝 Overview

The `reset_database.php` script completely resets your database and populates it with test data for all user roles. This makes it easy to test and demonstrate all system features.

---

## ⚠️ WARNING

**THIS SCRIPT WILL DELETE ALL EXISTING DATA!**

Only use this script in development/testing environments. Never run this in production.

---

## 🚀 How to Use

### Method 1: Via Browser (Recommended)
1. Make sure XAMPP is running (Apache + MySQL)
2. Open your browser
3. Navigate to: `http://localhost/Station_A/reset_database.php`
4. Wait for the script to complete
5. You'll see a summary of all created data

### Method 2: Via Command Line
```powershell
cd "D:\Others\Azeu Codes\Station_A"
C:\xampp\php\php.exe reset_database.php
```

---

## 👥 Test Accounts Created

### 🔴 Super Admin
- **Username:** `admin`
- **Password:** `admin`
- **Features:** Full system access, can create other admins

### 🔴 Admin
- **Username:** `admin2`
- **Password:** `admin123`
- **Features:** Everything except creating other admins

### 🟡 Staff
- **Username:** `staff1`
- **Password:** `staff123`
- **Features:** Approve accounts, confirm orders, assign riders, view inventory (read-only)

### 🟢 Riders
**Rider 1:**
- **Username:** `rider1`
- **Password:** `rider123`
- **Status:** Active, Available
- **Has:** 2 assigned deliveries

**Rider 2:**
- **Username:** `rider2`
- **Password:** `rider123`
- **Status:** Active, Available
- **Has:** 1 completed delivery

### 🔵 Customers

**Customer 1 (Active):**
- **Username:** `customer1`
- **Password:** `customer123`
- **Status:** Active
- **Has:** 3 addresses, 5 orders (all statuses), notifications

**Customer 2 (Pending Approval):**
- **Username:** `customer2`
- **Password:** `customer123`
- **Status:** Pending (waiting for staff/admin approval)
- **Cannot:** Place orders until approved

**Customer 3 (Flagged):**
- **Username:** `customer3`
- **Password:** `customer123`
- **Status:** Flagged (exceeded cancellation limit)
- **Has:** 1 pending appeal, 1 address, 2 orders
- **Cannot:** Place new orders until appeal is approved

---

## 📦 Sample Data Created

### Inventory (13 items)
- 30L Water Refill (100 in stock) - ₱150.00
- 20L Water Refill (80 in stock) - ₱100.00
- 10L Water Refill (60 in stock) - ₱60.00
- 5L Water Refill (50 in stock) - ₱35.00
- 1L Bottled Water (200 in stock) - ₱20.00
- 500ml Bottled Water (150 in stock) - ₱12.00
- Bleach 1L (30 in stock) - ₱45.00
- Water Dispenser (5 in stock) - ₱2,500.00
- Water Container 30L (15 in stock) - ₱450.00
- Water Container 20L (20 in stock) - ₱350.00
- Ice Tube (40 in stock) - ₱25.00
- **Alkaline Water 1L (0 in stock) - ₱35.00** ⚠️ OUT OF STOCK
- Distilled Water 5L (25 in stock) - ₱50.00

### Orders (7 orders)
1. **Order #1** - Pending (customer1)
   - 2x 30L Water Refill
   - Total: ₱350.00

2. **Order #2** - Confirmed (customer1)
   - 1x 20L + 1x 10L Water Refill
   - Total: ₱210.00

3. **Order #3** - Assigned to rider1 (customer1)
   - 12x 1L Bottled Water
   - Total: ₱290.00

4. **Order #4** - On Delivery by rider1 (customer1)
   - 10x 5L Water Refill
   - Total: ₱400.00

5. **Order #5** - Delivered by rider2 (customer1)
   - 3x 30L Water Refill
   - Total: ₱500.00
   - Delivered 1 hour ago

6. **Order #6** - Ready for Pickup (customer3)
   - 2x 10L Water Refill
   - Total: ₱120.00

7. **Order #7** - Cancelled (customer3)
   - 2x 20L Water Refill
   - Total: ₱250.00
   - Reason: "Changed my mind"

### Customer Addresses (4 addresses)
- customer1: 3 addresses (Home, Office, Parents House)
- customer3: 1 address (Home)

### Notifications (15 notifications)
- Order confirmations
- Rider assignments
- Delivery updates
- Account status notifications
- Low stock alerts
- System notices

### Session Logs (10 entries)
- Login/logout history for all users
- 2 failed login attempts (security demo)

### Appeals (1 appeal)
- customer3 has a pending cancellation appeal

---

## 🎯 What You Can Test

### As Super Admin (`admin`/`admin`)
✅ View analytics and revenue charts
✅ Manage all accounts (create, edit, delete)
✅ Create other admin accounts
✅ Manage inventory (CRUD)
✅ View session logs
✅ Change system settings
✅ Review appeals
✅ View all orders and assign riders

### As Admin (`admin2`/`admin123`)
✅ Everything Super Admin can do EXCEPT:
❌ Create other admins
❌ Delete super admin account

### As Staff (`staff1`/`staff123`)
✅ Approve pending accounts (customer2)
✅ Confirm orders
✅ Assign riders to deliveries
✅ View inventory (read-only)
✅ Review appeals
✅ View rider statistics

### As Rider (`rider1` or `rider2`/`rider123`)
✅ View assigned deliveries
✅ Drag-and-drop to organize delivery priority
✅ Mark orders as "On Delivery"
✅ Mark orders as "Delivered"
✅ Toggle availability (Available/Unavailable)
✅ View delivery history and statistics

### As Customer (`customer1`/`customer123`)
✅ Place new orders (delivery or pickup)
✅ Select from 3 saved addresses
✅ View order history
✅ Track active deliveries
✅ Cancel pending orders
✅ Mark delivered orders as "Received"
✅ Manage delivery addresses
✅ View notifications

### As Pending Customer (`customer2`/`customer123`)
❌ Cannot login (account pending approval)
✅ Can be approved by staff/admin in "Pending Accounts" page

### As Flagged Customer (`customer3`/`customer123`)
✅ Can login
✅ View existing orders
❌ Cannot place new orders (flagged)
✅ Can submit cancellation appeal (already has one pending)
✅ Appeal can be approved/denied by staff/admin

---

## 🔍 Testing Scenarios

### 1. Account Approval Workflow
1. Login as `staff1` or `admin`
2. Go to "Pending Accounts"
3. Approve `customer2` (Jose Customer)
4. Logout
5. Login as `customer2` (now works!)

### 2. Order Lifecycle (Delivery)
1. Login as `staff1`
2. Go to "Orders"
3. Confirm Order #1 (Pending → Confirmed)
4. Assign to rider1 or rider2
5. Logout, login as the assigned rider
6. Mark as "On Delivery"
7. Mark as "Delivered"
8. Logout, login as `customer1`
9. See the delivered order, mark as "Received"

### 3. Order Lifecycle (Pickup)
1. Login as `staff1`
2. Find Order #6 (customer3)
3. Mark as "Ready for Pickup"
4. (In real scenario, customer picks up at store)
5. Mark as "Picked Up"

### 4. Rider Priority Management
1. Login as `rider1`
2. Go to "Assigned Deliveries"
3. Drag and drop to reorder priorities
4. The order at the top should be delivered first

### 5. Appeal Workflow
1. Login as `staff1` or `admin`
2. Go to "Appeals"
3. Review customer3's appeal
4. Approve or deny with notes
5. If approved, customer3 can place orders again

### 6. Inventory Management (Admin only)
1. Login as `admin` or `admin2`
2. Go to "Inventory"
3. Restock "Alkaline Water 1L" (currently out of stock)
4. Edit prices, stock counts
5. Add new items
6. Mark items as inactive

### 7. Analytics (Admin only)
1. Login as `admin` or `admin2`
2. Go to "Analytics"
3. View revenue charts
4. See order status breakdown
5. View top products and customers

---

## 🔧 Disabling the Reset Script

For production, set this in `reset_database.php`:

```php
$ALLOW_RESET = false; // Prevents accidental execution
```

---

## 📊 Database Statistics After Reset

- **Database:** station_azeu
- **Tables:** 13
- **Settings:** 17
- **Default Items:** 10
- **Users:** 8 (1 super admin, 1 admin, 1 staff, 2 riders, 3 customers)
- **Inventory Items:** 13
- **Addresses:** 4
- **Orders:** 7
- **Order Items:** 10
- **Notifications:** 15
- **Session Logs:** 10
- **Appeals:** 1
- **Delivery Priorities:** 2

---

## 🐛 Troubleshooting

### "Database error" on execution
- Check XAMPP MySQL is running
- Verify database credentials in `config/constants.php`
- Check PHP error log

### "Call to undefined function encrypt()"
- Make sure `config/AESCrypt.php` exists
- Check that `require_once` paths are correct

### Tables already exist error
- The script drops the database first, this shouldn't happen
- Try manually dropping the database: `DROP DATABASE station_azeu;`

### Password doesn't work after reset
- Default passwords are set in the script
- Check `encrypt_passwords` setting is set to `1`
- Verify `ENCRYPTION_KEY` in `config/constants.php` matches

---

## 🎉 Quick Start After Reset

1. **Reset the database:** `http://localhost/Station_A/reset_database.php`
2. **Go to login:** `http://localhost/Station_A/`
3. **Login as admin:** username=`admin`, password=`admin`
4. **Explore the system!**

---

## 📝 Notes

- Passwords are encrypted using AES-256-CBC (matching system setting)
- All timestamps use Manila timezone (Asia/Manila)
- Receipt tokens are generated for all orders
- Session logs include some failed login attempts for security testing
- One item is intentionally out of stock to test low stock alerts

---

**Happy Testing! 🚀**
