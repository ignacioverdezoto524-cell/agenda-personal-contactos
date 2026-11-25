
<?php

session_start();

require_once "config/conexion.php";
require_once "config/seguridad.php";

// Verificar que exista una sesión activa
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verificarTokenCSRF();

    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = strtolower(trim($_POST["correo"] ?? ""));
    $ciudad = trim($_POST["ciudad"] ?? "");

    // Validar los campos
    if (
        empty($nombre) ||
        empty($telefono) ||
        empty($correo) ||
        empty($ciudad)
    ) {

        $error = "Todos los campos son obligatorios.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $error = "El correo electrónico no es válido.";

    } else {

        // Obtener el ID del usuario autenticado
        $id_usuario = $_SESSION["usuario_id"];

        // Insertar el contacto en la base de datos
        $consulta = $conexion->prepare(
            "INSERT INTO contactos
            (id_usuario, nombre, telefono, correo, ciudad)
            VALUES (?, ?, ?, ?, ?)"
        );

        $consulta->bind_param(
            "issss",
            $id_usuario,
            $nombre,
            $telefono,
            $correo,
            $ciudad
        );

        if ($consulta->execute()) {

            $consulta->close();

            header("Location: dashboard.php?registro=exitoso");
            exit;

        } else {

            $error = "No se pudo registrar el contacto.";
        }

        $consulta->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar contacto</title>
    <link rel="stylesheet" href="css/estilos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <div class="formulario">
    <h1>Registrar nuevo contacto</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Nombre completo:</label><br>
        <input type="text" name="nombre"
               maxlength="150" required>
        <br><br>

        <label>Teléfono:</label><br>
        <input type="text" name="telefono"
               maxlength="20" required>
        <br><br>

        <label>Correo electrónico:</label><br>
        <input type="email" name="correo"
               maxlength="150" required>
        <br><br>

        <label>Ciudad:</label><br>
        <input type="text" name="ciudad"
               maxlength="100" required>
        <br><br>

        <button type="submit">
            Guardar contacto
        </button>

    </form>

    <br>

           <a href="dashboard.php" class="btn-volver">
            Volver a mi agenda
        </a>

    </div>

</body>
</html>
