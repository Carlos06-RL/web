<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// ===== ELIMINAR PROGRESO =====
if(isset($_GET['eliminar'])){
    $id = (int)$_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM progreso WHERE id=? AND usuario_id=?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();

    echo "<script>
        alert('🗑️ Registro de progreso eliminado correctamente');
        window.location='progreso.php';
    </script>";
    exit;
}

// ===== INSERTAR NUEVO PROGRESO =====
if(isset($_POST['guardar_progreso'])){
    $peso = $_POST['peso'] ?? null;
    $grasa = $_POST['grasa'] ?? null;
    $musculo = $_POST['musculo'] ?? null;
    $imc = $_POST['imc'] ?? null;
    $observaciones = $_POST['observaciones'] ?? '';

    $stmt = $conn->prepare("INSERT INTO progreso (usuario_id, peso, grasa, musculo, imc, observaciones) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("idddds", $usuario_id, $peso, $grasa, $musculo, $imc, $observaciones);
    $stmt->execute();

    echo "<script>
        alert('✅ Progreso guardado correctamente');
        window.location='progreso.php';
    </script>";
    exit;
}

// ===== CONSULTAR PROGRESOS =====
$stmt = $conn->prepare("SELECT * FROM progreso WHERE usuario_id=? ORDER BY fecha DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Progreso</title>
<style>
body {
    background: #0d1b2a;
    color: #f1f1f1;
    font-family: 'Segoe UI', Arial, sans-serif;
    text-align: center;
    margin: 0;
    padding: 20px;
}

h2 {
    color: #00b4d8;
}

form {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    margin: 20px auto;
}

input, textarea {
    width: 100%;
    padding: 8px;
    margin: 8px 0;
    border-radius: 6px;
    border: 1px solid #333;
    background: #111;
    color: #fff;
}

button {
    background: linear-gradient(90deg,#00b4d8,#0096c7);
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    color: #000;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    transform: scale(1.05);
}

table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border: 1px solid #444;
}

th {
    background: #0096c7;
    color: #000;
}

tr:nth-child(even) {
    background: #1e1e1e;
}

.btn-eliminar {
    background: #e63946;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-weight: bold;
}

.btn-eliminar:hover {
    background: #c62828;
}

.btn-volver {
    position: fixed;
    top: 20px;
    left: 20px;
    background: linear-gradient(90deg, #00b4d8, #0096c7);
    color: #000;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.btn-volver:hover {
    background: linear-gradient(90deg, #0096c7, #00b4d8);
    transform: scale(1.05);
}
</style>
</head>
<body>

<!-- Botón para volver al inicio -->
<a href="index.php"><button class="btn-volver">🏠 Volver al inicio</button></a>

<h2>📊 Registro de Progreso</h2>

<form method="post">
    <label>Peso (kg):</label>
    <input type="number" name="peso" step="0.1" required>

    <label>Grasa corporal (%):</label>
    <input type="number" name="grasa" step="0.1">

    <label>Masa muscular (%):</label>
    <input type="number" name="musculo" step="0.1">

    <label>IMC (opcional):</label>
    <input type="number" name="imc" step="0.1">

    <label>Observaciones:</label>
    <textarea name="observaciones" rows="2" placeholder="Ej: Semana intensa, dieta baja en calorías..."></textarea>

    <button type="submit" name="guardar_progreso">💾 Guardar Progreso</button>
</form>

<h3>Historial</h3>
<table>
<tr>
    <th>Fecha</th>
    <th>Peso</th>
    <th>Grasa (%)</th>
    <th>Músculo (%)</th>
    <th>IMC</th>
    <th>Notas</th>
    <th>Acciones</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
    <td><?php echo htmlspecialchars($row['peso']); ?></td>
    <td><?php echo htmlspecialchars($row['grasa']); ?></td>
    <td><?php echo htmlspecialchars($row['musculo']); ?></td>
    <td><?php echo htmlspecialchars($row['imc']); ?></td>
    <td><?php echo htmlspecialchars($row['observaciones']); ?></td>
    <td>
        <form method="get" onsubmit="return confirm('¿Seguro que deseas eliminar este registro?');" style="display:inline;">
            <input type="hidden" name="eliminar" value="<?php echo $row['id']; ?>">
            <button type="submit" class="btn-eliminar">🗑️</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>