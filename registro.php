
<?php

require_once "config/conexion.php";

$error = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $correo = strtolower(trim($_POST["correo"] ?? ""));
    $password = $_POST["password"] ?? "";

    // Validar campos
    if (empty($nombre) || empty($correo) || empty($password)) {

        $error = "Todos los campos son obligatorios.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $error = "El correo electrónico no es válido.";

    } elseif (strlen($password) < 8) {

        $error = "La contraseña debe tener al menos 8 caracteres.";

    } else {

        // Comprobar si el correo ya existe
        $consulta = $conexion->prepare(
            "SELECT id FROM usuarios WHERE correo = ?"
        );

        $consulta->bind_param("s", $correo);
        $consulta->execute();
        $consulta->store_result();

        if ($consulta->num_rows > 0) {

            $error = "Este correo electrónico ya está registrado.";

        } else {

            // Proteger contraseña
            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Registrar usuario
            $insertar = $conexion->prepare(
                "INSERT INTO usuarios (nombre, correo, password)
                 VALUES (?, ?, ?)"
            );

            $insertar->bind_param(
                "sss",
                $nombre,
                $correo,
                $password_hash
            );

            if ($insertar->execute()) {

                $exito = "¡Usuario registrado correctamente!";

            } else {

                $error = "No se pudo completar el registro.";

            }

            $insertar->close();
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
    <title>Crear cuenta</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>


<body>

    <div class="formulario">

        <h1>Crear una cuenta</h1>

        <?php if (!empty($error)): ?>
            <p style="color:red;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <p style="color:green;">
                <?= htmlspecialchars($exito) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <label>Nombre completo:</label>

            <input
                type="text"
                name="nombre"
                maxlength="150"
                placeholder="Ingresa tu nombre completo"
                required
            >

            <label>Correo electrónico:</label>

            <input
                type="email"
                name="correo"
                maxlength="150"
                placeholder="ejemplo@correo.com"
                required
            >

            <label>Contraseña:</label>

            <input
                type="password"
                name="password"
                minlength="8"
                placeholder="Mínimo 8 caracteres"
                required
            >

            <button type="submit">Registrarme</button>

        </form>

        <p>
            ¿Ya tienes una cuenta?
            <a href="login.php">Inicia sesión aquí</a>
        </p>

    </div>

</body>

</html>
