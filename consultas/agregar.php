<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$error = false;
$tipo_mensaje = '';

$pacientes = $conn->query("SELECT id, nombre FROM pacientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paciente_id = intval($_POST["paciente_id"]);
    $fecha = date("Y-m-d H:i:s");
    $motivo = trim($_POST["motivo"]);
    $diagnostico = trim($_POST["diagnostico"]);
    $tratamiento = trim($_POST["tratamiento"]);

    // Validación de paciente
    if ($paciente_id <= 0) {
        $mensaje = "Debe seleccionar un paciente válido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de motivo
    if (strlen($motivo) < 10) {
        $mensaje = "El motivo debe tener al menos 10 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de diagnóstico
    if (strlen($diagnostico) < 10) {
        $mensaje = "El diagnóstico debe tener al menos 10 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de tratamiento
    if (strlen($tratamiento) < 5) {
        $mensaje = "El tratamiento debe tener al menos 5 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Solo insertar en la base de datos si no hay errores
    if (!$error) {
        $sql = "INSERT INTO consultas (paciente_id, fecha, motivo, diagnostico, tratamiento)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$paciente_id, $fecha, $motivo, $diagnostico, $tratamiento])) {
            $mensaje = "Consulta registrada con éxito.";
            $tipo_mensaje = 'exito';
        } else {
            $mensaje = "Error al registrar la consulta.";
            $tipo_mensaje = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Consulta Médica</title>
    <link rel="stylesheet" href="../css/consulta.css">
</head>
<body>
    <h2>Registrar Consulta Médica</h2>
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <?php if (!empty($mensaje) && $tipo_mensaje === 'exito'): ?>
        <script>
            setTimeout(() => {
                window.location.href = "../dashboard.php";
            }, 2000);
        </script>
    <?php endif; ?>

    <form method="post">
        <label>Paciente:</label>
        <select name="paciente_id" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
            <?php endforeach; ?>
        </select>

        <label>Motivo de consulta:</label>
        <textarea name="motivo" value="<?= htmlspecialchars($motivo ?? '') ?>" required></textarea>

        <label>Diagnóstico:</label>
        <textarea name="diagnostico" value="<?= htmlspecialchars($diagnostico ?? '') ?>" required></textarea>

        <label>Tratamiento:</label>
        <textarea name="tratamiento" value="<?= htmlspecialchars($tratamiento ?? '') ?>" required></textarea>

        <button style="margin-top: 10px; border-radius: 10px;" type="submit">Guardar Consulta</button>
        <a href="../dashboard.php" class="btn-volver">← Volver</a>
    </form>

    <script>
        setTimeout(() => {
            const mensaje = document.querySelector('.mensaje');
            if (mensaje) mensaje.style.display = 'none';
        }, 3000);
    </script>
</body>
</html>
