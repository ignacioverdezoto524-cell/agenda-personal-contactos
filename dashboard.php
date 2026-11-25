
<?php

session_start();

require_once "config/conexion.php";
require_once "config/seguridad.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener el usuario autenticado
$id_usuario = $_SESSION["usuario_id"];

// Obtener los parámetros de búsqueda y ordenamiento
$busqueda = trim($_GET["buscar"] ?? "");
$orden = $_GET["orden"] ?? "recientes";

// Opciones de ordenamiento permitidas
$ordenamientos = [
    "recientes" => "id DESC",
    "nombre_asc" => "nombre ASC",
    "nombre_desc" => "nombre DESC",
    "ciudad_asc" => "ciudad ASC",
    "ciudad_desc" => "ciudad DESC"
];

// Validar el orden seleccionado
if (!array_key_exists($orden, $ordenamientos)) {
    $orden = "recientes";
}

$orden_sql = $ordenamientos[$orden];

// Construir consulta SQL
$sql = "SELECT id, nombre, telefono, correo, ciudad
        FROM contactos
        WHERE id_usuario = ?";

if ($busqueda !== "") {
    $sql .= " AND (
        nombre LIKE ?
        OR correo LIKE ?
        OR ciudad LIKE ?
    )";
}

$sql .= " ORDER BY " . $orden_sql;

// Preparar consulta
$consulta = $conexion->prepare($sql);

if (!$consulta) {
    die("Error al preparar la consulta: " . $conexion->error);
}

if ($busqueda !== "") {

    $termino = "%" . $busqueda . "%";

    $consulta->bind_param(
        "isss",
        $id_usuario,
        $termino,
        $termino,
        $termino
    );

} else {

    $consulta->bind_param("i", $id_usuario);

}

// Ejecutar consulta
$consulta->execute();
$contactos = $consulta->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi Agenda Personal</title>

    <link rel="stylesheet" href="css/estilos.css">

</head>

<body>

    <h1>Mi agenda personal de contactos</h1>

    <h2>
        Bienvenido,
        <?= htmlspecialchars($_SESSION["usuario_nombre"]) ?>
    </h2>

    <p>
        Correo:
        <?= htmlspecialchars($_SESSION["usuario_correo"]) ?>
    </p>

    <hr>

    <h2>Mis contactos</h2>

    <!-- Mensajes de operaciones -->

    <?php if (isset($_GET["registro"]) && $_GET["registro"] == "exitoso"): ?>

        <p style="color:green;">
            ¡Contacto registrado correctamente!
        </p>

    <?php endif; ?>

    <?php if (isset($_GET["edicion"]) && $_GET["edicion"] == "exitosa"): ?>

        <p style="color:green;">
            ¡Contacto actualizado correctamente!
        </p>

    <?php endif; ?>

    <?php if (isset($_GET["eliminacion"]) && $_GET["eliminacion"] == "exitosa"): ?>

        <p style="color:green;">
            ¡Contacto eliminado correctamente!
        </p>

    <?php endif; ?>

    <?php if (isset($_GET["eliminacion"]) && $_GET["eliminacion"] == "no_encontrada"): ?>

        <p style="color:red;">
            No se encontró el contacto para eliminar.
        </p>

    <?php endif; ?>

    <!-- Agregar contacto -->

    <p>
        <a href="agregar_contacto.php">
            + Agregar nuevo contacto
        </a>
    </p>

    <!-- BUSQUEDA Y ORDENAMIENTO -->

    <div class="filtros">

        <h3>Buscar y ordenar contactos</h3>

        <form method="GET" action="dashboard.php">

            <label for="buscar">
                Buscar por nombre, correo o ciudad:
            </label>

            <input
                type="text"
                id="buscar"
                name="buscar"
                placeholder="Escribe un nombre, correo o ciudad"
                value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="orden">
                Ordenar contactos:
            </label>

            <select name="orden" id="orden">

                <option value="recientes"
                    <?= $orden == "recientes" ? "selected" : "" ?>>
                    Más recientes
                </option>

                <option value="nombre_asc"
                    <?= $orden == "nombre_asc" ? "selected" : "" ?>>
                    Nombre: A - Z
                </option>

                <option value="nombre_desc"
                    <?= $orden == "nombre_desc" ? "selected" : "" ?>>
                    Nombre: Z - A
                </option>

                <option value="ciudad_asc"
                    <?= $orden == "ciudad_asc" ? "selected" : "" ?>>
                    Ciudad: A - Z
                </option>

                <option value="ciudad_desc"
                    <?= $orden == "ciudad_desc" ? "selected" : "" ?>>
                    Ciudad: Z - A
                </option>

            </select>

            <div class="acciones-filtros">

                <button type="submit">
                    Buscar y ordenar
                </button>

                <a href="dashboard.php" class="btn-volver">
                    Limpiar filtros
                </a>

            </div>

        </form>

    </div>

    <br>

    <!-- RESULTADOS -->

    <?php if ($contactos->num_rows > 0): ?>

        <p>
            Contactos encontrados:
            <strong><?= $contactos->num_rows ?></strong>
        </p>

        <table>

            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Ciudad</th>
                <th>Acciones</th>
            </tr>

            <?php while ($contacto = $contactos->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($contacto["nombre"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($contacto["telefono"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($contacto["correo"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($contacto["ciudad"]) ?>
                    </td>

                    <td>

                        <a href="editar_contacto.php?id=<?= (int)$contacto['id'] ?>">
                            Editar
                        </a>

                        <form
                            action="eliminar_contacto.php"
                            method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('¿Estás seguro de eliminar este contacto?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int)$contacto['id'] ?>"
                            >

                            <button type="submit">
                                Eliminar
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <?php if ($busqueda !== ""): ?>

            <p>No se encontraron contactos con esa búsqueda.</p>

        <?php else: ?>

            <p>Todavía no tienes contactos registrados.</p>

        <?php endif; ?>

    <?php endif; ?>

    <br>

    <a href="logout.php">Cerrar sesión</a>

</body>

</html>

<?php

$consulta->close();

?>
