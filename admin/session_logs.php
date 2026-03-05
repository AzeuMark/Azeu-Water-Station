<?php
/**
 * ============================================================================
 * AZEU WATER STATION - SESSION LOGS
 * ============================================================================
 * 
 * Purpose: View login/logout activity logs
 * Role: ADMIN
 * Status: ✅ IMPLEMENTED
 * ============================================================================
 */

$page_title = "Session Logs";
$page_css = "main.css";

require_once __DIR__ . '/../includes/auth_check.php';
require_role([ROLE_ADMIN, ROLE_SUPER_ADMIN]);

// Get session logs
$logs = db_fetch_all("SELECT * FROM session_logs ORDER BY created_at DESC LIMIT 100");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">
    <div class="content-header">
        <h1 class="content-title">Session Logs</h1>
    </div>
    
    <div class="glass-card">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($log['username']); ?></strong></td>
                                <td><span class="badge badge-<?php echo $log['role']; ?>"><?php echo $log['role']; ?></span></td>
                                <td>
                                    <span class="badge <?php echo $log['action'] === 'login' ? 'badge-success' : ($log['action'] === 'logout' ? 'badge-info' : 'badge-danger'); ?>">
                                        <?php echo $log['action']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td><?php echo format_date($log['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5"><div class="empty-state"><p>No session logs</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
