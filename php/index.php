<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 48px 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 8px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 8px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
        }

        .message.error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .message.success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 24px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome</h1>
            <p>Sign in to your account</p>
        </div>

        <?php
        session_start();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = htmlspecialchars($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Basic validation
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email';
            } else {
                // Mock authentication (replace with real database validation)
                if ($email === 'demo@example.com' && $password === 'password') {
                    $_SESSION['user'] = $email;
                    $success = 'Login successful! Redirecting...';
                    // Uncomment to redirect:
                    // header('Location: dashboard.php');
                    // exit;
                } else {
                    $error = 'Invalid email or password';
                }
            }
        }
        ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Sign In</button>
        </form>

        <div class="footer-links">
            <a href="#">Forgot password?</a>
            <span>•</span>
            <a href="#">Sign up</a>
        </div>
    </div>
</body>
</html>
