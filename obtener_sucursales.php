<?php
header('Content-Type: application/json');

// Validación del parámetro de entrada
if (!isset($_GET['bodega_id']) || empty($_GET['bodega_id'])) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No se proporcionó un ID de bodega válido',
        'sucursales' => []
    ]);
    exit;
}

// Configuración de la base de datos
$host = "localhost";
$dbname = "registro_productos";
$user = "root";
$password = "";

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $bodega_id = intval($_GET['bodega_id']);

    // Obtener sucursales de la bodega seleccionada
    $stmt = $pdo->prepare("SELECT nombre FROM sucursales WHERE bodega_id = ?");
    $stmt->execute([$bodega_id]);
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transformar el resultado para incluir el nombre como valor
    $sucursalesFormateadas = array_map(function($sucursal) {
        return [
            'id' => $sucursal['nombre'],
            'nombre' => $sucursal['nombre']
        ];
    }, $sucursales);

    // Enviar respuesta exitosa
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Sucursales obtenidas correctamente',
        'sucursales' => $sucursalesFormateadas
    ]);

} catch (PDOException $e) {
    // Manejo de errores de base de datos
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al conectar con la base de datos: ' . $e->getMessage(),
        'sucursales' => []
    ]);
}
?>
