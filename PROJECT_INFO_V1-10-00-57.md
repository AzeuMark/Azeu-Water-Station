# Azeu Water Station — Complete Project Guide

**Version:** 1.0  
**Date:** March 10, 2026  
**Status:** ✅ Production Ready (118 files)

---

## Table of Contents

1. [Quick Start](#1-quick-start)
2. [System Overview](#2-system-overview)
3. [Technology Stack](#3-technology-stack)
4. [Project File Structure](#4-project-file-structure)
5. [Database Schema](#5-database-schema)
6. [Database Reset & Setup](#6-database-reset--setup)
7. [User Roles & Permissions](#7-user-roles--permissions)
8. [Test Account Credentials](#8-test-account-credentials)
9. [Authentication System](#9-authentication-system)
10. [Order Lifecycle & Algorithms](#10-order-lifecycle--algorithms)
11. [Inventory Management](#11-inventory-management)
12. [Customer Module](#12-customer-module)
13. [Rider Module](#13-rider-module)
14. [Staff Module](#14-staff-module)
15. [Admin Module](#15-admin-module)
16. [API Endpoints Reference](#16-api-endpoints-reference)
17. [Notification System](#17-notification-system)
18. [Logging System](#18-logging-system)
19. [Security Features](#19-security-features)
20. [Design System & Theming](#20-design-system--theming)
21. [Configuration Reference](#21-configuration-reference)
22. [Troubleshooting](#22-troubleshooting)

---

## 1. Quick Start

### Server Requirements
- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Apache** with mod_rewrite (XAMPP recommended)

### Installation Steps

```
Step 1 → Install XAMPP and start Apache + MySQL
Step 2 → Copy the Station_A folder into C:\xampp\htdocs\
Step 3 → Open browser → http://localhost/Station_A/setup_db_test.php
Step 4 → Click "CREATE DATABASE & SEED DATA" to initialize everything
Step 5 → Go to http://localhost/Station_A/ 
Step 6 → Login with admin / admin (Super Admin)
```

### Quick URLs

| Page | URL |
|------|-----|
| Login | `http://localhost/Station_A/` |
| Database Setup/Reset | `http://localhost/Station_A/setup_db_test.php` |
| Registration | `http://localhost/Station_A/register.php` |
| Forgot Password | `http://localhost/Station_A/forgot_password.php` |

---

## 2. System Overview

Azeu Water Station is a **web-based order and delivery management system** for a water refilling station located in the Philippines. It handles the complete workflow from customer order placement through rider delivery to order completion.

### Core Capabilities
- Multi-role user management (5 roles)
- Full order lifecycle management (9 statuses)
- Inventory tracking with stock alerts
- Rider delivery assignment and tracking
- Account approval and flagging system
- Cancellation appeals workflow
- Analytics and reporting dashboard
- Public receipt generation with QR codes
- Real-time notifications
- Dark/light theme support

### System Flow Diagram

```
Customer registers → Account set to "Pending"
                          ↓
Staff/Admin approves → Account set to "Active"
                          ↓
Customer places order → Order status: "Pending"
                          ↓
Staff confirms → "Confirmed" (stock decremented here)
                          ↓
Staff assigns rider → "Assigned"
  (or auto-assign)       ↓
Rider starts delivery → "On Delivery"
                          ↓
Rider marks delivered → "Delivered"
                          ↓
Customer confirms → "Accepted" (optional)
```

---

## 3. Technology Stack

### Backend
| Component | Technology |
|-----------|-----------|
| Language | Pure PHP (no frameworks) |
| Database | MySQL via PDO |
| Authentication | `$_SESSION` based |
| Encryption | AES-256-CBC (OpenSSL) |

### Frontend
| Component | Technology |
|-----------|-----------|
| Structure | HTML5 semantic elements |
| Styling | Vanilla CSS with CSS Variables |
| Logic | Vanilla JavaScript (ES6+) |
| Icons | Material Icons (Google Fonts CDN) |
| Typography | Inter font (Google Fonts CDN) |

### External Libraries (all via CDN)
| Library | Purpose |
|---------|---------|
| SweetAlert2 | Beautiful confirmation/alert dialogs |
| Chart.js | Analytics charts and graphs |
| QRCode.js | QR code on receipts |
| html2pdf.js | PDF export of receipts |
| html2canvas | Image export of receipts |
| Sortable.js | Drag-and-drop rider delivery priority |
| PHPMailer | Email sending for password reset (local vendor) |

---

## 4. Project File Structure

```
Station_A/
│
├── config/                          ← Core configuration (7 files)
│   ├── constants.php                  System constants, roles, statuses
│   ├── database.php                   PDO connection, table creation, query helpers
│   ├── session.php                    Session management, auth guards, cleanup
│   ├── functions.php                  60+ utility functions
│   ├── AESCrypt.php                   AES-256-CBC encrypt/decrypt
│   ├── logger.php                     Logging engine with rotation
│   └── request_logger.php             HTTP request/response logging
│
├── includes/                        ← Shared PHP includes (4 files)
│   ├── auth_check.php                 Auth guard (included by all protected pages)
│   ├── header.php                     HTML head, nav bar, notifications, theme toggle
│   ├── sidebar.php                    Role-based sidebar navigation
│   └── footer.php                     Common scripts, page-specific JS loader
│
├── assets/                          ← Shared frontend assets
│   ├── css/
│   │   ├── global.css                 CSS variables, reset, typography, themes
│   │   ├── auth.css                   Login/register page styles
│   │   ├── components.css             Reusable UI components (600+ lines)
│   │   ├── layout.css                 Header, sidebar, content layout
│   │   └── receipt.css                Public receipt page styling
│   ├── js/
│   │   ├── global.js                  Theme toggle, Manila clock, CSRF helpers
│   │   ├── auth.js                    Login/register AJAX form handlers
│   │   ├── components.js              Toast, modals, pagination, notifications
│   │   ├── sidebar.js                 Sidebar collapse/expand, active link
│   │   └── receipt.js                 QR code generation, PDF/image download
│   └── images/
│       └── default-item.png           Fallback icon for inventory items
│
├── api/                             ← REST API endpoints (45 files)
│   ├── auth/                          Login, logout, register, password reset (5)
│   ├── orders/                        CRUD, status, cancel, assign, receipt (8)
│   ├── accounts/                      List, get, create, update, approve, flag (6)
│   ├── inventory/                     List, get, create, update, restock (5)
│   ├── riders/                        List, statistics, toggle, priority (4)
│   ├── addresses/                     List, create, update, delete (4)
│   ├── appeals/                       Create, list, review (3)
│   ├── notifications/                 Get, count, mark read, create (4)
│   ├── settings/                      Get, update, preferences (3)
│   └── analytics/                     Dashboard, orders, revenue (3)
│
├── customer/                        ← Customer portal
│   ├── dashboard.php                  Dashboard with stats & active orders
│   ├── place_order.php                Item selection, checkout, place order
│   ├── orders.php                     Active orders & order history
│   ├── addresses.php                  Manage delivery addresses
│   ├── settings.php                   Profile, password, appeals
│   ├── css/                           Page-specific stylesheets
│   └── js/                            Page-specific JavaScript
│
├── rider/                           ← Rider portal
│   ├── dashboard.php                  Stats, availability toggle
│   ├── deliveries.php                 Active deliveries
│   ├── assigned_deliveries.php        Delivery queue with drag-drop reorder
│   ├── delivery_history.php           Past deliveries
│   ├── settings.php                   Profile settings
│   ├── css/                           Page-specific stylesheets
│   └── js/                            Page-specific JavaScript
│
├── staff/                           ← Staff portal (shared with admin)
│   ├── dashboard.php                  System stats, quick actions
│   ├── orders.php                     All orders management
│   ├── accounts.php                   All user accounts
│   ├── pending_accounts.php           Approve new registrations
│   ├── inventory.php                  View/manage inventory
│   ├── riders.php                     Rider list
│   ├── rider_statistics.php           Rider performance metrics
│   ├── appeals.php                    Review cancellation appeals
│   ├── settings.php                   Personal settings
│   ├── css/main.css                   Staff styles
│   └── js/                            Staff JavaScript
│
├── admin/                           ← Admin portal (extends staff)
│   ├── (shares all staff pages)
│   ├── analytics.php                  Charts, trends, reports
│   ├── session_logs.php               Login/logout audit trail
│   ├── system_settings.php            Station config, colors, encryption
│   ├── css/main.css                   Admin styles
│   └── js/                            Admin JavaScript
│
├── errors/                          ← Error pages
│   ├── 403.php                        Forbidden access
│   └── 404.php                        Page not found
│
├── logs/                            ← Auto-created log files
│   ├── log.txt                        Main activity log
│   ├── error.txt                      Error-only log
│   └── .htaccess                      Blocks web access to logs
│
├── ignore/                          ← Development reference files
│   └── final-prompt.txt               Original system specification
│
├── index.php                        ← Login page (entry point)
├── register.php                     ← Customer registration
├── forgot_password.php              ← Password reset request
├── reset_password.php               ← Password reset form
├── receipt.php                      ← Public receipt viewer (via token)
├── maintenance.php                  ← Maintenance mode page
├── setup_db_test.php                ← Database reset & seed tool
└── .htaccess                        ← Apache rewrite rules
```

---

## 5. Database Schema

Database name: **`station_azeu`** — auto-created on first run.

### Table 1: `users`
| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AI | User ID |
| username | VARCHAR(50) UNIQUE | Login username |
| password | TEXT | AES-encrypted or plain password |
| full_name | VARCHAR(100) | Display name |
| email | VARCHAR(100) | Email address |
| phone | VARCHAR(20) | Phone number |
| role | ENUM | `customer`, `rider`, `staff`, `admin`, `super_admin` |
| status | ENUM | `pending`, `active`, `flagged`, `deleted` |
| is_available | TINYINT | Rider availability flag (1=available) |
| cancellation_count | INT | Monthly cancellation counter |
| cancellation_reset_date | DATE | Date to reset cancellation count |
| login_attempts | INT | Failed login attempt counter |
| login_locked_until | DATETIME | Lockout expiry timestamp |
| created_at | DATETIME | Account creation time |

### Table 2: `user_preferences`
| Column | Type | Description |
|--------|------|-------------|
| user_id | INT FK→users | One-to-one with users |
| dark_mode | TINYINT | 0=light, 1=dark |

### Table 3: `customer_addresses`
| Column | Type | Description |
|--------|------|-------------|
| customer_id | INT FK→users | Address owner |
| label | VARCHAR(50) | "Home", "Office", etc. |
| full_address | TEXT | Full delivery address |
| is_default | TINYINT | 1 = default address |

### Table 4: `inventory`
| Column | Type | Description |
|--------|------|-------------|
| item_name | VARCHAR(100) | Product name |
| item_icon | VARCHAR(255) | Image path (nullable) |
| stock_count | INT | Current stock quantity |
| price | DECIMAL(10,2) | Unit price in ₱ |
| status | ENUM | `active`, `inactive`, `out_of_stock` |
| last_restocked_at | DATETIME | Last restock timestamp |

### Table 5: `orders`
| Column | Type | Description |
|--------|------|-------------|
| customer_id | INT FK→users | Who placed the order |
| rider_id | INT FK→users | Assigned rider (nullable) |
| payment_type | ENUM | `cod`, `pickup`, `online` |
| delivery_type | ENUM | `delivery`, `pickup` |
| status | ENUM | 9 statuses (see Order Lifecycle) |
| delivery_address | TEXT | Delivery location |
| order_notes | TEXT | Customer instructions |
| delivery_fee | DECIMAL | ₱0 for pickup, configurable for delivery |
| subtotal | DECIMAL | Sum of item prices × quantities |
| total_amount | DECIMAL | subtotal + delivery_fee |
| expected_delivery_date | DATE | Estimated delivery date |
| cancellation_reason | TEXT | Why the order was cancelled |
| cancelled_by | INT FK→users | Who cancelled it |
| receipt_token | VARCHAR(64) UNIQUE | Token for public receipt URL |
| delivered_at | DATETIME | When delivery was completed |
| customer_confirmed | TINYINT | 1 = customer accepted delivery |
| customer_confirmed_at | DATETIME | When customer confirmed |

### Table 6: `order_items`
Snapshot of items at order time (prices frozen).

| Column | Type | Description |
|--------|------|-------------|
| order_id | INT FK→orders | Parent order |
| inventory_id | INT FK→inventory | Original item reference |
| item_name | VARCHAR | Snapshot of name at order time |
| item_price | DECIMAL | Snapshot of price at order time |
| quantity | INT | Quantity ordered |
| subtotal | DECIMAL | item_price × quantity |

### Table 7: `notifications`
| Column | Type | Description |
|--------|------|-------------|
| user_id | INT FK→users | Notification recipient |
| title | VARCHAR(150) | Notification title |
| message | TEXT | Notification body |
| type | VARCHAR(50) | Event type (order_placed, order_confirmed, etc.) |
| reference_id | INT | Related order/entity ID |
| is_read | TINYINT | 0=unread, 1=read |

### Table 8: `session_logs`
| Column | Type | Description |
|--------|------|-------------|
| user_id | INT FK→users | User who logged in |
| username | VARCHAR | Username at time of action |
| role | VARCHAR | Role at time of action |
| action | ENUM | `login`, `logout`, `failed_login` |
| ip_address | VARCHAR(45) | Client IP address |

### Table 9: `settings`
Key-value store for system configuration (17 settings). See [Configuration Reference](#21-configuration-reference).

### Table 10: `default_items`
Preset dropdown names when creating new inventory items.

### Table 11: `cancellation_appeals`
| Column | Type | Description |
|--------|------|-------------|
| customer_id | INT FK→users | Flagged customer |
| reason | TEXT | Customer's appeal reason |
| status | ENUM | `pending`, `approved`, `denied` |
| reviewed_by | INT FK→users | Staff/admin who reviewed |
| admin_notes | TEXT | Reviewer's notes |

### Table 12: `password_resets`
Token-based email password reset. Tokens expire after 1 hour.

### Table 13: `delivery_priority`
Tracks rider's delivery queue order (drag-and-drop reordering via Sortable.js).

---

## 6. Database Reset & Setup

### ⚡ Recommended: `setup_db_test.php`

```
URL: http://localhost/Station_A/setup_db_test.php
```

**What it does (single click):**
1. **DROPS** the entire `station_azeu` database if it exists
2. **CREATES** a fresh database with all 13 tables
3. **SEEDS** complete test data:
   - 17 system settings
   - 10 default item names
   - 8 user accounts (1 super admin, 2 customers, 2 riders, 2 staff, 1 pending)
   - User preferences for all accounts
   - Customer addresses
   - 10 inventory items
   - 10 orders across all statuses
   - Sample notifications and session logs

⚠️ **WARNING:** This destroys ALL existing data. Use only for testing.

### Alternative: Manual Database Reset

```sql
-- In phpMyAdmin or MySQL CLI:
DROP DATABASE IF EXISTS station_azeu;
-- Then visit any page — database.php will auto-create tables
-- But you'll only get the super admin account and default settings
```

---

## 7. User Roles & Permissions

### Permission Matrix

| Feature | Customer | Rider | Staff | Admin | Super Admin |
|---------|----------|-------|-------|-------|-------------|
| Place orders | ✅ | ❌ | ❌ | ❌ | ❌ |
| Cancel own pending orders | ✅ | ❌ | ❌ | ❌ | ❌ |
| Track own orders | ✅ | ❌ | ❌ | ❌ | ❌ |
| Accept/confirm delivery | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage own addresses | ✅ | ❌ | ❌ | ❌ | ❌ |
| Submit appeals | ✅ | ❌ | ❌ | ❌ | ❌ |
| View assigned deliveries | ❌ | ✅ | ❌ | ❌ | ❌ |
| Mark On Delivery/Delivered | ❌ | ✅ | ❌ | ❌ | ❌ |
| Reorder delivery priority | ❌ | ✅ | ❌ | ❌ | ❌ |
| Toggle availability | ❌ | ✅ | ❌ | ❌ | ❌ |
| Approve pending accounts | ❌ | ❌ | ✅ | ✅ | ✅ |
| Confirm orders | ❌ | ❌ | ✅ | ✅ | ✅ |
| Assign riders | ❌ | ❌ | ✅ | ✅ | ✅ |
| Cancel any order | ❌ | ❌ | ✅ | ✅ | ✅ |
| Review appeals | ❌ | ❌ | ✅ | ✅ | ✅ |
| Manage inventory (CRUD) | ❌ | ❌ | ❌ | ✅ | ✅ |
| View analytics | ❌ | ❌ | ❌ | ✅ | ✅ |
| View session logs | ❌ | ❌ | ❌ | ✅ | ✅ |
| System settings | ❌ | ❌ | ❌ | ✅ | ✅ |
| Create admin accounts | ❌ | ❌ | ❌ | ❌ | ✅ |
| Cannot be deleted | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 8. Test Account Credentials

### After running `setup_db_test.php`:

| Role | Username | Password | Status | Notes |
|------|----------|----------|--------|-------|
| Super Admin | `admin` | `admin` | Active | Full system control, cannot be deleted |
| Customer | `customer1` | `12345` | Active | Has addresses, orders across all statuses |
| Customer | `customer2` | `12345` | Active | Has address, orders |
| Rider | `rider1` | `12345` | Active | Has assigned deliveries |
| Rider | `rider2` | `12345` | Active | Has assigned delivery |
| Staff | `staff1` | `12345` | Active | Can manage orders & accounts |
| Staff | `staff2` | `12345` | Active | Can manage orders & accounts |
| Customer | `pending1` | `12345` | **Pending** | For testing account approval flow |

---

## 9. Authentication System

### Login Flow
```
1. User submits username + password via AJAX (POST /api/auth/login.php)
2. Server checks if username exists in DB (WHERE deleted_at IS NULL)
3. If not found → "Invalid credentials" (generic message)
4. If found → check if account is locked (login_locked_until > now)
5. If locked → "Account locked. Try again in X minutes"
6. Compare password:
   a. If encrypt_passwords=1 → decrypt stored password with AES, compare
   b. If encrypt_passwords=0 → plain text comparison
7. If password wrong → increment login_attempts
   - If login_attempts >= max_login_attempts → set login_locked_until
8. If password correct → reset login_attempts, start session
9. Log session_logs entry (login action)
10. Return JSON {success, role, redirect_url}
11. JS redirects to role-appropriate dashboard
```

### Registration Flow
```
1. Customer fills out form: username, password, full_name, email, phone
2. POST /api/auth/register.php validates all fields
3. Checks username uniqueness, email format
4. Creates user with status = 'pending'
5. Creates user_preferences record
6. Account sits in pending until Staff/Admin approves it
7. Pending accounts auto-expire after pending_expiry_days (default 7)
```

### Password Reset Flow
```
1. User enters email on forgot_password.php
2. POST /api/auth/forgot_password.php generates token (64-char hex)
3. Token stored in password_resets table (expires in 1 hour)
4. Email sent via PHPMailer with reset link
5. User clicks link → reset_password.php?token=xxx
6. User enters new password
7. POST /api/auth/reset_password.php validates token, updates password
8. Token marked as used
```

### Session Management
- Sessions start with `session.php` (included via `auth_check.php`)
- Session stores: `user_id`, `username`, `role`, `full_name`, `dark_mode`
- `require_login()` — redirects to login if not authenticated
- `require_role([...])` — checks if user's role is in allowed list
- `check_maintenance()` — redirects to maintenance page if enabled
- CSRF tokens generated per session, validated on all POST requests

---

## 10. Order Lifecycle & Algorithms

### Status Flow: Delivery Orders (COD)

```
PENDING ──→ CONFIRMED ──→ ASSIGNED ──→ ON DELIVERY ──→ DELIVERED ──→ ACCEPTED
   │              │             │            │                          (optional)
   ↓              ↓             ↓            ↓
CANCELLED    CANCELLED     CANCELLED    CANCELLED
(customer)   (staff/admin) (staff/admin) (staff/admin)
```

### Status Flow: Pickup Orders

```
PENDING ──→ CONFIRMED ──→ READY FOR PICKUP ──→ PICKED UP ──→ ACCEPTED
   │              │                                           (optional)
   ↓              ↓
CANCELLED    CANCELLED
```

### Stock Management Rules

| Action | Stock Effect |
|--------|-------------|
| Order placed (Pending) | **No stock change** |
| Confirmed (Pending → Confirmed) | **Stock DECREMENTED** |
| Cancel Pending order | No stock change (was never decremented) |
| Cancel Confirmed/Assigned/On Delivery | **Stock RESTORED** |
| Delivered/Picked Up | No change (already decremented) |

### Cancellation Rules

| Who Cancels | What Happens |
|-------------|-------------|
| **Customer** | Can only cancel `Pending` orders. Increments `cancellation_count`. At max (default 5) → account flagged |
| **Rider** | Can cancel `Assigned` or `On Delivery` → order reverts to `Confirmed` (rider unassigned, not fully cancelled) |
| **Staff/Admin** | Can cancel at any status. Must provide reason. Stock restored if order was Confirmed+ |

### Auto-Assign Rider Algorithm

```
1. Get all riders WHERE role='rider' AND status='active' AND is_available=1
2. For each rider, COUNT their active assignments (orders in assigned/on_delivery)
3. Sort by active assignment count ASC
4. On ties, sort by user ID ASC (earliest account wins)
5. Assign to the rider with fewest active deliveries
6. If no riders available → order stays in Confirmed, staff manually assigns
```

### Monthly Cancellation Reset
- Runs automatically via `session.php` cleanup (on every page load)
- Checks if `cancellation_reset_date` has passed for each customer
- If passed: resets `cancellation_count` to 0, sets new reset date to 1st of next month
- If account was flagged due to cancellations and count is now 0: status stays flagged (requires appeal)

### Pending Account Expiry
- Runs alongside cancellation reset
- Deletes all users WHERE `status='pending'` AND `created_at < NOW() - pending_expiry_days`
- Default: 7 days

---

## 11. Inventory Management

### Item Properties
- **Name** — from default_items dropdown or free text
- **Icon** — optional image upload
- **Price** — in Philippine Peso (₱)
- **Stock Count** — integer quantity
- **Status** — `active` / `inactive` / `out_of_stock`

### Operations

| Operation | Who | Endpoint |
|-----------|-----|----------|
| View items | Staff, Admin | `GET /api/inventory/list.php` |
| Create item | Admin only | `POST /api/inventory/create.php` |
| Update item | Admin only | `POST /api/inventory/update.php` |
| Restock | Admin only | `POST /api/inventory/restock.php` |
| Delete/deactivate | Admin only | Via update (set status=inactive) |

### Low Stock Alert
- Threshold configurable in settings (`low_stock_threshold`, default: 10)
- Items at or below threshold show warning badge
- Notification sent to staff/admin when item crosses threshold

---

## 12. Customer Module

### Pages & Sidebar Navigation
1. **Dashboard** (`customer/dashboard.php`)
   - Greeting with user's name
   - 3 stat cards: Total Orders, Pending Orders, Delivered Orders
   - Active orders preview table (max 3)
   - Quick action buttons: Place Order, My Orders, Order History, Settings
   - If account is **pending**: shows "awaiting approval" notice
   - If account is **flagged**: shows cancellation count + appeal button

2. **Place Order** (`customer/place_order.php`)
   - Items grid loaded via AJAX (paginated, 20 per page)
   - Search bar filters items by name
   - Click card to select/deselect, adjust quantity with +/- buttons
   - Out-of-stock items shown with overlay (not selectable)
   - Checkout bar appears at bottom when items selected
   - Checkout modal with:
     - Selected items summary with remove buttons
     - Delivery address dropdown (saved addresses + custom option)
     - Special instructions text area
     - Payment method: COD, Pickup, Online (disabled/coming soon)
     - Order summary: items, subtotal, delivery fee, total
   - Supports reorder via `?reorder=orderId` URL param

3. **My Orders / Order History** (`customer/orders.php`)
   - Filter buttons by status
   - Search by Order ID or item name
   - Date range filtering
   - Table with: Order ID, Items, Payment, Status, Qty, Rider, Dates
   - Actions: Cancel (if pending), Accept/Received (if delivered), Reorder, View Receipt

4. **Addresses** (`customer/addresses.php`)
   - List all saved addresses with labels
   - Add new address (label + full address + set as default)
   - Edit existing addresses
   - Delete addresses (confirmation required)
   - Set any address as default

5. **Settings** (`customer/settings.php`)
   - Profile form: full name, email, phone (username read-only)
   - Change password: current + new + confirm
   - If flagged: appeals history table + submit new appeal button

---

## 13. Rider Module

### Pages & Sidebar Navigation
1. **Dashboard** (`rider/dashboard.php`)
   - Availability toggle (Available / Unavailable)
   - Stats: Active Deliveries, Completed Today, Total Completed, Success Rate
   - Current active deliveries list

2. **My Deliveries** (`rider/deliveries.php`)
   - Active deliveries with customer name, address, items, order date
   - Actions: Mark "On Delivery", Mark "Delivered"
   - Rider can cancel (reverts order to Confirmed, unassigns rider)

3. **Assigned Deliveries** (`rider/assigned_deliveries.php`)
   - Drag-and-drop reorderable delivery queue (Sortable.js)
   - Priority saved to `delivery_priority` table
   - Shows order details, customer info, delivery address

4. **Delivery History** (`rider/delivery_history.php`)
   - Past completed/cancelled deliveries
   - Filterable by date range
   - Performance metrics

5. **Settings** (`rider/settings.php`)
   - Profile edit + password change (same as customer)

---

## 14. Staff Module

### Pages & Sidebar Navigation
1. **Dashboard** — Stats: Total Orders, Pending Orders, Pending Accounts, Low Stock
2. **Orders** — View all system orders, filter by status, confirm orders, assign riders, cancel with reason
3. **Accounts** — View all user accounts, filter by role, edit details, flag/unflag accounts, delete (not super admin)
4. **Pending Accounts** — List of pending registrations, approve or reject
5. **Inventory** — View items (read-only for staff, full CRUD for admin)
6. **Riders** — View rider list with availability status
7. **Rider Statistics** — Performance metrics per rider
8. **Appeals** — View pending appeals, approve (unflags account + resets count) or deny with notes
9. **Settings** — Personal profile settings

### Order Management Workflow (Staff)
```
Step 1: Go to Orders page
Step 2: Click on a "Pending" order
Step 3: Click "Confirm" → order status changes, stock decremented
Step 4: Click "Assign Rider" → select from available riders (or auto-assign)
Step 5: Wait for rider to mark "On Delivery" → "Delivered"
Step 6: Or cancel with reason at any point
```

---

## 15. Admin Module

### Additional Pages (beyond Staff)
1. **Analytics** (`admin/analytics.php`)
   - Revenue charts (daily/weekly/monthly) via Chart.js
   - Order trends and status distribution
   - Top products by sales volume
   - Customer growth metrics

2. **Session Logs** (`admin/session_logs.php`)
   - Complete login/logout/failed_login audit trail
   - Filterable by user, date range, action type
   - Shows IP address, timestamp, role

3. **System Settings** (`admin/system_settings.php`)
   - Station name and address
   - Delivery fee amount
   - Max cancellation limit per month
   - Pending account expiry days
   - Low stock threshold
   - Maintenance mode toggle
   - Encrypt passwords toggle (with migration)
   - Force dark mode system-wide
   - Theme colors: primary, secondary, accent, surface
   - Max login attempts and lockout duration

### Password Encryption Toggle
When admin toggles `encrypt_passwords`:
```
If toggling ON  → All existing plain-text passwords encrypted with AES-256-CBC
If toggling OFF → All existing encrypted passwords decrypted back to plain text
Migration happens immediately for ALL users in a single transaction
```

---

## 16. API Endpoints Reference

### Authentication (`/api/auth/`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `login.php` | Login with username/password |
| GET | `logout.php` | End session, redirect to login |
| POST | `register.php` | Customer registration |
| POST | `forgot_password.php` | Request password reset email |
| POST | `reset_password.php` | Reset password with token |

### Orders (`/api/orders/`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `create.php` | Place new order |
| GET | `get.php?id=` | Get single order details |
| GET | `list.php` | List orders (with filters, pagination) |
| POST | `update_status.php` | Change order status |
| POST | `cancel.php` | Cancel order |
| POST | `assign_rider.php` | Assign rider to order |
| POST | `confirm_delivery.php` | Customer confirms receipt |
| GET | `get_receipt.php?order_id=` | Get/generate receipt token |

### Accounts (`/api/accounts/`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `list.php` | List all accounts (filterable) |
| GET | `get.php?id=` | Get account details |
| POST | `create.php` | Create staff/rider/admin account |
| POST | `update.php` | Update account details |
| POST | `approve.php` | Approve pending account |
| POST | `flag.php` | Flag or unflag account |

### Inventory (`/api/inventory/`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `list.php` | List inventory items |
| GET | `get.php?id=` | Get item details |
| POST | `create.php` | Create new item |
| POST | `update.php` | Update item |
| POST | `restock.php` | Update stock quantity |

### Other APIs
| Group | Endpoints |
|-------|-----------|
| Riders | list, statistics, toggle_availability, update_priority |
| Addresses | list, create, update, delete |
| Appeals | create, list, review |
| Notifications | get, count_unread, mark_read, create |
| Settings | get, update, update_preferences |
| Analytics | dashboard, orders, revenue |

### API Request Format
All POST requests require:
- `Content-Type: application/json`
- `csrf_token` field in the JSON body
- Valid session (except auth endpoints)

### API Response Format
```json
{
    "success": true|false,
    "message": "Human-readable message",
    "data": { ... }
}
```

---

## 17. Notification System

### How Notifications Work
- Created server-side via `create_notification()` function
- Stored in `notifications` table
- Header bell icon shows unread count badge
- Dropdown shows recent notifications
- "Mark all read" button
- Auto-refreshes periodically via JavaScript polling

### Notification Types
| Type | When Created |
|------|-------------|
| `order_placed` | Customer places new order |
| `order_confirmed` | Staff confirms order |
| `order_assigned` | Rider assigned to order |
| `on_delivery` | Rider starts delivery |
| `delivered` | Rider marks delivered |
| `order_accepted` | Customer accepts delivery |
| `ready_for_pickup` | Order ready for pickup |
| `picked_up` | Customer picks up order |
| `order_cancelled` | Order cancelled by anyone |
| `account_approved` | Pending account approved |
| `account_flagged` | Account flagged |
| `account_unflagged` | Appeal approved, account unflagged |
| `low_stock` | Item stock below threshold |
| `appeal_reviewed` | Appeal approved or denied |

---

## 18. Logging System

### Log Files
| File | Content | Always Active |
|------|---------|---------------|
| `logs/log.txt` | All activity | Only if `DEBUG_MODE=true` for TRACE/DEBUG/INFO |
| `logs/error.txt` | Errors only | Always |

### Log Levels
| Level | Value | When Used |
|-------|-------|-----------|
| TRACE | 0 | Function entry/exit, detailed flow |
| DEBUG | 1 | SQL queries, variable dumps |
| INFO | 2 | User actions, successful operations |
| WARNING | 3 | Failed logins, validation issues |
| ERROR | 4 | Database errors, exceptions |
| CRITICAL | 5 | Fatal errors, system crashes |

> TRACE, DEBUG, INFO are only logged when `DEBUG_MODE=true`.  
> WARNING, ERROR, CRITICAL are **always** logged.

### Log Format
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] [filename.php] [function_name] — MESSAGE | key=value | key=value
```

### Log Rotation
- Max file size: 10MB (configurable via `LOG_MAX_SIZE`)
- When exceeded, current log renamed to `log_YYYYMMDD_HHMMSS.txt`
- New empty log file started automatically

### Sensitive Data Protection
These fields are **automatically redacted** in logs:
`password`, `token`, `secret`, `key`, `csrf` → `[REDACTED]`

---

## 19. Security Features

| Feature | Implementation |
|---------|---------------|
| **Password Encryption** | AES-256-CBC via OpenSSL (togglable in settings) |
| **CSRF Protection** | Per-session token, validated on all POST requests via `hash_equals()` |
| **Login Lockout** | After `max_login_attempts` (default 10) failures → locked for `login_lockout_minutes` (default 15) |
| **Role-Based Access** | `require_role()` guard on every protected page |
| **Input Sanitization** | `sanitize_input()` wrapper around `htmlspecialchars()` + `trim()` |
| **SQL Injection Prevention** | PDO prepared statements with parameterized queries everywhere |
| **XSS Prevention** | `htmlspecialchars()` on all output, CSP headers |
| **Session Security** | Session-based auth, session data validated against DB |
| **Log Protection** | `.htaccess` in logs/ directory blocks web access |
| **Sensitive Data Redaction** | Passwords/tokens auto-removed from log entries |
| **Account Expiry** | Pending accounts auto-deleted after configurable days |
| **Maintenance Mode** | System-wide lockout redirecting to maintenance page |

---

## 20. Design System & Theming

### Color Palette (configurable in System Settings)
| Variable | Default | Usage |
|----------|---------|-------|
| `--primary` | `#1565C0` | Buttons, links, active states |
| `--secondary` | `#1E88E5` | Secondary actions |
| `--accent` | `#42A5F5` | Highlights, hover states |
| `--success` | `#66BB6A` | Success states, confirmed |
| `--warning` | `#FFA726` | Warnings, pending states |
| `--danger` | `#EF5350` | Errors, delete, cancel |
| `--info` | `#29B6F6` | Information states |
| `--surface` | `#F5F7FA` | Card backgrounds (light mode) |

### Dark Mode
- Per-user preference stored in `user_preferences.dark_mode`
- Toggle button in header
- Admin can force dark mode system-wide via `force_dark_mode` setting
- CSS variables switch automatically via `[data-theme="dark"]` selector

### UI Components
- **Glass cards** — `backdrop-filter: blur()` with semi-transparent backgrounds
- **Floating labels** — Input labels that animate up when focused
- **Status badges** — Color-coded role and status indicators
- **Toast notifications** — Slide-in feedback messages
- **Modal system** — Overlay modals for forms and confirmations
- **Data tables** — Sortable, responsive with horizontal scroll on mobile
- **Pagination** — Numbered pages with Previous/Next buttons

### Responsive Breakpoints
| Breakpoint | Target |
|------------|--------|
| `> 1024px` | Desktop — full sidebar, multi-column grids |
| `768px – 1024px` | Tablet — collapsible sidebar, reduced columns |
| `< 768px` | Mobile — hamburger menu, single column, stacked layouts |
| `< 480px` | Small mobile — further reduced padding/sizing |

---

## 21. Configuration Reference

### `config/constants.php` — System Constants

| Constant | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `localhost` | Database server |
| `DB_NAME` | `station_azeu` | Database name |
| `DB_USER` | `root` | MySQL username |
| `DB_PASS` | *(empty)* | MySQL password |
| `ENCRYPTION_KEY` | `azeu_water_station_2025_key` | AES encryption key |
| `DEBUG_MODE` | `true` | Enable verbose logging |
| `LOG_MAX_SIZE` | `10485760` (10MB) | Log rotation threshold |
| `LOG_TIMEZONE` | `Asia/Manila` | Timezone for log timestamps |
| `ITEMS_PER_PAGE` | `50` | Default pagination size |
| `ORDER_ITEMS_PER_PAGE` | `20` | Items per page on place order |

### `settings` Table — Runtime Configuration

| Key | Default | Description |
|-----|---------|-------------|
| `station_name` | Azeu Water Station | Displayed in header and receipts |
| `station_address` | *(sample address)* | Physical station location |
| `max_cancellation` | 5 | Monthly cancel limit before flagging |
| `pending_expiry_days` | 7 | Days before pending accounts expire |
| `low_stock_threshold` | 10 | Stock count to trigger low stock alert |
| `maintenance_mode` | 0 | 1 = block all access except admin |
| `encrypt_passwords` | 1 | 1 = AES encryption, 0 = plain text |
| `auto_assign_orders` | 0 | 1 = auto-assign rider on confirm |
| `timezone` | Asia/Manila | System timezone |
| `force_dark_mode` | 0 | 1 = force dark theme for all users |
| `primary_color` | #1565C0 | Theme primary color |
| `secondary_color` | #1E88E5 | Theme secondary color |
| `accent_color` | #42A5F5 | Theme accent color |
| `surface_color` | #F5F7FA | Card/surface background |
| `max_login_attempts` | 10 | Failed attempts before lockout |
| `delivery_fee` | 50.00 | Delivery fee in ₱ |
| `login_lockout_minutes` | 15 | Lockout duration in minutes |

---

## 22. Troubleshooting

### Common Issues

| Problem | Solution |
|---------|----------|
| **"Connection failed" on first load** | Make sure XAMPP MySQL is running. Check `DB_HOST`, `DB_USER`, `DB_PASS` in `constants.php` |
| **Login doesn't work** | Run `setup_db_test.php` to reset the database. Default: `admin` / `admin` |
| **Blank page / PHP errors** | Set `DEBUG_MODE = true` in `constants.php`. Check `logs/error.txt` |
| **"Account locked"** | Wait for lockout to expire, or reset via `setup_db_test.php` |
| **Customer can't place orders** | Check if account status is `active` (not `pending` or `flagged`) |
| **Rider not showing for assignment** | Check rider's `is_available` (must be 1) and `status` (must be `active`) |
| **Styles not loading** | Ensure the folder is named `Station_A` and accessed via `http://localhost/Station_A/` |
| **Emails not sending (password reset)** | Configure PHPMailer SMTP settings (currently requires manual setup) |
| **"CSRF token mismatch"** | Clear browser cookies/session and login again |
| **Tables already exist errors** | Use `setup_db_test.php` — it drops everything first |

### Viewing Logs (PowerShell)
```powershell
# View last 50 log entries
Get-Content "D:\Others\Azeu Codes\Station_A\logs\log.txt" -Tail 50

# Find errors
Select-String -Path "D:\Others\Azeu Codes\Station_A\logs\log.txt" -Pattern "\[ERROR\]"

# Track a specific user
Select-String -Path "D:\Others\Azeu Codes\Station_A\logs\log.txt" -Pattern "user_id=5"

# Find a specific order
Select-String -Path "D:\Others\Azeu Codes\Station_A\logs\log.txt" -Pattern "order_id=1"
```

### Production Deployment Checklist
- [ ] Set `DEBUG_MODE = false` in `constants.php`
- [ ] Change `ENCRYPTION_KEY` to a unique random string
- [ ] Change default `admin` password
- [ ] Set strong `DB_PASS`
- [ ] Configure PHPMailer SMTP for password reset emails
- [ ] Set `maintenance_mode = 0` when ready to go live
- [ ] Verify `.htaccess` blocks access to `logs/` directory
- [ ] Remove `setup_db_test.php` from production server
- [ ] Set appropriate file permissions (755 for directories, 644 for files)

---

*End of Project Documentation — Azeu Water Station v1.0*
