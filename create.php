<?php
// create.php
require_once 'includes/session_check.php';
require_once 'includes/db.php';

// Obtenemos todos los usuarios para el multi-select de colaboradores
$allUsersStmt = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
$allUsers = $allUsersStmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'];
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date    = $_POST['due_date'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    $is_recurrent       = isset($_POST['is_recurrent']) ? 1 : 0;
    $recurrence_type    = $_POST['recurrence_type'] ?? null;
    $recurrence_interval = $_POST['recurrence_interval'] ?? 1;

    // Insertar recordatorio
    $stmt = $pdo->prepare("
      INSERT INTO reminders
      (user_id, category_id, title, description, due_date,
       is_recurrent, recurrence_type, recurrence_interval)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
      $user_id,
      $category_id,
      $title,
      $description,
      $due_date,
      $is_recurrent,
      $recurrence_type,
      $recurrence_interval
    ]);
    $reminder_id = $pdo->lastInsertId();

    // Insertar colaboradores
    if (!empty($_POST['collaborators'])) {
        foreach ($_POST['collaborators'] as $colab_user_id) {
            // Evitar que el creador se duplique como colaborador
            if ($colab_user_id == $user_id) continue;
            $ins = $pdo->prepare("INSERT INTO reminder_collaborators (reminder_id, user_id) VALUES (?, ?)");
            $ins->execute([$reminder_id, $colab_user_id]);
        }
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Crear Recordatorio</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
  <h1>Crear Recordatorio</h1>
  <form method="POST">
    <label>Título:</label>
    <input type="text" name="title" required>

    <label>Categoría:</label>
    <select name="category_id">
      <option value="">Sin categoría</option>
      <?php foreach($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>">
          <?php echo htmlspecialchars($cat['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Descripción:</label>
    <textarea name="description"></textarea>

    <label>Fecha límite:</label>
    <input type="date" name="due_date">

    <label>Colaboradores (Ctrl+Click para múltiple):</label>
    <select name="collaborators[]" multiple>
      <?php foreach($allUsers as $u): ?>
        <?php if ($u['id'] == $user_id) continue; ?>
        <option value="<?php echo $u['id']; ?>">
          <?php echo htmlspecialchars($u['username']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <fieldset style="margin-top:10px;">
      <legend>¿Recurrente?</legend>
      <input type="checkbox" id="recurr" name="is_recurrent" value="1" />
      <label for="recurr">Sí, repetir</label>

      <div style="margin-top:6px;">
        <label>Tipo:</label>
        <select name="recurrence_type">
          <option value="daily">Diaria</option>
          <option value="weekly">Semanal</option>
          <option value="monthly">Mensual</option>
        </select>
        <label>Cada</label>
        <input type="number" name="recurrence_interval" value="1" style="width:60px;" />
        <span>(días/semanas/meses)</span>
      </div>
    </fieldset>

    <button type="submit">Guardar</button>
    <a href="index.php">Volver</a>
  </form>
</div>
</body>
</html>
