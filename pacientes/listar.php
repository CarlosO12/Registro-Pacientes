<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$tipo_mensaje = '';

if (isset($_GET['mensaje'])) {
    $mensajes_permitidos = ['eliminado', 'registrado', 'actualizado', 'error', 'errorpaciente', 'errorconsultas', 'errorcitas'];

    if (in_array($_GET['mensaje'], $mensajes_permitidos)) {
        switch ($_GET['mensaje']) {
            case 'eliminado':
                $mensaje = "Paciente eliminado con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'registrado':
                $mensaje = "Paciente registrado con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'actualizado':
                $mensaje = "Paciente actualizado con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'error':
                $mensaje = "Ocurrió un error. Intenta nuevamente.";
                $tipo_mensaje = "error";
                break;
            case 'errorpaciente':
                $mensaje = "Paciente no encontrado.";
                $tipo_mensaje = "error";
                break;
            case 'errorconsultas':
                $mensaje = "Lo sentimos, no se puede eliminar: el paciente tiene consultas registradas.";
                $tipo_mensaje = "error";
                break;
            case 'errorcitas':
                $mensaje = "Lo sentimos, no se puede eliminar: el paciente tiene citas agendadas.";
                $tipo_mensaje = "error";
                break;
        }
    }
}

$pacientes = [];
$por_pagina = 6;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $por_pagina;

$cedula = trim($_GET['cedula'] ?? '');

if ($cedula !== '') {
    // Si hay búsqueda por cédula
    $stmt = $conn->prepare("SELECT * FROM pacientes WHERE cedula = ?");
    $stmt->execute([$cedula]);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($pacientes) === 0) {
        $mensaje = "No se encontró ningún paciente con esa cédula.";
        $tipo_mensaje = 'error';
    }

    $total_paginas = 0;
} else {
    $total_stmt = $conn->query("SELECT COUNT(*) FROM pacientes");
    $total = $total_stmt->fetchColumn();
    $total_paginas = ceil($total / $por_pagina);

    $stmt = $conn->prepare("SELECT * FROM pacientes ORDER BY nombre LIMIT :inicio, :cantidad");
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindValue(':cantidad', $por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Pacientes</title>
    <link rel="stylesheet" href="../css/listar.css">
</head>

<body>

    <div class="top-bar">
        <h2>Listado de pacientes</h2>
        <div style="display: flex; gap: 10px;">
            <a href="../dashboard.php"><button class="volver">← Volver</button></a>
            <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'recepcionista'): ?>
                <a href="agregar.php"><button class="registrar">Registrar paciente</button></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <div class="search-bar">
        <form method="get" action="listar.php">
            <input type="text" name="cedula" placeholder="Buscar por cédula..." value="<?= isset($_GET['cedula']) ? htmlspecialchars($_GET['cedula']) : '' ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cedula</th>
                <th>Edad</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= $p['cedula'] ?></td>
                    <td><?= $p['edad'] ?></td>
                    <td><?= $p['contacto'] ?></td>
                    <td class="actions">
                        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'recepcionista'): ?>
                            <a href="editar.php?id=<?= $p['id'] ?>">Editar</a>
                        <?php endif; ?>
                        <a href="historial.php?id=<?= $p['id'] ?>">Ver historial</a>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="confirmar_eliminacion.php?id=<?= $p['id'] ?>">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_paginas > 1): ?>
        <div class="pagination">
            <?php if ($pagina > 1): ?>
                <a href="?pagina=<?= $pagina - 1 ?>&cedula=">Anterior </a>
                <span> | </span>
            <?php else: ?>
                <span class="disabled">Anterior </span>
                <span class="disabled"> | </span>
            <?php endif; ?>

            <?php if ($pagina < $total_paginas): ?>
                <a href="?pagina=<?= $pagina + 1 ?>&cedula=">Siguiente</a>
            <?php else: ?>
                <span class="disabled">Siguiente</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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