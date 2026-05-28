<?php

// =========================
// OBTENER TODOS LOS DISPOSITIVOS
// =========================
function obtenerTodosLosDispositivos(PDO $pdo): array
{
    $sql = "SELECT d.*, t.type_name FROM device d LEFT JOIN device_type t ON d.type_id = t.type_id ORDER BY d.device_id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// =========================
// OBTENER DISPOSITIVO POR ID
// =========================
function obtenerDispositivoPorId(PDO $pdo, int $id)
{
    $sql = "SELECT * FROM device WHERE device_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// =========================
// INSERTAR DISPOSITIVO
// =========================
function insertarDispositivo(PDO $pdo,int $id,string $nombre,?float $precio,?int $tipo_id,?string $imagen,string $marca): int|false {

    $sql = "INSERT INTO device(device_id,device_name,device_brand,device_price,device_image,type_id)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    try {

        $stmt->execute([$id,$nombre,$marca,$precio,$imagen,$tipo_id]);
        return $id;
    } catch (PDOException $e) {
        error_log("Error al insertar: " . $e->getMessage());
        echo "<pre>ERROR: " . $e->getMessage() . "</pre>";
        return false;
    }
}

// =========================
// ACTUALIZAR DISPOSITIVO
// =========================
function actualizarDispositivo(PDO $pdo,int $id,string $nombre,?float $precio,?int $tipo_id,?string $imagen,string $marca): bool {

    // SI NO SE ENVÍA NUEVA IMAGEN
    // CONSERVAR LA ANTERIOR
    if ($imagen === null) {

        $sql = "UPDATE device SET device_name = ?, device_brand = ?, device_price = ?,type_id = ? WHERE device_id = ?";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$nombre,$marca,$precio,$tipo_id,$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al actualizar: " . $e->getMessage());
            echo "<pre>ERROR: " . $e->getMessage() . "</pre>";
            return false;
        }

    } else {

        // SI HAY NUEVA IMAGEN O SE ELIMINA
        $sql = "UPDATE device SET device_name = ?,device_brand = ?,device_price = ?,device_image = ?,type_id = ? WHERE device_id = ?";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$nombre,$marca,$precio,$imagen,$tipo_id,$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al actualizar: " . $e->getMessage());
            echo "<pre>ERROR: " . $e->getMessage() . "</pre>";
            return false;
        }
    }
}

// =========================
// ELIMINAR DISPOSITIVO
// =========================
function eliminarDispositivo(PDO $pdo, int $id): bool
{
    $sql = "DELETE FROM device WHERE device_id = ?";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error al eliminar: " . $e->getMessage());
        return false;
    }
}

// =========================
// OBTENER TODOS LOS TIPOS
// =========================
function obtenerTodosLosTipos(PDO $pdo): array
{
    $sql = "SELECT * FROM device_type ORDER BY type_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// =========================
// CREAR TIPO
// =========================
function crearTipo(PDO $pdo, string $nombre): int|false
{
    $sql = "INSERT INTO device_type (type_name)VALUES (?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$nombre]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al crear tipo: " . $e->getMessage());
        return false;
    }
}

// =========================
// ACTUALIZAR TIPO
// =========================
function actualizarTipo(PDO $pdo, int $id, string $nombre): bool
{
    $sql = "UPDATE device_type SET type_name = ? WHERE type_id = ?";
    $stmt = $pdo->prepare($sql);
    try {

        $stmt->execute([$nombre,$id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error al actualizar tipo: " . $e->getMessage());
        return false;
    }
}

// =========================
// ELIMINAR TIPO
// =========================
function eliminarTipo(PDO $pdo, int $id_tipo): bool
{
    $sql = "DELETE FROM device_type WHERE type_id = ?";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$id_tipo]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error al eliminar tipo: " . $e->getMessage());
        return false;
    }
}

// =========================
// OBTENER TODAS LAS MARCAS
// =========================
function obtenerTodasLasMarcas(PDO $pdo): array
{
    $sql = "SELECT * FROM device_brand ORDER BY brand_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// =========================
// CREAR TIPO
// =========================
function crearMarcas(PDO $pdo, string $nombrem): int|false
{
    $sql = "INSERT INTO device_brand (brand_name)VALUES (?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$nombrem]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al crear tipo: " . $e->getMessage());
        return false;
    }
}

// =========================
// ACTUALIZAR TIPO
// =========================
function actualizarMarca(PDO $pdo, int $idm, string $nombrem): bool
{
    $sql = "UPDATE device_brand SET brand_name = ? WHERE brand_id = ?";
    $stmt = $pdo->prepare($sql);
    try {

        $stmt->execute([$nombrem,$idm]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error al actualizar tipo: " . $e->getMessage());
        return false;
    }
}

// =========================
// ELIMINAR TIPO
// =========================
function eliminarMarca(PDO $pdo, int $idm): bool
{
    $sql = "DELETE FROM device_brand WHERE brand_id = ?";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$idm]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error al eliminar tipo: " . $e->getMessage());
        return false;
    }
}

?>