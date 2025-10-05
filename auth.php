<?php
require 'conexion.php';
session_start();

$mensaje_login = "";
$mensaje_registro = "";

// LOGIN
if(isset($_POST['login'])) {
    $usuario = $_POST['usuario_login'];
    $password = $_POST['password_login'];

    $stmt = $conn->prepare("SELECT id, usuario, password FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        if (password_verify($password, $fila['password'])) {
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['usuario_id'] = $fila['id'];
            header("Location: index.php");
            exit();
        } else {
            $mensaje_login = "❌ Contraseña incorrecta.";
        }
    } else {
        $mensaje_login = "❌ Usuario no encontrado.";
    }
}

// REGISTRO
if(isset($_POST['registro'])) {
    $usuario = $_POST['usuario_reg'];
    $email = $_POST['email_reg'];
    $password = password_hash($_POST['password_reg'], PASSWORD_BCRYPT);
    $edad = $_POST['edad_reg'];
    $peso = $_POST['peso_reg'];
    $altura = $_POST['altura_reg'];

    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, email, password, edad, peso, altura) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiii", $usuario, $email, $password, $edad, $peso, $altura);

    if ($stmt->execute()) {
        $mensaje_registro = "✅ Registro exitoso. <a href='#login' onclick='showTab(\"login\")'>Inicia sesión aquí</a>";
    } else {
        $mensaje_registro = "❌ Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login / Registro</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #000000, #0d1b2a);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    overflow: hidden;
}

.auth-container {
    background: rgba(20, 20, 20, 0.95);
    padding: 40px 35px;
    border-radius: 16px;
    box-shadow: 0 0 30px rgba(0, 180, 216, 0.5);
    width: 380px;
    animation: zoomIn 0.4s forwards;
}

@keyframes zoomIn { to { transform: scale(1); } }

h1 {
    text-align: center;
    margin-bottom: 25px;
    color: #00b4d8;
    letter-spacing: 1px;
}

.tabs {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.tab-button {
    flex: 1;
    padding: 10px;
    cursor: pointer;
    background: #111;
    border: none;
    color: #fff;
    font-weight: bold;
    transition: 0.3s;
    border-radius: 8px 8px 0 0;
}

.tab-button.active {
    background: linear-gradient(90deg, #00b4d8, #0091c2);
    color: #000;
}

form {
    display: none;
    flex-direction: column;
}

form.active {
    display: flex;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #555;
    background-color: #222;
    color: #fff;
    transition: all 0.3s;
}

input:focus {
    outline: none;
    border-color: #00b4d8;
    box-shadow: 0 0 8px #00b4d8;
}

button.submit-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #00b4d8, #0091c2);
    color: #000;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
}

button.submit-btn:hover {
    background: linear-gradient(90deg, #0091c2, #00b4d8);
    transform: scale(1.03);
}

.mensaje {
    margin-bottom: 12px;
    text-align: center;
    font-weight: bold;
}

a { color: #00b4d8; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="auth-container">
    <h1>Bienvenido</h1>
    <div class="tabs">
        <button class="tab-button active" onclick="showTab('login')">Login</button>
        <button class="tab-button" onclick="showTab('registro')">Registro</button>
    </div>

    <form id="login" class="active" method="POST">
        <?php if(!empty($mensaje_login)) echo "<div class='mensaje'>$mensaje_login</div>"; ?>
        <label>Usuario:</label>
        <input type="text" name="usuario_login" required>
        <label>Contraseña:</label>
        <input type="password" name="password_login" required>
        <button type="submit" name="login" class="submit-btn">Iniciar sesión</button>
    </form>

    <form id="registro" method="POST">
        <?php if(!empty($mensaje_registro)) echo "<div class='mensaje'>$mensaje_registro</div>"; ?>
        <label>Usuario:</label>
        <input type="text" name="usuario_reg" required>
        <label>Email:</label>
        <input type="email" name="email_reg" required>
        <label>Contraseña:</label>
        <input type="password" name="password_reg" required>
        <label>Edad:</label>
        <input type="number" name="edad_reg" required>
        <label>Peso (kg):</label>
        <input type="number" name="peso_reg" required>
        <label>Altura (cm):</label>
        <input type="number" name="altura_reg" required>
        <button type="submit" name="registro" class="submit-btn">Registrarse</button>
    </form>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('form').forEach(f => f.classList.remove('active'));

    document.querySelector('.tab-button[onclick*="'+tab+'"]').classList.add('active');
    document.getElementById(tab).classList.add('active');
}
</script>
</body>
</html>