<?php
// complete.php
require_once 'includes/session_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verificar si el usuario es propietario o colaborador del recordatorio
$stmt = $pdo->prepare("
  SELECT * FROM reminders 
  WHERE id = ? 
    AND (user_id = ? OR id IN (SELECT reminder_id FROM reminder_collaborators WHERE user_id = ?))
");
$stmt->execute([$id, $user_id, $user_id]);
$reminder = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reminder) {
    // Marcar como completado
    $upd = $pdo->prepare("UPDATE reminders SET is_completed=1 WHERE id=?");
    $upd->execute([$id]);

    // Si es recurrente, creamos el siguiente
    if ($reminder['is_recurrent']) {
        createNextRecurrentReminder($pdo, $reminder);
    }
}

header("Location: index.php");
exit;
