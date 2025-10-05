<?php 
session_start();
include("conexion.php"); // Conexión a musculosdb

// ===== ELIMINAR RUTINA =====
if(isset($_GET['eliminar']) && isset($_SESSION['usuario_id'])) {
    $rutina_id = (int)$_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM rutinas WHERE id=? AND usuario_id=?");
    $stmt->bind_param("ii", $rutina_id, $_SESSION['usuario_id']);
    $stmt->execute();
    header("Location: index.php"); // recarga la página para actualizar la lista
    exit;
}
?>
<?php if(isset($_GET['msg'])): ?>
    <div class="alert 
        <?php echo ($_GET['msg']=="rutina_creada" || $_GET['msg']=="rutina_editada" || $_GET['msg']=="rutina_eliminada") ? 'success' : 'error'; ?>">
        <?php 
            switch($_GET['msg']){
                case "rutina_creada": echo "✅ Rutina creada correctamente."; break;
                case "rutina_editada": echo "✏️ Rutina editada correctamente."; break;
                case "rutina_eliminada": echo "🗑️ Rutina eliminada correctamente."; break;
                case "error": echo "⚠️ Ha ocurrido un error."; break;
            }
        ?>
    </div>
<?php endif; ?>



<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salud y Músculos 3D</title>
<link rel="stylesheet" href="css/style.css">
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/4.0.0/model-viewer.min.js"></script>
<style>
/* ===== Reset básico ===== */
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #000000, #0d1b2a); color: #fff; text-align:center; }

/* ===== Usuario ===== */
.usuario-box {
    position: fixed; top: 16px; right: 24px; font-size: 18px; z-index: 1000;
    background: rgba(20,20,20,0.85); padding:8px 12px; border-radius:8px;
}
.usuario-box span, .usuario-box a { font-size: 18px; font-weight:bold; color:#00b4d8; text-decoration:none; margin-left:12px; }
.usuario-box a:hover { text-decoration:underline; }

/* ===== Menú desplegable ===== */
.menu-desplegable { background: rgba(20,20,20,0.85); padding:20px; border-radius:12px; margin:16px auto; max-width:360px; box-shadow:0 0 20px rgba(0,180,216,0.4); position:absolute; top:24px; left:24px; z-index:10; }
.menu-desplegable label { display:block; margin:12px 0 4px; font-weight:bold; }
.menu-desplegable select { font-size:1.2em; padding:6px 12px; border-radius:6px; border:1px solid #aaa; background:#222; color:#fff; }

/* ===== Calculadora ===== */
.calculadora { background: rgba(20,20,20,0.85); padding:20px; border-radius:12px; margin:16px auto; max-width:360px; box-shadow:0 0 20px rgba(0,180,216,0.4); position:absolute; top:24px; right:24px; z-index:10; }
.calculadora label { display:block; margin:12px 0 4px; font-weight:bold; }
.calculadora input, .calculadora select { width:100%; padding:10px; border-radius:8px; border:1px solid #555; background:#222; color:#fff; margin-bottom:12px; transition:all 0.3s; }
.calculadora input:focus, .calculadora select:focus { outline:none; border-color:#00b4d8; box-shadow:0 0 8px #00b4d8; }
.calculadora button { padding:12px; width:100%; border-radius:10px; border:none; font-weight:bold; cursor:pointer; background:linear-gradient(90deg,#00b4d8,#0091c2); color:#000; margin-top:10px; transition:0.3s; }
.calculadora button:hover { background:linear-gradient(90deg,#0091c2,#00b4d8); transform:scale(1.03); }
.calculadora .resultado { margin-top:12px; font-weight:bold; text-align:center; color:#00b4d8; }
.calculadora hr { margin:18px 0; border:none; border-top:1px solid #444; }

/* ===== Títulos y modelo 3D ===== */
h1.fade-in { text-align:center; margin-top:40px; color:#00b4d8; }
model-viewer { width:100%; max-width:800px; height:500px; display:block; margin:24px auto; border-radius:12px; box-shadow:0 0 30px rgba(0,180,216,0.5); }

/* ===== Compartir ===== */
#compartir { margin:64px auto 24px auto; max-width:600px; }
#compartir button { padding:10px 18px; margin-top:12px; border-radius:8px; border:none; background:#00b4d8; color:#000; font-weight:bold; cursor:pointer; transition:0.3s; }
#compartir button:hover { background:#0091c2; }

/* ===== Botón Rutinas ===== */
#btnRutinas {
    position: fixed; bottom: 24px; left: 24px;
    background:#1CAAD9; color:#000; border:none; border-radius:10px;
    padding:12px 20px; font-weight:bold; cursor:pointer; z-index:1000; transition:0.3s;
}
#btnRutinas:hover { background:#1488b3; transform:scale(1.05); }

/* ===== Modal Rutinas ===== */
#modalRutinas { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:2000; }
.modal-content { background:#222; padding:20px; border-radius:12px; width:90%; max-width:600px; color:#fff; max-height:90%; overflow-y:auto; position:relative; }
.modal-content h2 { margin-bottom:16px; color:#00b4d8; }
.modal-content label { display:block; margin:12px 0 4px; font-weight:bold; }
.modal-content input, .modal-content textarea { width:100%; padding:8px; border-radius:6px; border:1px solid #555; background:#111; color:#fff; margin-bottom:12px; }
.modal-content button { padding:10px; border-radius:8px; border:none; background:#00b4d8; color:#000; font-weight:bold; cursor:pointer; margin-top:6px; transition:0.3s; }
.modal-content button:hover { background:#0091c2; }
.modal-close { position:absolute; top:12px; right:12px; font-size:20px; cursor:pointer; }

/* ===== Rutina Card ===== */
#listaRutinas { display:flex; flex-direction:column; gap:12px; max-height:300px; overflow-y:auto; padding-right:6px; }
#listaRutinas::-webkit-scrollbar { width:6px; }
#listaRutinas::-webkit-scrollbar-thumb { background:#00b4d8; border-radius:3px; }
.rutina-card { background:#1a1a1a; padding:14px 16px; border-radius:12px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 15px rgba(0,180,216,0.3); transition: transform 0.3s, box-shadow 0.3s; }
.rutina-card:hover { transform:translateY(-4px); box-shadow:0 6px 20px rgba(0,180,216,0.5); }
.rutina-info h3 { margin-bottom:6px; color:#00b4d8; }
.rutina-info p { color:#ccc; margin-bottom:0; }
.rutina-actions { display:flex; gap:8px; }
.rutina-actions .delete { background:#e63946; color:#fff; padding:6px 12px; border:none; border-radius:8px; font-weight:bold; cursor:pointer; transition:0.3s; }
.rutina-actions .delete:hover { background:#c62828; transform: scale(1.05); }
.rutina-actions .edit { background:#2a9d8f; color:#fff; padding:6px 12px; border:none; border-radius:8px; font-weight:bold; cursor:pointer; transition:0.3s; }
.rutina-actions .edit:hover { background:#21867a; transform: scale(1.05); }

/* ===== Formulario Crear Rutina ===== */
.form-rutina { margin-top:12px; display:flex; flex-direction: column; gap:8px; }
.form-rutina input, .form-rutina textarea {
  padding:10px; border-radius:8px; border:1px solid #555; background:#111; color:#fff; transition: all 0.3s;
}
.form-rutina input:focus, .form-rutina textarea:focus { outline:none; border-color:#00b4d8; box-shadow:0 0 8px #00b4d8; }
.btn-crear { padding:12px; border:none; border-radius:10px; font-weight:bold; background:linear-gradient(90deg,#00b4d8,#0091c2); color:#000; cursor:pointer; transition:0.3s; }
.btn-crear:hover { background:linear-gradient(90deg,#0091c2,#00b4d8); transform:scale(1.05); }

/* ===== Animaciones ===== */
.fade-in { opacity:0; animation:fadeIn 1s forwards; }
@keyframes fadeIn { from{opacity:0; transform:translateY(-10px);} to{opacity:1; transform:translateY(0);} }

/* ===== Responsive ===== */
@media(max-width:800px){ .menu-desplegable,.calculadora{position:static;width:95vw;margin:12px auto;} h1{font-size:1.5em;} model-viewer{width:100vw!important;height:50vh!important;} }
</style>
</head>
<body>

<!-- Usuario -->
<div class="usuario-box">
<?php
if(isset($_SESSION['usuario'])) {
  echo "<span>Bienvenido, " . htmlspecialchars($_SESSION['usuario']) . "</span> 
        <a href='progreso.php'>📊 Mi progreso</a> | 
        <a href='logout.php'>Cerrar sesión</a>";
} else {
  echo "<a href='auth.php'>Iniciar sesión</a>";
}
?>
</div>

<!-- Menú desplegable -->
<div class="menu-desplegable fade-in">
<label for="musculo-select">Músculo del cuerpo:</label>
<select id="musculo-select">
  <option value="">Selecciona...</option>
  <option value="biceps.php">Bíceps</option>
  <option value="pecho.php">Pecho</option>
  <option value="abdomen.php">Abdomen</option>
</select>
</div>

<!-- Calculadoras -->
<?php
if (isset($_SESSION['usuario_id'])) {
    $id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT edad,peso,altura FROM usuarios WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $datos = $stmt->get_result()->fetch_assoc();
}
?>
<div class="calculadora fade-in">
<h3>Estimador de Grasa Corporal</h3>
<label for="sexo">Sexo:</label>
<select id="sexo"><option value="hombre">Hombre</option><option value="mujer">Mujer</option></select>
<label for="edad">Edad:</label><input type="number" id="edad" min="10" max="120" value="<?php echo $datos['edad']??''; ?>">
<label for="peso">Peso (kg):</label><input type="number" id="peso" min="30" max="200" value="<?php echo $datos['peso']??''; ?>">
<label for="altura">Altura (cm):</label><input type="number" id="altura" min="100" max="250" value="<?php echo $datos['altura']??''; ?>">
<button id="calcularGrasa">Calcular grasa corporal</button>
<div class="resultado" id="resultadoGrasa"></div>

<hr>

<h3>Calculadora de Riesgo Cardiometabólico</h3>
<label for="cintura">Cintura (cm):</label><input type="number" id="cintura" min="40" max="150">
<label for="presion">Presión sistólica (mmHg):</label><input type="number" id="presion" min="80" max="250">
<label for="glucosa">Glucosa (mg/dL):</label><input type="number" id="glucosa" min="50" max="300">
<button id="calcularRiesgo">Calcular riesgo cardiometabólico</button>
<div class="resultado" id="resultadoRiesgo"></div>
</div>

<!-- Modelo 3D -->
<h1 class="fade-in">Selecciona un músculo en 3D</h1>
<model-viewer id="modelo" class="fade-in" src="./models/cuerpo/scene.gltf" alt="Músculo 3D" camera-controls auto-rotate exposure="1" shadow-intensity="1" loading="eager" ar ar-modes="webxr scene-viewer quick-look">
<div slot="poster" style="color:white;font-size:1.5em;margin-top:2em;">Cargando modelo 3D...</div>
</model-viewer>

<!-- Compartir -->
<div id="compartir">
<h3>¡Comparte con tus amigos!</h3>
<button onclick="compartir('twitter')">Twitter</button>
<button onclick="compartir('facebook')">Facebook</button>
</div>

<!-- Botón Rutinas -->
<button id="btnRutinas">Rutinas</button>

<!-- Modal Rutinas -->
<div id="modalRutinas">
  <div class="modal-content">
    <span class="modal-close" onclick="cerrarRutinas()">&times;</span>
    <h2>Mis Rutinas</h2>

    <?php if(isset($_SESSION['usuario_id'])): ?>
    <div id="listaRutinas">
    <?php
      $stmt = $conn->prepare("SELECT * FROM rutinas WHERE usuario_id=? ORDER BY fecha_creacion DESC");
      $stmt->bind_param("i", $_SESSION['usuario_id']);
      $stmt->execute();
      $rutinas = $stmt->get_result();
      while($r = $rutinas->fetch_assoc()) {
          echo "<div class='rutina-card'>";
          echo "<div class='rutina-info'>";
          echo "<h3>".htmlspecialchars($r['nombre'])."</h3>";
          echo "<p>".nl2br(htmlspecialchars($r['descripcion']))."</p>";
          echo "</div>";
          echo "<div class='rutina-actions'>";
          echo "<form method='get' style='display:inline;'><input type='hidden' name='eliminar' value='".$r['id']."'><button class='delete'>Eliminar</button></form>";
          echo "<form action='editar_rutina.php' method='get' style='display:inline;'><input type='hidden' name='rutina_id' value='".$r['id']."'><button class='edit'>Editar</button></form>";
          echo "</div>";
          echo "</div>";
      }
    ?>
    </div>

    <hr>

    <h3>Crear nueva rutina</h3>
    <form method="get" action="crear_rutina.php">
    <label for="nombreRutina">Nombre:</label>
    <input type="text" name="nombreRutina" id="nombreRutina" required>

    <label for="descripcionRutina">Descripción:</label>
    <textarea name="descripcionRutina" id="descripcionRutina"></textarea>

    <button type="submit">Crear Rutina</button>
</form>

    <?php else: ?>
      <p>Debes <a href="auth.php">iniciar sesión</a> para gestionar rutinas.</p>
    <?php endif; ?>
  </div>
</div>

<script>
/* Modelo 3D */
let isInteracting = false;
const mv = document.querySelector('#modelo');
const select = document.getElementById('musculo-select');
mv.addEventListener('mousedown',()=>{isInteracting=true;});
mv.addEventListener('mouseup',()=>{isInteracting=false;});
mv.addEventListener('click',()=>{if(!isInteracting){if(select.value) window.location.href=select.value; else alert('Selecciona un músculo');}});
select.addEventListener('change',function(){if(this.value) window.location.href=this.value;});

/* Calculadoras */
document.getElementById('calcularGrasa').addEventListener('click',function(){
  const sexo=document.getElementById('sexo').value;
  const edad=parseFloat(document.getElementById('edad').value);
  const peso=parseFloat(document.getElementById('peso').value);
  const altura=parseFloat(document.getElementById('altura').value);
  let grasa=null;
  if(sexo==='hombre'&&peso&&altura&&edad) grasa=(1.20*(peso/((altura/100)**2)))+(0.23*edad)-16.2;
  else if(sexo==='mujer'&&peso&&altura&&edad) grasa=(1.20*(peso/((altura/100)**2)))+(0.23*edad)-5.4;
  const res=document.getElementById('resultadoGrasa');
  res.textContent=(grasa>0&&grasa<70)?`Tu estimación de grasa corporal es: ${grasa.toFixed(1)}%`:'Rellena todos los campos correctamente.';
});
document.getElementById('calcularRiesgo').addEventListener('click',function(){
  const cintura=parseFloat(document.getElementById('cintura').value);
  const presion=parseFloat(document.getElementById('presion').value);
  const glucosa=parseFloat(document.getElementById('glucosa').value);
  let riesgo=0; let mensaje="";
  if(cintura&&presion&&glucosa){
    if(cintura>102||presion>130||glucosa>100) riesgo++;
    if(cintura>110||presion>140||glucosa>126) riesgo++;
    mensaje= riesgo===0?"Riesgo bajo":riesgo===1?"Riesgo moderado":"Riesgo alto";
    document.getElementById('resultadoRiesgo').textContent=`Tu riesgo cardiometabólico estimado es: ${mensaje}`;
  }else document.getElementById('resultadoRiesgo').textContent='Rellena todos los campos correctamente.';
});

/* Compartir */
function compartir(red){
  const url=encodeURIComponent(window.location.href);
  const texto=encodeURIComponent("¡Mira esta página sobre salud y músculos 3D!");
  if(red==='twitter') window.open(`https://twitter.com/intent/tweet?url=${url}&text=${texto}`,'_blank');
  else if(red==='facebook') window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`,'_blank');
}

/* Rutinas */
const btnRutinas=document.getElementById('btnRutinas');
const modalRutinas=document.getElementById('modalRutinas');
btnRutinas.onclick=()=>{modalRutinas.style.display='flex';}
function cerrarRutinas(){modalRutinas.style.display='none';}
window.onclick=(e)=>{if(e.target==modalRutinas) cerrarRutinas();}
</script>

</body>
</html>