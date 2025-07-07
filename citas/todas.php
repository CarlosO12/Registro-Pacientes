<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$parametros = [];
$filtro = trim($_GET['buscar'] ?? '');

$sql = "SELECT c.id, c.fecha, c.hora, c.medico_asignado, c.estado, p.nombre 
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        WHERE 1=1";

// Si hay algo escrito en el buscador
if ($filtro !== '') {
    $sql .= " AND (
        p.nombre LIKE ? OR
        c.medico_asignado LIKE ? OR
        c.estado = ?
    )";
    $parametros = [
        '%' . $filtro . '%',
        '%' . $filtro . '%',
        strtolower($filtro)
    ];
}

$sql .= " ORDER BY c.fecha DESC, c.hora ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($parametros);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($citas) === 0) {
    $mensaje = "No se encontraron citas con ese criterio.";
    $tipo_mensaje = 'error';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Todas las Citas</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/listar_todas.css">
</head>
<body>

    <div class="top-bar">
        <h2>Todas las Citas</h2>
        <div class="buttons">
            <a href="../citas/listar.php"><button class="btn-volver">← Volver</button></a>
        </div> 
    </div>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <div class="search-bar">
        <form method="get" class="filtro-citas">
            <div class="controls">
                <input type="text" name="buscar" placeholder="Buscar por paciente, médico o estado..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                <button type="submit">🔍</button>
            </div>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= date("d/m/Y", strtotime($c['fecha'])) ?></td>
                    <td><?= date("g:i A", strtotime($c['hora'])) ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['medico_asignado']) ?></td>
                    <td>
                        <?php
                            switch ($c['estado']) {
                                case 'programada':  $color = '#1a73e8'; break;
                                case 'completada':  $color = '#2e7d32'; break;
                                case 'cancelada':   $color = '#d32f2f'; break;
                                default:            $color = '#666';
                            }
                        ?>
                        <span style="color: <?= $color ?>; font-weight: bold;"><?= ucfirst($c['estado']) ?></span>
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