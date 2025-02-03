<?php
// delete.php
require_once 'includes/session_check.php';
require_once 'includes/db.php';

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT * FROM reminders 
  WHERE id = ? 
    AND (user_id = ? OR id IN (SELECT reminder_id FROM reminder_collaborators WHERE user_id = ?))
");
$stmt->execute([$id, $user_id, $user_id]);
$reminder = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reminder) {
    $del = $pdo->prepare("DELETE FROM reminders WHERE id = ?");
    $del->execute([$id]);
}

header("Location: index.php");
exit;
