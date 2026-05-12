<?php
session_start();

require_once __DIR__ . '/class/class.php';

$error = "";
$success = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $lastname = trim($_POST["lastname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $password)) {
        $error = "La contraseña debe contener al menos un número y una letra mayúscula y minúscula, y al menos 8 o más caracteres.";
    } elseif (register($name, $lastname, $email, $password)) {
        $success = true;
    } else {
        $error = "Error al registrar el usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/img/favicon.ico" rel="icon" type="image">
    <link href="assets/styles/style.css" rel="stylesheet">
    <title>Sign Up | SIED</title>
</head>
<body class="auth-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Registrarse</h1>
            <p>Crea una nueva cuenta para continuar</p>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <p>Usuario registrado exitosamente. Redirigiendo...</p>
            </div>

            <script>
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" action="">
                
                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Apellido</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>

               <div class="form-group">
                    <label for="email">Correo Electrónico</label>

                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña (mínimo 8 caracteres)</label>
                    <input type="password" id="password" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                            title="Debe contener al menos un número y una letra mayúscula y minúscula, y al menos 8 o más caracteres" required>
                    <p id="passwordError"  style="color: var(--danger-color); text-align: start; display: none;">
                        La contraseña debe contener al menos un número y una letra mayúscula y minúscula, y al menos 8 o más caracteres.
                    </p>
                </div>

                <button type="submit">Registrarse</button>
            </form>

            <div class="footer-links">
                <a href="index.php">Cancelar</a>
            </div>
        <?php endif; ?>
    </div>









    <script>
        const passwordInput = document.getElementById("password");
        const passwordError = document.getElementById("passwordError");

        const passwordRegex = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/;

        passwordInput.addEventListener("blur", function () {
            const password = passwordInput.value.trim();

            if (password !== "" && !passwordRegex.test(password)) {
                passwordError.style.display = "block";
                passwordInput.classList.add("input-error");
            } else {
                passwordError.style.display = "none";
                passwordInput.classList.remove("input-error");
            }
        });

        passwordInput.addEventListener("input", function () {
            const password = passwordInput.value.trim();

            if (passwordRegex.test(password)) {
                passwordError.style.display = "none";
                passwordInput.classList.remove("input-error");
            }
        });
    </script>
</body>
</html>