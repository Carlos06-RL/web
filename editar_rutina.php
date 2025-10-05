<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if(!isset($_GET['rutina_id'])){
    header("Location: index.php");
    exit;
}

$rutina_id = (int)$_GET['rutina_id'];

// ===== OBTENER DATOS DE LA RUTINA =====
$stmt = $conn->prepare("SELECT nombre, descripcion FROM rutinas WHERE id=? AND usuario_id=?");
$stmt->bind_param("ii", $rutina_id, $usuario_id);
$stmt->execute();
$rutina = $stmt->get_result()->fetch_assoc();
if(!$rutina){
    header("Location: index.php");
    exit;
}

// ===== GUARDAR CAMBIOS =====
if(isset($_POST['guardar_rutina'])){
    $nombre = $_POST['nombreRutina'] ?? '';
    $descripcion = $_POST['descripcionRutina'] ?? '';

    if($nombre){
        // Verificar si ya existe una rutina con el mismo nombre (pero diferente ID)
        $stmt = $conn->prepare("SELECT id FROM rutinas WHERE usuario_id=? AND nombre=? AND id<>?");
        $stmt->bind_param("isi", $usuario_id, $nombre, $rutina_id);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();

        if($existe){
            // ⚠️ Mostrar el mensaje de error en pantalla (no con alert)
            $error = "⚠️ Ya existe otra rutina con ese nombre.";
        } else {
            // Actualizar rutina
            $stmt = $conn->prepare("UPDATE rutinas SET nombre=?, descripcion=? WHERE id=? AND usuario_id=?");
            $stmt->bind_param("ssii", $nombre, $descripcion, $rutina_id, $usuario_id);
            $stmt->execute();

            // Borrar ejercicios anteriores
            $stmt = $conn->prepare("DELETE FROM rutina_ejercicios WHERE rutina_id=?");
            $stmt->bind_param("i", $rutina_id);
            $stmt->execute();

            // Insertar los nuevos ejercicios seleccionados
            if(isset($_POST['ejercicio_id'])){
                foreach($_POST['ejercicio_id'] as $idx => $ejercicio_id){
                    $series = $_POST['series'][$idx] ?? 3;
                    $reps = $_POST['repeticiones'][$idx] ?? 10;
                    $stmt2 = $conn->prepare("INSERT INTO rutina_ejercicios (rutina_id, ejercicio_id, series, repeticiones) VALUES (?,?,?,?)");
                    $stmt2->bind_param("iiii",$rutina_id,$ejercicio_id,$series,$reps);
                    $stmt2->execute();
                }
            }

            // ✅ Mensaje de éxito tipo alert + redirección
            echo "<script>
                alert('✅ Rutina editada con éxito');
                window.location='index.php';
            </script>";
            exit;
        }
    }
}

// ===== OBTENER TODOS LOS EJERCICIOS =====
$ejercicios = $conn->query("SELECT id, nombre, dificultad FROM ejercicios ORDER BY nombre");

// ===== OBTENER EJERCICIOS YA SELECCIONADOS EN LA RUTINA =====
$stmt = $conn->prepare("SELECT ejercicio_id, series, repeticiones FROM rutina_ejercicios WHERE rutina_id=?");
$stmt->bind_param("i",$rutina_id);
$stmt->execute();
$result = $stmt->get_result();
$ejercicios_rutina = [];
while($row = $result->fetch_assoc()){
    $ejercicios_rutina[$row['ejercicio_id']] = ['series'=>$row['series'], 'repeticiones'=>$row['repeticiones']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Rutina</title>
<link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #0a0a0a, #0d1b2a);
    color: #fff;
    text-align: center;
    min-height: 100vh;
    padding: 20px;
}

/* Contenedor */
.container {
    max-width: 950px;
    margin: 0 auto;
    padding: 20px;
}

/* Títulos */
h2 {
    color: #00b4d8;
    margin-bottom: 20px;
    font-weight: 700;
    text-shadow: 0 0 8px rgba(0,180,216,0.7);
}

h3 {
    margin-top: 20px;
    margin-bottom: 10px;
    color: #90e0ef;
    font-weight: 600;
}

/* Caja del formulario */
form {
    background: rgba(20,20,20,0.9);
    padding: 28px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,180,216,0.4);
}

/* Inputs */
input[type=text], textarea, input[type=number] {
    width: 100%;
    padding: 10px;
    margin-bottom: 14px;
    border-radius: 8px;
    border: 1px solid #444;
    background-color: #111;
    color: #fff;
    transition: all 0.3s;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #00b4d8;
    box-shadow: 0 0 8px #00b4d8;
}

/* Botón */
button {
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #00b4d8, #0091c2);
    color: #000;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 15px;
}

button:hover {
    background: linear-gradient(90deg, #0091c2, #00b4d8);
    transform: scale(1.05);
}

/* Ejercicios */
.ejercicios-container {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    margin-top: 15px;
}

.ejercicio-item {
    flex: 1 1 280px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #111;
    padding: 14px;
    border-radius: 10px;
    border: 1px solid #333;
    transition: all 0.3s;
}

.ejercicio-item:hover {
    background: #1a1a1a;
}

.ejercicio-item label {
    flex: 1;
    text-align: left;
    font-weight: 500;
}

.ejercicio-item input[type=number] {
    width: 65px;
    margin-left: 6px;
    margin-right: 4px;
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

/* Responsive */
@media (max-width: 650px){
    .ejercicio-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .ejercicio-item input[type=number] {
        margin-left:0;
        margin-top:6px;
    }
}
</style>
</head>
<body>
<div class="container">
    <h2>✏️ Editar Rutina</h2>

    <?php if(isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Nombre de la rutina:</label>
        <input type="text" name="nombreRutina" value="<?php echo htmlspecialchars($rutina['nombre']); ?>" required>

        <label>Descripción:</label>
        <textarea name="descripcionRutina" rows="3"><?php echo htmlspecialchars($rutina['descripcion']); ?></textarea>

        <h3>🏋️ Selecciona los ejercicios</h3>
        <div class="ejercicios-container">
        <?php while($row=$ejercicios->fetch_assoc()): 
            $checked = isset($ejercicios_rutina[$row['id']]) ? 'checked' : '';
            $series = $ejercicios_rutina[$row['id']]['series'] ?? 3;
            $reps = $ejercicios_rutina[$row['id']]['repeticiones'] ?? 10;
        ?>
        <div class="ejercicio-item">
            <label>
                <input type="checkbox" name="ejercicio_id[]" value="<?php echo $row['id']; ?>" <?php echo $checked; ?>>
                <?php echo htmlspecialchars($row['nombre']); ?> (<?php echo $row['dificultad']; ?>)
            </label>
            <div>
                <input type="number" name="series[]" value="<?php echo $series; ?>" min="1"> S
                <input type="number" name="repeticiones[]" value="<?php echo $reps; ?>" min="1"> R
            </div>
        </div>
        <?php endwhile; ?>
        </div>

        <button type="submit" name="guardar_rutina">💾 Guardar Cambios</button>
    </form>
</div>
</body>
</html>