<?php
date_default_timezone_set("America/Bogota");
session_start();
include 'includes/conexion.php';

$error = "";

// Si ya está logueado, redirigir no se cierra la sesion
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $clave = $_POST['clave'];

    if (empty($usuario) || empty($clave)) {
        $error = "Debes ingresar usuario y contraseña.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($clave, $user['contraseña_hash'])) {
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['usuario_id'] = $user['id'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Credenciales inválidas.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <div class="login">
        <h2>Iniciar Sesión</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'sesion_expirada'): ?>
            <p class="error">Tu sesión ha expirado por inactividad.</p>
        <?php endif; ?>

        <form method="post">
            <label>Usuario:</label>
            <input type="text" name="usuario" required>

            <label>Contraseña:</label>
            <input type="password" name="clave" required>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <script>
        setTimeout(() => {
            const mensaje = document.querySelector('.error');
            if (mensaje) mensaje.style.display = 'none';
        }, 3000);
    </script>

</body>

</html>