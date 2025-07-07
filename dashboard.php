<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Registro de Pacientes</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .tarjeta-contenedor {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .tarjeta {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.4s ease;
        }

        .tarjeta:hover {
            background-color: rgb(227, 226, 226);
        }

        .tarjeta a {
            text-decoration: none;
            color: #1a73e8;
            font-weight: bold;
        }

        .tarjeta-usuario {
            position: absolute;
            left: 40px;
            top: 40px;
            background-color: #f5f5f5;
            border-left: 4px solid #1a73e8;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            font-size: 14px;
            width: 220px;
            color: #333;
        }

        .tarjeta-usuario strong {
            color: #000;
        }
    </style>
</head>

<body>
    <?php include 'includes/auth.php'; ?>
    <div class="tarjeta-usuario">
        <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['usuario']) ?></p>
        <p><strong>Rol:</strong> <?= ucfirst($_SESSION['rol']) ?></p>
    </div>
    <h1>Panel Principal | Sistema de Registro de Pacientes</h1>
    <p>Seleccione una opción:</p>

    <div class="tarjeta-contenedor">
        <?php if (es_admin() || es_recepcionista() || es_medico()): ?>
            <div class="tarjeta">
                <h3>Pacientes</h3>
                <a href="pacientes/listar.php">Ver Pacientes</a>
            </div>
        <?php endif; ?>

        <?php if (es_admin() || es_medico()): ?>
            <div class="tarjeta">
                <h3>Consultas</h3>
                <a href="consultas/agregar.php">Registrar Consulta</a>
            </div>
        <?php endif; ?>

        <?php if (es_admin()): ?>
            <div class="tarjeta">
                <h3>Usuarios</h3>
                <a href="usuarios/listar.php">Gestionar Usuarios</a>
            </div>
        <?php endif; ?>

        <?php if (es_admin() || es_recepcionista() || es_medico()): ?>
            <div class="tarjeta">
                <h3>Citas</h3>
                <a href="citas/listar.php">Agendar Cita</a>
            </div>
        <?php endif; ?>

        <?php if (es_admin() || es_medico() || es_recepcionista()): ?>
            <div class="tarjeta">
                <h3>Fichas Medicas</h3>
                <a href="fichas_medicas/listar.php">Consultar Fichas</a>
            </div>
        <?php endif; ?>

        <?php if (es_admin() || es_recepcionista() || es_medico()): ?>
            <div class="tarjeta">
                <h3>Cerrar sesion</h3>
                <a href="logout.php">Cerrar</a>
            </div>
        <?php endif; ?>

    </div>

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