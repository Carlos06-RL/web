<?php
include("conexion.php"); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ejercicios para Pecho</title>
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/4.0.0/model-viewer.min.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #121212;
      color: #f1f1f1;
      margin: 0;
      padding: 0;
    }

    header {
      background: linear-gradient(90deg, #1CAAD9, #00C896);
      padding: 1.5em;
      text-align: center;
      box-shadow: 0px 2px 10px rgba(0,0,0,0.5);
    }

    header h1 {
      margin: 0;
      font-size: 2em;
      color: white;
    }

    .embed-3d {
      width: 100%;
      height: 70vh;
      background: #181818;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .embed-3d iframe {
      width: 90%; 
      height: 90%; 
      border: none;
      border-radius: 12px;
      box-shadow: 0px 6px 20px rgba(0,0,0,0.7);
    }

    .container {
      max-width: 1000px;
      margin: 2em auto;
      padding: 1em;
    }

    .exercise {
      background: #1e1e1e;
      padding: 1.5em;
      margin-bottom: 2em;
      border-radius: 12px;
      box-shadow: 0px 4px 15px rgba(0,0,0,0.6);
    }

    .exercise h2 {
      color: #1CAAD9;
      margin-bottom: 0.5em;
    }

    .exercise iframe {
      width: 100%;
      height: 300px;
      border-radius: 8px;
      margin-bottom: 1em;
    }

    .exercise p {
      text-align: left;
      max-width: 700px;
      margin: 0 auto;
    }

    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 2em;
    }

    .pagination a {
      color: #1CAAD9;
      padding: 8px 12px;
      margin: 0 4px;
      text-decoration: none;
      border: 1px solid #1CAAD9;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .pagination a.active {
      background: #1CAAD9;
      color: white;
    }

    .pagination a:hover {
      background: #00C896;
      color: white;
    }

    a.volver {
      display: inline-block;
      margin: 2em auto;
      padding: 0.8em 1.5em;
      background: #1CAAD9;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: 0.3s ease;
    }

    a.volver:hover {
      background: #00C896;
      transform: scale(1.05);
    }
  </style>
</head>
<body>
  <header>
    <h1>Ejercicios para Pecho</h1>
  </header>

  <!-- Modelo 3D -->
  <div class="embed-3d">
    <iframe 
      title="Pectoralis Major" 
      allowfullscreen 
      mozallowfullscreen="true" 
      webkitallowfullscreen="true" 
      allow="autoplay; fullscreen; xr-spatial-tracking"
      src="https://sketchfab.com/models/65e1a062661f4af89bad2822cd3dbb08/embed">
    </iframe>
  </div>

  <div class="container">
    <h2 style="text-align:center; margin-bottom:1em;">Aprende cómo entrenar pecho con estos ejercicios</h2>

    <?php
    // musculo_id = 2 → pecho
    $result = $conn->query("SELECT nombre, descripcion, video_url FROM ejercicios WHERE musculo_id = 2");

    $i = 1;
    while($row = $result->fetch_assoc()) {
        echo "<div class='exercise'>";
        echo "<h2>$i. " . htmlspecialchars($row['nombre']) . "</h2>";
        echo "<iframe src='" . htmlspecialchars($row['video_url']) . "' allowfullscreen></iframe>";
        echo "<p>" . nl2br(htmlspecialchars($row['descripcion'])) . "</p>";
        echo "</div>";
        $i++;
    }
    ?>

    <!-- Paginación -->
    <div class="pagination">
      <a href="#">&laquo;</a>
      <a href="pecho.php" class="active">1</a>
      <a href="pecho2.php">2</a>
      <a href="pecho3.php">3</a>
      <a href="#">&raquo;</a>
    </div>

    <!-- Botón volver -->
    <div style="text-align:center;">
      <a href="index.php" class="volver">← Volver a la página principal</a>
    </div>
  </div>
</body>
</html>
