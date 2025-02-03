<?php
// calendar_events.php
require_once 'includes/session_check.php';
require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT id, title, due_date, is_completed
  FROM reminders
  WHERE user_id = ?
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($rows as $r) {
    $events[] = [
        'id'    => $r['id'],
        'title' => $r['title'] . ($r['is_completed'] ? ' (Completado)' : ''),
        'start' => $r['due_date'],  
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
