<?php
/**
 * Azeu Water Station - 404 Not Found Error Page
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - Azeu Water Station</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
        
        .error-container {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }
        
        .error-icon {
            font-size: 120px !important;
            color: #FFA726;
            margin-bottom: 24px;
        }
        
        .error-code {
            font-size: 3rem;
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 16px;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4A4A6A;
            margin-bottom: 16px;
        }
        
        .error-message {
            font-size: 1rem;
            color: #8A8AA0;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #1565C0;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0D47A1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #1565C0;
            color: #1565C0;
        }
        
        .btn-outline:hover {
            background: #1565C0;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <span class="material-icons error-icon">search_off</span>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">
            The page you're looking for doesn't exist. It might have been moved or deleted.
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-outline">
                <span class="material-icons">arrow_back</span>
                Go Back
            </a>
            <a href="../login.php" class="btn btn-primary">
                <span class="material-icons">home</span>
                Home
            </a>
        </div>
    </div>
</body>
</html>
