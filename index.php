<?php
// Configuración de la base de datos
$host = "localhost";
$dbname = "registro_productos";
$user = "root";
$password = "";

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener datos para los selectores
    $stmt = $pdo->prepare("SELECT id, nombre FROM monedas");
    $stmt->execute();
    $monedas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, nombre FROM bodegas");
    $stmt->execute();
    $bodegas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Manejo de errores de base de datos
    echo json_encode(["exito" => false, "mensaje" => "Error de conexión a la base de datos: " . $e->getMessage()]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Productos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form id="productoForm">
        <h2>Formulario de Producto</h2>

        <!-- Sección: Identificación del producto -->
        <div class="form-row">
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo">
            </div>
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre">
            </div>
        </div>

        <!-- Sección: Ubicación del producto -->
        <div class="form-row">
            <div class="form-group">
                <label for="bodegaSelect">Bodega</label>
                <select id="bodegaSelect" name="bodega">
                    <option value=""></option>
                    <?php foreach ($bodegas as $bodega): ?>
                        <option value="<?php echo $bodega['nombre']; ?>" data-id="<?php echo $bodega['id']; ?>"><?php echo $bodega['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sucursalSelect">Sucursal</label>
                <select id="sucursalSelect" name="sucursal">
                    <option value=""></option>
                </select>
            </div>
        </div>

        <!-- Sección: Información de precio -->
        <div class="form-row">
            <div class="form-group">
                <label for="moneda">Moneda</label>
                <select id="moneda" name="moneda">
                    <option value=""></option>
                    <?php foreach ($monedas as $moneda): ?>
                        <option value="<?php echo $moneda['nombre']; ?>"><?php echo $moneda['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="precio">Precio</label>
                <input type="text" id="precio" name="precio">
            </div>
        </div>

        <!-- Sección: Materiales del producto -->
        <div class="form-group">
            <label>Material del Producto</label>
            <div class="materiales-container">
                <div class="material-item">
                    <input type="checkbox" id="plastico" name="material[]" value="Plástico">
                    <label for="plastico">Plástico</label>
                </div>
                <div class="material-item">
                    <input type="checkbox" id="metal" name="material[]" value="Metal">
                    <label for="metal">Metal</label>
                </div>
                <div class="material-item">
                    <input type="checkbox" id="madera" name="material[]" value="Madera">
                    <label for="madera">Madera</label>
                </div>
                <div class="material-item">
                    <input type="checkbox" id="vidrio" name="material[]" value="Vidrio">
                    <label for="vidrio">Vidrio</label>
                </div>
                <div class="material-item">
                    <input type="checkbox" id="textil" name="material[]" value="Textil">
                    <label for="textil">Textil</label>
                </div>
            </div>
        </div>

        <!-- Sección: Descripción detallada -->
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"></textarea>
        </div>

        <!-- Sección: Botón de envío -->
        <div class="button-container">
            <button type="submit" id="guardarProducto">Guardar Producto</button>
        </div>
    </form>

    <script src="validaciones.js"></script>
</body>
</html>
