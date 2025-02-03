<?php
// login.php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userOrEmail = trim($_POST['user'] ?? '');
    $pass        = $_POST['password'] ?? '';

    if (!$userOrEmail || !$pass) {
        $error = "Todos los campos son requeridos.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->execute([$userOrEmail, $userOrEmail]);
        $usr = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usr && password_verify($pass, $usr['password'])) {
            // Login válido
            $_SESSION['user_id'] = $usr['id'];
            $_SESSION['username'] = $usr['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuario/contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Gestor</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <h1>Iniciar Sesión</h1>
    <?php if(isset($_GET['registered'])): ?>
      <p style="color:green;">Registro exitoso. Ahora puedes iniciar sesión.</p>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Usuario o Email:</label>
        <input type="text" name="user" />

        <label>Contraseña:</label>
        <input type="password" name="password" />

        <button type="submit">Acceder</button>
        <a href="register.php">¿No tienes cuenta? Regístrate</a>
    </form>
</div>
</body>
</html>
