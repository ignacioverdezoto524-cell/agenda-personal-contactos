
<?php

session_start();

require_once "config/conexion.php";

// Si ya inició sesión, ir al panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = strtolower(trim($_POST["correo"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (empty($correo) || empty($password)) {

        $error = "Debes completar todos los campos.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $error = "El correo electrónico no es válido.";

    } else {

        // Buscar usuario por correo
        $consulta = $conexion->prepare(
            "SELECT id, nombre, correo, password
             FROM usuarios
             WHERE correo = ?"
        );

        $consulta->bind_param("s", $correo);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($usuario = $resultado->fetch_assoc()) {

            // Verificar contraseña
            if (password_verify($password, $usuario["password"])) {

                // Regenerar identificador de sesión
                session_regenerate_id(true);

                // Guardar datos en la sesión
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["usuario_nombre"] = $usuario["nombre"];
                $_SESSION["usuario_correo"] = $usuario["correo"];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Correo o contraseña incorrectos.";

            }

        } else {

            $error = "Correo o contraseña incorrectos.";

        }

        $consulta->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>


<body>

    <div class="formulario">

        <h1>Iniciar sesión</h1>

        <?php if (!empty($error)): ?>
            <p style="color:red;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <label>Correo electrónico:</label>

            <input
                type="email"
                name="correo"
                placeholder="Ingresa tu correo"
                required
            >

            <label>Contraseña:</label>

            <input
                type="password"
                name="password"
                placeholder="Ingresa tu contraseña"
                required
            >

            <button type="submit">
                Ingresar
            </button>

        </form>

        <p>
            ¿No tienes una cuenta?
            <a href="registro.php">Regístrate aquí</a>
        </p>

    </div>

</body>


</html>
I