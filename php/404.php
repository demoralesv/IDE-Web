<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="assets/styles/style.css">
    <style>
        .error-container {
            text-align: center;
            color: var(--extra1-color);
        }
        .error-code {
            font-size: 120px;
            margin: 0;
            font-weight: 700;
            color: var(--danger-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .error-title {
            font-size: 28px;
            margin: 20px 0;
            font-weight: 600;
            color: var(--extra1-color);
        }
        .error-description {
            font-size: 16px;
            margin: 15px 0;
            opacity: 0.8;
            color: var(--extra1-color);
        }
        .error-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .error-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(83, 109, 130, 0.4);
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="error-container">
            <div class="error-code">404</div>
            <h1 class="error-title">Page No Encontrada</h1>
            <p class="error-description">La página que estás buscando no existe.</p>
            <a href="index.php" class="error-button">Volver a un lugar seguro</a>
        </div>
    </div>
</body>
</html>
