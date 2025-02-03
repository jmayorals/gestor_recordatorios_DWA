<?php
// register.php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pass     = $_POST['password'] ?? '';

    if (!$username || !$email || !$pass) {
        $error = "Debes completar todos los campos.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "El usuario o email ya está en uso.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->execute([$username, $email, $hash]);
            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro - Gestor de Recordatorios</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <h1>Registro</h1>
    <?php if(!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Usuario:</label>
        <input type="text" name="username" />
        
        <label>Email:</label>
        <input type="email" name="email" />
        
        <label>Contraseña:</label>
        <input type="password" name="password" />
        
        <button type="submit">Registrarme</button>
        <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
    </form>
</div>
</body>
</html>
