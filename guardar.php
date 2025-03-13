<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = "localhost";
$dbname = "registro_productos";
$user = "root";
$password = "";

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificación de código único
    if (isset($_POST['verificar_codigo'])) {
        $codigo = $_POST['verificar_codigo'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE codigo = ?");
        $stmt->execute([$codigo]);
        $existe = $stmt->fetchColumn() > 0;
        
        echo json_encode([
            "disponible" => !$existe,
            "mensaje" => $existe ? "El código ya existe" : "Código disponible"
        ]);
        exit;
    }

    // Procesamiento del formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtención de datos del formulario
        $codigo = $_POST['codigo'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $bodega = $_POST['bodega'] ?? '';
        $sucursal = $_POST['sucursal'] ?? '';
        $moneda = $_POST['moneda'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $materiales = $_POST['material'] ?? [];
        $descripcion = $_POST['descripcion'] ?? '';

        // Validaciones del lado del servidor
        if (empty($codigo) || !preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]{5,15}$/', $codigo)) {
            throw new Exception("Código de producto inválido");
        }

        if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 50) {
            throw new Exception("Nombre de producto inválido");
        }

        if (empty($precio) || !preg_match('/^\d+(\.\d{1,2})?$/', $precio)) {
            throw new Exception("Precio inválido");
        }

        if (count($materiales) < 2) {
            throw new Exception("Debe seleccionar al menos dos materiales");
        }

        if (empty($descripcion) || strlen($descripcion) < 10 || strlen($descripcion) > 1000) {
            throw new Exception("Descripción inválida");
        }

        // Verificación final de código único
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE codigo = ?");
        $stmt->execute([$codigo]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("El código del producto ya está registrado");
        }

        // Inicio de transacción
        $pdo->beginTransaction();

        try {
            // Inserción del producto
            $stmt = $pdo->prepare("INSERT INTO productos (codigo, nombre, bodega, sucursal, moneda, precio, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$codigo, $nombre, $bodega, $sucursal, $moneda, $precio, $descripcion]);
            
            $producto_id = $pdo->lastInsertId();

            // Inserción de materiales
            $stmt = $pdo->prepare("INSERT INTO productos_materiales (producto_id, material) VALUES (?, ?)");
            foreach ($materiales as $material) {
                $stmt->execute([$producto_id, $material]);
            }

            // Confirmar transacción
            $pdo->commit();

            echo json_encode([
                "exito" => true,
                "mensaje" => "Producto registrado exitosamente"
            ]);

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            throw $e;
        }
    }

} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        "exito" => false,
        "mensaje" => "Error: " . $e->getMessage()
    ]);
}
?>
