<?php
require_once __DIR__ . '/connection/conect.php';
require_once __DIR__ . '/functions/product_functions.php';

$db = new Database();
$pdo = $db->conectar();

if ($pdo === null) {
    die('Error de conexion a la base de datos');
}

$tipos = obtenerTodosLosTipos($pdo);
$marcas=obtenerTodasLasMarcas($pdo);

$accion = $_GET['accion'] ?? 'menu';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // =========================
    // CREAR DISPOSITIVO
    // =========================
    if (isset($_POST['crear'])) {

        $id = intval($_POST['device_id'] ?? 0);

        $nombre = trim($_POST['device_name'] ?? '');

        $marca = ($_POST['brand_id'] !== '')
            ? intval($_POST['brand_id'])
            : null;

        $precio = ($_POST['device_price'] !== '')
            ? floatval($_POST['device_price'])
            : null;

        $tipo_id = ($_POST['type_id'] !== '')
            ? intval($_POST['type_id'])
            : null;

        $imagen = null;
        $alert_img = '';

        $rutaDestino = __DIR__ . '/img/';

        if (!is_dir($rutaDestino)) {
            mkdir($rutaDestino, 0777, true);
        }

        // =========================
        // SUBIR IMAGEN
        // =========================
        if (
            isset($_FILES['imagen']) &&
            $_FILES['imagen']['error'] === 0
        ) {

            $ext = strtolower(
                pathinfo(
                    $_FILES['imagen']['name'],
                    PATHINFO_EXTENSION
                )
            );

            $max = 3 * 1024 * 1024;

            if ($ext !== 'png') {

                $alert_img = "Solo se permiten archivos PNG";

            } elseif ($_FILES['imagen']['size'] > $max) {

                $alert_img = "La imagen supera los 3 MB";

            } else {

                $nombre_img = uniqid('img_') . '.png';

                if (
                    move_uploaded_file(
                        $_FILES['imagen']['tmp_name'],
                        $rutaDestino . $nombre_img
                    )
                ) {

                    $imagen = $nombre_img;

                } else {

                    $alert_img = "Error al guardar la imagen";
                }
            }
        }

        // =========================
        // INSERTAR
        // =========================
        if (
            $id > 0 &&
            $nombre !== '' &&
            $marca !== null
        ) {

            $nuevo = insertarDispositivo(
                $pdo,
                $id,
                $nombre,
                $precio,
                $tipo_id,
                $imagen,
                $marca
            );

            $mensaje = $nuevo
                ? "Dispositivo creado correctamente"
                : "Error al crear dispositivo";

        } else {

            $mensaje = "ID, nombre y marca son obligatorios";
        }
    }

    // =========================
    // ACTUALIZAR DISPOSITIVO
    // =========================
    elseif (isset($_POST['actualizar'])) {

        $id = intval($_POST['device_id'] ?? 0);

        $nombre = trim($_POST['device_name'] ?? '');

        $marca = ($_POST['brand_id'] !== '')
            ? intval($_POST['brand_id'])
            : null;

        $precio = ($_POST['device_price'] !== '')
            ? floatval($_POST['device_price'])
            : null;

        $tipo_id = ($_POST['type_id'] !== '')
            ? intval($_POST['type_id'])
            : null;

        $imagen = null;
        $alert_img = '';

        $rutaDestino = __DIR__ . '/img/';

        if (!is_dir($rutaDestino)) {
            mkdir($rutaDestino, 0777, true);
        }

        // =========================
        // NUEVA IMAGEN
        // =========================
        if (
            isset($_FILES['imagen']) &&
            $_FILES['imagen']['error'] === 0
        ) {

            $ext = strtolower(
                pathinfo(
                    $_FILES['imagen']['name'],
                    PATHINFO_EXTENSION
                )
            );

            $max = 3 * 1024 * 1024;

            if ($ext !== 'png') {

                $alert_img = "Solo se permiten archivos PNG";

            } elseif ($_FILES['imagen']['size'] > $max) {

                $alert_img = "La imagen supera los 3 MB";

            } else {

                $nombre_img = uniqid('img_') . '.png';

                if (
                    move_uploaded_file(
                        $_FILES['imagen']['tmp_name'],
                        $rutaDestino . $nombre_img
                    )
                ) {

                    $old = obtenerDispositivoPorId($pdo, $id);

                    if (
                        !empty($old['device_image']) &&
                        file_exists($rutaDestino . $old['device_image'])
                    ) {

                        unlink($rutaDestino . $old['device_image']);
                    }

                    $imagen = $nombre_img;

                } else {

                    $alert_img = "Error al guardar la imagen";
                }
            }
        }

        // =========================
        // BORRAR IMAGEN
        // =========================
        elseif (isset($_POST['borrar_imagen'])) {

            $old = obtenerDispositivoPorId($pdo, $id);

            if (
                !empty($old['device_image']) &&
                file_exists($rutaDestino . $old['device_image'])
            ) {

                unlink($rutaDestino . $old['device_image']);
            }

            $imagen = '';
        }

        // =========================
        // ACTUALIZAR
        // =========================
        if (
            $id > 0 &&
            $nombre !== '' &&
            $marca !== null &&
            $alert_img === ''
        ) {

            $ok = actualizarDispositivo(
                $pdo,
                $id,
                $nombre,
                $precio,
                $tipo_id,
                $imagen,
                $marca
            );

            $mensaje = $ok
                ? "Dispositivo actualizado correctamente"
                : "Error al actualizar";

        } else {

            $mensaje = $alert_img ?: "Datos inválidos";
        }
    }

    // =========================
    // ELIMINAR DISPOSITIVO
    // =========================
    elseif (isset($_POST['eliminar'])) {

        $id = intval($_POST['device_id'] ?? 0);

        if ($id > 0) {

            $ok = eliminarDispositivo($pdo, $id);

            $mensaje = $ok
                ? "Dispositivo eliminado correctamente"
                : "No se encontró el dispositivo";

        } else {

            $mensaje = "ID inválido";
        }
    }

    // =========================
    // CREAR TIPO
    // =========================
    elseif (isset($_POST['crear_tipo'])) {

        $nombre_tipo = trim($_POST['nombre_tipo']);

        if ($nombre_tipo !== '') {

            $res = crearTipo($pdo, $nombre_tipo);

            $mensaje = $res
                ? "Tipo creado correctamente"
                : "Error al crear tipo";
        }
    }

    // =========================
    // ACTUALIZAR TIPO
    // =========================
    elseif (isset($_POST['actualizar_tipo'])) {

        $id_tipo = intval($_POST['id_tipo']);

        $nombre_tipo = trim($_POST['nombre_tipo']);

        if ($id_tipo > 0 && $nombre_tipo !== '') {

            $res = actualizarTipo(
                $pdo,
                $id_tipo,
                $nombre_tipo
            );

            $mensaje = $res
                ? "Tipo actualizado"
                : "Error al actualizar";
        }
    }

    // =========================
    // ELIMINAR TIPO
    // =========================
    elseif (isset($_POST['eliminar_tipo'])) {

        $id_tipo = intval($_POST['id_tipo']);

        if ($id_tipo > 0) {

            $res = eliminarTipo($pdo, $id_tipo);

            $mensaje = $res
                ? "Tipo eliminado"
                : "Error al eliminar";
        }
    }

    // =========================
    // CREAR MARCA
    // =========================
    elseif (isset($_POST['crear_marca'])) {

        $nombre_marca = trim($_POST['nombre_marca']);

        if ($nombre_marca !== '') {

            $resm = crearMarcas($pdo, $nombre_marca);

            $mensaje = $resm
                ? "Marca creada correctamente"
                : "Error al crear marca";
        }
    }

    // =========================
    // ACTUALIZAR MARCA
    // =========================
    elseif (isset($_POST['actualizar_marca'])) {

        $id_marca = intval($_POST['id_marca']);

        $nombre_marca = trim($_POST['nombre_marca']);

        if ($id_marca > 0 && $nombre_marca !== '') {

            $resm = actualizarMarca(
                $pdo,
                $id_marca,
                $nombre_marca
            );

            $mensaje = $resm
                ? "Marca actualizada"
                : "Error al actualizar";
        }
    }

    // =========================
    // ELIMINAR MARCA
    // =========================
    elseif (isset($_POST['eliminar_marca'])) {

        $id_marca = intval($_POST['id_marca']);

        if ($id_marca > 0) {

            $resm = eliminarMarca($pdo, $id_marca);

            $mensaje = $resm
                ? "Marca eliminada"
                : "Error al eliminar";
        }
    }
}

// =========================
// LISTAR
// =========================
$device = [];

if ($accion === 'listar') {

    $device = obtenerTodosLosDispositivos($pdo);
}

// =========================
// EDITAR
// =========================
$dispositivo_editar = null;

if (
    $accion === 'editar_form' &&
    isset($_GET['device_id'])
) {

    $id_buscar = intval($_GET['device_id']);

    if ($id_buscar > 0) {

        $dispositivo_editar = obtenerDispositivoPorId(
            $pdo,
            $id_buscar
        );
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CRUD Dispositivos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container py-4">

        <h1 class="mb-4">CRUD de Dispositivos</h1>

        <?php if ($mensaje): ?>

            <div class="alert alert-info">
                <strong><?= htmlspecialchars($mensaje) ?></strong>
            </div>

            <a href="?accion=menu" class="btn btn-secondary">
                ← Volver al menú
            </a>

        <?php else: ?>

            <!-- ================= MENU ================= -->
            <?php if ($accion === 'menu'): ?>

                <div class="card shadow-sm">
                    <div class="card-body">

                        <h5 class="card-title mb-3">
                            <strong>Seleccione una opción:</strong>
                        </h5>

                        <ul class="list-group list-group-flush">

                            <li class="list-group-item">
                                <a href="?accion=listar"
                                    class="text-decoration-none text-warning">
                                    Listar Dispositivos
                                </a>
                            </li>

                            <li class="list-group-item">
                                <a href="?accion=crear_form"
                                    class="text-decoration-none text-primary">
                                    Crear Dispositivo
                                </a>
                            </li>

                            <li class="list-group-item">
                                <a href="?accion=editar_form"
                                    class="text-decoration-none text-danger">
                                    Actualizar Dispositivo
                                </a>
                            </li>

                            <li class="list-group-item">
                                <a href="?accion=eliminar_form"
                                    class="text-decoration-none text-warning">
                                    Eliminar Dispositivo
                                </a>
                            </li>

                            <li class="list-group-item">
                                <a href="?accion=type_Device"
                                    class="text-decoration-none text-primary">
                                    Gestionar Tipos
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="?accion=type_Device"
                                    class="text-decoration-none text-danger">
                                    Gestionar Marcas
                                </a>
                            </li>

                        </ul>

                    </div>
                </div>

                <!-- ================= LISTAR ================= -->
            <?php elseif ($accion === 'listar'): ?>

                <h2 class="mb-3">Listado de Dispositivos</h2>

                <?php if (count($device) > 0): ?>

                    <table class="table table-bordered table-striped align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($device as $d): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($d['device_id']) ?>
                                    </td>

                                    <td>

                                        <?php if (!empty($d['device_image'])): ?>

                                            <img
                                                src="img/<?= htmlspecialchars($d['device_image']) ?>"
                                                style="max-height:60px; border-radius:5px;"
                                                alt="Dispositivo">

                                        <?php else: ?>

                                            <span class="text-muted">
                                                Sin imagen
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars($d['device_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($d['device_brand']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($d['type_name'] ?? 'Sin tipo') ?>
                                    </td>

                                    <td>
                                        $<?= number_format($d['device_price'], 2) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="alert alert-info">
                        No hay dispositivos registrados.
                    </div>

                <?php endif; ?>

                <a href="?accion=menu" class="btn btn-secondary mt-3">
                    ← Volver al menú
                </a>

                <!-- ================= CREAR ================= -->
            <?php elseif ($accion === 'crear_form'): ?>

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h2 class="card-title mb-3">
                            Nuevo Dispositivo
                            <i class="fa-solid fa-circle-plus"></i>
                        </h2>

                        <form action="" method="POST" enctype="multipart/form-data">

                            <div class="mb-3">
                                <label class="form-label">ID:</label>

                                <input
                                    type="number"
                                    name="device_id"
                                    required
                                    class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre:</label>

                                <input
                                    type="text"
                                    name="device_name"
                                    required
                                    maxlength="100"
                                    class="form-control">
                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Marca del dispositivo:
                                </label>

                                <select name="brand_id" class="form-control">

                                    <option value="">
                                        Sin Marca
                                    </option>

                                    <?php foreach ($marcas as $m): ?>

                                        <option value="<?= $m['brand_id'] ?>">

                                            <?= htmlspecialchars($m['brand_name']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio:</label>

                                <input
                                    type="number"
                                    step="0.01"
                                    name="device_price"
                                    class="form-control">
                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Tipo de dispositivo:
                                </label>

                                <select name="type_id" class="form-control">

                                    <option value="">
                                        Sin tipo
                                    </option>

                                    <?php foreach ($tipos as $t): ?>

                                        <option value="<?= $t['type_id'] ?>">

                                            <?= htmlspecialchars($t['type_name']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Imagen (.png):
                                </label>

                                <input
                                    type="file"
                                    name="imagen"
                                    class="form-control"
                                    accept=".png">

                            </div>

                            <button
                                type="submit"
                                name="crear"
                                class="btn btn-primary">

                                Guardar

                            </button>

                            <a href="?accion=menu"
                                class="btn btn-secondary">

                                Cancelar

                            </a>

                        </form>

                    </div>
                </div>

                <!-- ================= BUSCAR EDITAR ================= -->
            <?php elseif ($accion === 'editar_form' && !$dispositivo_editar): ?>

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h2 class="card-title mb-3">
                            Editar Dispositivo
                        </h2>

                        <form action="" method="GET">

                            <input
                                type="hidden"
                                name="accion"
                                value="editar_form">

                            <div class="mb-3">

                                <label class="form-label">
                                    ID del dispositivo:
                                </label>

                                <input
                                    type="number"
                                    name="device_id"
                                    required
                                    min="1"
                                    class="form-control">

                            </div>

                            <button type="submit"
                                class="btn btn-primary">

                                Buscar

                            </button>

                            <a href="?accion=menu"
                                class="btn btn-secondary">

                                Cancelar

                            </a>

                        </form>

                    </div>
                </div>

                <!-- ================= FORM EDITAR ================= -->
            <?php elseif ($accion === 'editar_form' && $dispositivo_editar): ?>

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h2 class="card-title mb-3">
                            Editar dispositivo #<?= htmlspecialchars($dispositivo_editar['device_id']) ?>
                        </h2>

                        <form action=""
                            method="POST"
                            enctype="multipart/form-data">

                            <input
                                type="hidden"
                                name="device_id"
                                value="<?= htmlspecialchars($dispositivo_editar['device_id']) ?>">

                            <div class="mb-3">

                                <label class="form-label">Nombre:</label>

                                <input
                                    type="text"
                                    name="device_name"
                                    value="<?= htmlspecialchars($dispositivo_editar['device_name']) ?>"
                                    required
                                    class="form-control">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Marca:</label>

                                <input
                                    type="text"
                                    name="device_brand"
                                    value="<?= htmlspecialchars($dispositivo_editar['device_brand']) ?>"
                                    required
                                    class="form-control">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Precio:</label>

                                <input
                                    type="number"
                                    step="0.01"
                                    name="device_price"
                                    value="<?= htmlspecialchars($dispositivo_editar['device_price']) ?>"
                                    class="form-control">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Tipo:
                                </label>

                                <select
                                    name="type_id"
                                    class="form-control">

                                    <?php foreach ($tipos as $t): ?>

                                        <option
                                            value="<?= $t['type_id'] ?>"
                                            <?= ($dispositivo_editar['type_id'] == $t['type_id']) ? 'selected' : '' ?>>

                                            <?= htmlspecialchars($t['type_name']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Imagen actual:
                                </label>

                                <?php if ($dispositivo_editar['device_image']): ?>

                                    <img
                                        src="img/<?= htmlspecialchars($dispositivo_editar['device_image']) ?>"
                                        class="d-block mb-2"
                                        style="max-height:100px; border-radius:5px;">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="borrar_imagen"
                                            id="delImg">

                                        <label
                                            class="form-check-label"
                                            for="delImg">

                                            Eliminar imagen actual

                                        </label>

                                    </div>

                                <?php else: ?>

                                    <span class="text-muted">
                                        Sin imagen
                                    </span>

                                <?php endif; ?>

                                <label class="form-label mt-2">
                                    Nueva imagen:
                                </label>

                                <input
                                    type="file"
                                    name="imagen"
                                    class="form-control"
                                    accept=".png">

                            </div>

                            <button
                                type="submit"
                                name="actualizar"
                                class="btn btn-warning">

                                Actualizar

                            </button>

                            <a href="?accion=menu"
                                class="btn btn-secondary">

                                Cancelar

                            </a>

                        </form>

                    </div>
                </div>

                <!-- ================= ELIMINAR ================= -->
            <?php elseif ($accion === 'eliminar_form'): ?>

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h2 class="card-title mb-3">
                            Eliminar Dispositivo
                            <i class="fa-solid fa-trash-can"></i>
                        </h2>

                        <form action="" method="POST">

                            <div class="mb-3">

                                <label class="form-label">
                                    ID del dispositivo:
                                </label>

                                <input
                                    type="number"
                                    name="device_id"
                                    required
                                    min="1"
                                    class="form-control">

                            </div>

                            <button
                                type="submit"
                                name="eliminar"
                                class="btn btn-danger"
                                onclick="return confirm('¿Eliminar dispositivo?')">

                                Eliminar

                            </button>

                            <a href="?accion=menu"
                                class="btn btn-secondary">

                                Cancelar

                            </a>

                        </form>

                    </div>
                </div>

                <!-- ================= TIPOS ================= -->
            <?php elseif ($accion === 'type_Device'):

                $lista_tipos = obtenerTodosLosTipos($pdo);

            ?>

                <h2 class="mb-3">Tipos de dispositivos</h2>

                <div class="card mb-3 bg-light">

                    <div class="card-body">

                        <form
                            method="POST"
                            class="d-flex gap-2">

                            <input
                                type="text"
                                name="nombre_tipo"
                                class="form-control"
                                placeholder="Nuevo tipo"
                                required>

                            <button
                                type="submit"
                                name="crear_tipo"
                                class="btn btn-success">

                                + Agregar

                            </button>

                        </form>

                    </div>
                </div>

                <?php if (count($lista_tipos) > 0): ?>

                    <table class="table table-hover">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($lista_tipos as $tipo): ?>

                                <tr>

                                    <td>
                                        <?= $tipo['type_id'] ?>
                                    </td>

                                    <td>

                                        <form
                                            method="POST"
                                            class="d-flex gap-2">

                                            <input
                                                type="hidden"
                                                name="id_tipo"
                                                value="<?= $tipo['type_id'] ?>">

                                            <input
                                                type="text"
                                                name="nombre_tipo"
                                                value="<?= htmlspecialchars($tipo['type_name']) ?>"
                                                class="form-control form-control-sm"
                                                required>

                                            <button
                                                type="submit"
                                                name="actualizar_tipo"
                                                class="btn btn-primary btn-sm">

                                                Guardar

                                            </button>

                                        </form>

                                    </td>

                                    <td>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('¿Eliminar tipo?');">

                                            <input
                                                type="hidden"
                                                name="id_tipo"
                                                value="<?= $tipo['type_id'] ?>">

                                            <button
                                                type="submit"
                                                name="eliminar_tipo"
                                                class="btn btn-danger btn-sm">

                                                Eliminar

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="alert alert-info">
                        No hay tipos registrados.
                    </div>

                <?php endif; ?>

                <a href="?accion=menu"
                    class="btn btn-secondary mt-3">

                    ← Volver al menú

                </a>

            <?php endif; ?>

        <?php endif; ?>

    </div>

</body>

</html>