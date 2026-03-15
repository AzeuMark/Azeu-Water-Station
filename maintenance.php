<?php
/**
 * Azeu Water Station - Maintenance Mode Page
 */
require_once 'config/database.php';
require_once 'config/functions.php';

$station_name = get_setting('station_name') ?? 'Azeu Water Station';
$maintenance_mode = get_setting('maintenance_mode') ?? 0;

// Redirect if maintenance mode is off
if ($maintenance_mode == 0) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - <?php echo htmlspecialchars($station_name); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .maintenance-container {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }
        
        .maintenance-icon {
            font-size: 120px !important;
            color: #FFA726;
            margin-bottom: 24px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 16px;
        }
        
        .maintenance-message {
            font-size: 1.1rem;
            color: #4A4A6A;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .maintenance-details {
            background: #F5F7FA;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        
        .maintenance-details p {
            color: #8A8AA0;
            margin-bottom: 8px;
        }
        
        .maintenance-details p:last-child {
            margin-bottom: 0;
        }
        
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            background: #0D47A1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <span class="material-icons maintenance-icon">construction</span>
        <h1 class="maintenance-title">Under Maintenance</h1>
        <p class="maintenance-message">
            <?php echo htmlspecialchars($station_name); ?> is currently undergoing scheduled maintenance. 
            We'll be back online shortly!
        </p>
        
        <div class="maintenance-details">
            <p><strong>What's happening?</strong></p>
            <p>We're making improvements to serve you better. Our system is temporarily unavailable.</p>
            <p style="margin-top: 16px;"><strong>When will it be back?</strong></p>
            <p>We're working as fast as we can. Please check back soon!</p>
        </div>
        
        <a href="login.php" class="btn-home">
            <span class="material-icons">home</span>
            Back to Home
        </a>
    </div>
</body>
</html>
