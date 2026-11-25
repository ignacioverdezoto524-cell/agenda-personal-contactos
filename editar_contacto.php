
<?php

session_start();

require_once "config/conexion.php";
require_once "config/seguridad.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["usuario_id"];
$id_contacto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_contacto || $id_contacto <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Buscar contacto verificando su propietario
$consulta = $conexion->prepare(
    "SELECT * FROM contactos
     WHERE id = ? AND id_usuario = ?"
);

$consulta->bind_param("ii", $id_contacto, $id_usuario);
$consulta->execute();

$resultado = $consulta->get_result();
$contacto = $resultado->fetch_assoc();
$consulta->close();

if (!$contacto) {
    http_response_code(404);
    exit("Contacto no encontrado.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verificarTokenCSRF();

    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = strtolower(trim($_POST["correo"] ?? ""));
    $ciudad = trim($_POST["ciudad"] ?? "");

    if (
        empty($nombre) ||
        empty($telefono) ||
        empty($correo) ||
        empty($ciudad)
    ) {

        $error = "Todos los campos son obligatorios.";

    } elseif (
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {

        $error = "El correo no es válido.";

    } else {

        $actualizar = $conexion->prepare(
            "UPDATE contactos
             SET nombre = ?, telefono = ?, correo = ?, ciudad = ?
             WHERE id = ? AND id_usuario = ?"
        );

        $actualizar->bind_param(
            "ssssii",
            $nombre,
            $telefono,
            $correo,
            $ciudad,
            $id_contacto,
            $id_usuario
        );

        if ($actualizar->execute()) {

            $actualizar->close();

            header("Location: dashboard.php?edicion=exitosa");
            exit;

        } else {

            $error = "No se pudo actualizar el contacto.";
            $actualizar->close();
        }
    }

    // Mantener los datos ingresados
    $contacto["nombre"] = $nombre;
    $contacto["telefono"] = $telefono;
    $contacto["correo"] = $correo;
    $contacto["ciudad"] = $ciudad;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar contacto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/estilos.css">
</head>

<body>
<div class="formulario">
    <h1>Editar contacto</h1>

    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Nombre:</label><br>
        <input type="text" name="nombre"
        value="<?= htmlspecialchars($contacto["nombre"]) ?>"
        maxlength="150" required>

        <br><br>

        <label>Teléfono:</label><br>
        <input type="text" name="telefono"
        value="<?= htmlspecialchars($contacto["telefono"]) ?>"
        maxlength="20" required>

        <br><br>

        <label>Correo:</label><br>
        <input type="email" name="correo"
        value="<?= htmlspecialchars($contacto["correo"]) ?>"
        maxlength="150" required>

        <br><br>

        <label>Ciudad:</label><br>
        <input type="text" name="ciudad"
        value="<?= htmlspecialchars($contacto["ciudad"]) ?>"
        maxlength="100" required>

        <br><br>

        <button type="submit">Guardar cambios</button>

    </form>

    <br>
    <a href="dashboard.php" class="btn-volver">
        Volver a mi agenda
    </a>
</div>
</html>
