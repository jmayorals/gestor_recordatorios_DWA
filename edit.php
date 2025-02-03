<?php
// edit.php
require_once 'includes/session_check.php';
require_once 'includes/db.php';

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Obtener el recordatorio si el usuario es propietario o colaborador
$stmt = $pdo->prepare("
  SELECT * FROM reminders 
  WHERE id = ? 
    AND (user_id = ? OR id IN (SELECT reminder_id FROM reminder_collaborators WHERE user_id = ?))
");
$stmt->execute([$id, $user_id, $user_id]);
$reminder = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reminder) {
    header("Location: index.php");
    exit;
}

// Obtener colaboradores actuales
$collabStmt = $pdo->prepare("SELECT user_id FROM reminder_collaborators WHERE reminder_id=?");
$collabStmt->execute([$id]);
$currentCollabs = $collabStmt->fetchAll(PDO::FETCH_COLUMN);

// Usuarios para el multi-select
$allUsers = $pdo->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date    = $_POST['due_date'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    $is_recurrent        = isset($_POST['is_recurrent']) ? 1 : 0;
    $recurrence_type     = $_POST['recurrence_type'] ?? null;
    $recurrence_interval = $_POST['recurrence_interval'] ?? 1;

    $update = $pdo->prepare("
      UPDATE reminders
      SET category_id=?, title=?, description=?, due_date=?,
          is_recurrent=?, recurrence_type=?, recurrence_interval=?
      WHERE id=? AND user_id=?
    ");
    $update->execute([
      $category_id,
      $title,
      $description,
      $due_date,
      $is_recurrent,
      $recurrence_type,
      $recurrence_interval,
      $id,
      $user_id
    ]);

    // Actualizamos colaboradores
    // Primero, eliminar los existentes
    $del = $pdo->prepare("DELETE FROM reminder_collaborators WHERE reminder_id=?");
    $del->execute([$id]);

    // Insertar los nuevos
    if (!empty($_POST['collaborators'])) {
        foreach ($_POST['collaborators'] as $colab_user_id) {
            if ($colab_user_id == $user_id) continue;
            $ins = $pdo->prepare("INSERT INTO reminder_collaborators (reminder_id, user_id) VALUES (?, ?)");
            $ins->execute([$id, $colab_user_id]);
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
  <title>Editar Recordatorio</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
  <h1>Editar Recordatorio</h1>
  <form method="POST">
    <label>Título:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($reminder['title']); ?>" required>

    <label>Categoría:</label>
    <select name="category_id">
      <option value="">Sin categoría</option>
      <?php foreach($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>"
          <?php if($cat['id'] == $reminder['category_id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($cat['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Descripción:</label>
    <textarea name="description"><?php echo htmlspecialchars($reminder['description']); ?></textarea>

    <label>Fecha límite:</label>
    <input type="date" name="due_date" value="<?php echo $reminder['due_date']; ?>">

    <label>Colaboradores:</label>
    <select name="collaborators[]" multiple>
      <?php foreach($allUsers as $u): ?>
        <?php if ($u['id'] == $user_id) continue; ?>
        <option value="<?php echo $u['id']; ?>"
          <?php if (in_array($u['id'], $currentCollabs)) echo 'selected'; ?>>
          <?php echo htmlspecialchars($u['username']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <fieldset style="margin-top:10px;">
      <legend>¿Recurrente?</legend>
      <input type="checkbox" id="recurr" name="is_recurrent" value="1"
        <?php if($reminder['is_recurrent']) echo 'checked'; ?> />
      <label for="recurr">Sí, repetir</label>

      <div style="margin-top:6px;">
        <label>Tipo:</label>
        <select name="recurrence_type">
          <option value="daily"   <?php if($reminder['recurrence_type']=='daily') echo 'selected'; ?>>Diaria</option>
          <option value="weekly"  <?php if($reminder['recurrence_type']=='weekly') echo 'selected'; ?>>Semanal</option>
          <option value="monthly" <?php if($reminder['recurrence_type']=='monthly') echo 'selected'; ?>>Mensual</option>
        </select>
        <label>Cada</label>
        <input type="number" name="recurrence_interval" value="<?php echo $reminder['recurrence_interval']; ?>" style="width:60px;" />
        <span>(días/semanas/meses)</span>
      </div>
    </fieldset>

    <button type="submit">Guardar cambios</button>
    <a href="index.php">Volver</a>
  </form>
</div>
</body>
</html>
