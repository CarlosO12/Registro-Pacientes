<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$tipo_mensaje = "";

if (isset($_GET['mensaje'])) {
    $mensajes_permitidos = ['eliminada', 'registrada', 'actualizada'];

    if (in_array($_GET['mensaje'], $mensajes_permitidos)) {
        switch ($_GET['mensaje']) {
            case 'eliminada':
                $mensaje = "Ficha eliminada con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'registrada':
                $mensaje = "Ficha registrada con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'actualizada':
                $mensaje = "Ficha actualizada con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'error':
                $mensaje = "Ocurrió un error. Intenta nuevamente.";
                $tipo_mensaje = "error";
                break;
            case 'errorid':
                $mensaje = "Ficha no encontrada.";
                $tipo_mensaje = "error";
                break;
        }
    }
}
$fichas = [];

$nombre = trim($_GET['paciente'] ?? '');

if ($nombre !== '') {
    $stmt = $conn->prepare("
        SELECT f.*, p.nombre 
        FROM fichas_medicas f
        JOIN pacientes p ON f.paciente_id = p.id
        WHERE p.nombre LIKE ?
        ORDER BY f.fecha DESC
    ");
    $stmt->execute(["%$nombre%"]);
} else {
    $stmt = $conn->prepare("
        SELECT f.*, p.nombre 
        FROM fichas_medicas f
        JOIN pacientes p ON f.paciente_id = p.id
        ORDER BY f.fecha DESC
    ");
    $stmt->execute();
}

$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($fichas) === 0) {
    $mensaje = "No se encontraron fichas para ese paciente.";
    $tipo_mensaje = 'error';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial Clínico</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/listar_ficha.css">
</head>

<body>

    <div class="top-bar">
        <h2>Historial Clínico</h2>
        <div class="buttons">
            <a href="../dashboard.php"><button class="btn-volver">← Volver</button></a>
            <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'medico'): ?>
                <a href="agregar.php"><button class="btn-primary">Nueva Ficha</button></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <div class="search-bar">
        <form method="get" action="listar.php">
            <div class="controls">
                <input type="text" name="paciente" placeholder="Buscar por nombre de paciente..." value="<?= htmlspecialchars($_GET['paciente'] ?? '') ?>">
                <button type="submit">🔍</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Motivo</th>
                <th>Diagnóstico</th>
                <th>Tratamiento</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fichas as $f): ?>
                <tr>
                    <td><?= date("d/m/Y H:i", strtotime($f['fecha'])) ?></td>
                    <td><?= htmlspecialchars($f['nombre']) ?></td>
                    <td><?= nl2br(htmlspecialchars($f['motivo'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($f['diagnostico'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($f['tratamiento'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($f['observaciones'])) ?></td>
                    <td class="actions">
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="editar.php?id=<?= $f['id'] ?>">Editar</a>
                            <a href="confirmar_eliminacion.php?id=<?= $f['id'] ?>">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script>
        setTimeout(() => {
            const mensaje = document.querySelector('.mensaje');
            if (mensaje) mensaje.style.display = 'none';
        }, 3000);
    </script>

    <script>
        const TIEMPO_MAX_INACTIVIDAD = 15 * 60 * 1000;
        let temporizador;

        function cerrarSesion() {
            window.location.href = '../logout.php?mensaje=sesion_expirada';
        }

        function reiniciarTemporizador() {
            clearTimeout(temporizador);
            temporizador = setTimeout(cerrarSesion, TIEMPO_MAX_INACTIVIDAD);
        }
        window.onload = reiniciarTemporizador;
        document.onmousemove = reiniciarTemporizador;
        document.onkeydown = reiniciarTemporizador;
        document.onclick = reiniciarTemporizador;
        document.onscroll = reiniciarTemporizador;
    </script>

</body>

</html>