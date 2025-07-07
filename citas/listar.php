<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$hoy = date("Y-m-d");

$stmt = $conn->prepare("
    SELECT c.id, c.hora, c.medico_asignado, c.estado, p.nombre
    FROM citas c
    JOIN pacientes p ON c.paciente_id = p.id
    WHERE c.fecha = ?
    ORDER BY c.hora ASC
");

$stmt->execute([$hoy]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";
$tipo_mensaje = '';

if (isset($_GET['mensaje'])) {
    $mensajes_permitidos = ['registrada', 'actualizada', 'error'];

    if (in_array($_GET['mensaje'], $mensajes_permitidos)) {
        switch ($_GET['mensaje']) {
            case 'registrada':
                $mensaje = "Cita creada con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'actualizada':
                $mensaje = "Cita actualizada con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'error':
                $mensaje = "Ocurrió un error. Intenta nuevamente.";
                $tipo_mensaje = "error";
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Citas para Hoy</title>
    <link rel="stylesheet" href="../css/listar_cita.css">
</head>

<body>
    <div class="top-bar">
        <h2>Citas Médicas - <?= date("d/m/Y") ?></h2>
        <div class="buttons">
            <a href="../dashboard.php"><button class="btn-volver">← Volver</button></a>
            <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'recepcionista'): ?>
                <a href="agendar.php"><button class="btn-primary">Nueva cita</button></a>
                <a href="todas.php"><button class="btn-primary">Ver todas las citas</button></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= date("g:i A", strtotime($c['hora'])) ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= $c['medico_asignado'] ?></td>
                    <td class="estado <?= $c['estado'] ?>"><?= $c['estado'] ?></td>
                    <td class="actions">
                        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'recepcionista'): ?>
                            <a href="editar.php?id=<?= $c['id'] ?>">Editar</a>
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