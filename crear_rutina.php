<?php 
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Recibimos nombre y descripción desde GET
$nombre = $_GET['nombreRutina'] ?? '';
$descripcion = $_GET['descripcionRutina'] ?? '';

if(!$nombre){
    echo "<p style='color:red;'>⚠️ No se recibió el nombre de la rutina.</p>";
    exit;
}

// ===== GUARDAR RUTINA Y EJERCICIOS =====
if(isset($_POST['guardar_rutina'])){
    $nombre = trim($nombre);
    $descripcion = trim($descripcion);

    // Verificar duplicados
    $stmt = $conn->prepare("SELECT id FROM rutinas WHERE usuario_id=? AND nombre=?");
    $stmt->bind_param("is", $usuario_id, $nombre);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();

    if($existe){
        // Mostrar error directamente en la página
        $error = "⚠️ Ya existe otra rutina con ese nombre.";
    } elseif(!isset($_POST['ejercicio_id'])) {
        // Validar que haya al menos un ejercicio seleccionado
        $error = "⚠️ Debes seleccionar al menos un ejercicio para crear la rutina.";
    } else {
        // Insertar rutina
        $stmt = $conn->prepare("INSERT INTO rutinas (usuario_id, nombre, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $usuario_id, $nombre, $descripcion);
        $stmt->execute();
        $rutina_id = $stmt->insert_id;

        // Insertar ejercicios
        foreach($_POST['ejercicio_id'] as $idx => $ejercicio_id){
            $series = $_POST['series'][$idx] ?? 3;
            $reps = $_POST['repeticiones'][$idx] ?? 10;
            $stmt2 = $conn->prepare("INSERT INTO rutina_ejercicios (rutina_id, ejercicio_id, series, repeticiones) VALUES (?,?,?,?)");
            $stmt2->bind_param("iiii",$rutina_id,$ejercicio_id,$series,$reps);
            $stmt2->execute();
        }

        // Mostrar mensaje de éxito con alert y redirigir
        echo "<script>
            alert('✅ Rutina creada con éxito');
            window.location='index.php';
        </script>";
        exit;
    }
}

// ===== OBTENER EJERCICIOS =====
$ejercicios = $conn->query("SELECT id, nombre, dificultad FROM ejercicios ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Rutina</title>
<style>
body {
    background: linear-gradient(135deg, #000000, #0d1b2a);
    color: #f0f0f0;
    font-family: 'Segoe UI', Arial, sans-serif;
    text-align: center;
    margin: 0;
    padding: 0;
}
h2 {
    margin-top: 30px;
    font-size: 2rem;
    color: #00b4d8;
}
p em {
    color: #adb5bd;
    font-size: 1.1rem;
}

form {
    background: rgba(20,20,20,0.9);
    padding: 25px;
    border-radius: 16px;
    width: 80%;
    max-width: 700px;
    margin: 30px auto;
    box-shadow: 0 0 25px rgba(0, 180, 216, 0.4);
    text-align: left;
}

.ejercicio {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #1a1a1a;
    padding: 12px 15px;
    margin: 10px 0;
    border-radius: 10px;
    transition: 0.3s;
    border: 1px solid #222;
}
.ejercicio:hover {
    background: #222;
    transform: scale(1.02);
    border-color: #00b4d8;
}
.ejercicio label {
    font-size: 1rem;
    color: #f8f9fa;
}
.ejercicio input[type="number"] {
    width: 60px;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #444;
    background: #111;
    color: #fff;
    text-align: center;
    margin-left: 6px;
}

button {
    display: block;
    width: 100%;
    padding: 14px;
    margin-top: 20px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg,#00b4d8,#0096c7);
    color: #000;
    font-weight: bold;
    cursor: pointer;
    font-size: 1.1rem;
    transition: 0.3s;
}
button:hover {
    background: linear-gradient(90deg,#0096c7,#00b4d8);
    transform: scale(1.03);
    box-shadow: 0 0 12px rgba(0,180,216,0.6);
}

/* Mensaje de error */
.error {
    background-color: rgba(255, 0, 0, 0.2);
    border: 1px solid #ff4d4d;
    color: #ffb3b3;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: bold;
}
</style>
</head>
<body>

<h2>Agregar ejercicios a la rutina: <?php echo htmlspecialchars($nombre); ?></h2>
<p><em><?php echo htmlspecialchars($descripcion); ?></em></p>

<?php if(isset($error)): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <?php while($row=$ejercicios->fetch_assoc()): ?>
        <div class="ejercicio">
            <label>
                <input type="checkbox" name="ejercicio_id[]" value="<?php echo $row['id']; ?>">
                <?php echo htmlspecialchars($row['nombre']); ?> 
                <span style="color:#00b4d8;">(<?php echo $row['dificultad']; ?>)</span>
            </label>
            <div>
                <input type="number" name="series[]" value="3" min="1"> <span>Series</span>
                <input type="number" name="repeticiones[]" value="10" min="1"> <span>Reps</span>
            </div>
        </div>
    <?php endwhile; ?>
    <button type="submit" name="guardar_rutina">💾 Guardar Rutina</button>
</form>

</body>
</html>