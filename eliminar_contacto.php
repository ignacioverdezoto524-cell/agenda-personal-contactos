
<?php
session_start();
require_once "config/conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Solo permitir solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

// Obtener el ID del contacto
$id_contacto = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$id_usuario = $_SESSION["usuario_id"];

if (!$id_contacto) {
    header("Location: dashboard.php");
    exit;
}

// Eliminar únicamente si el contacto pertenece al usuario
$consulta = $conexion->prepare(
    "DELETE FROM contactos WHERE id = ? AND id_usuario = ?"
);

$consulta->bind_param("ii", $id_contacto, $id_usuario);
$consulta->execute();

if ($consulta->affected_rows > 0) {
    header("Location: dashboard.php?eliminacion=exitosa");
} else {
    header("Location: dashboard.php?eliminacion=no_encontrada");
}

$consulta->close();
$conexion->close();
exit;
?>
