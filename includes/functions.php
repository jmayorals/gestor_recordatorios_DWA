<?php
// includes/functions.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function createNextRecurrentReminder($pdo, $reminder) {

    $currentDue = $reminder['due_date'];
    $interval  = (int)$reminder['recurrence_interval'];
    $type      = $reminder['recurrence_type'];

    $nextDue = null;
    switch($type) {
        case 'daily':
            $nextDue = date('Y-m-d', strtotime("$currentDue +{$interval} day"));
            break;
        case 'weekly':
            $nextDue = date('Y-m-d', strtotime("$currentDue +{$interval} week"));
            break;
        case 'monthly':
            $nextDue = date('Y-m-d', strtotime("$currentDue +{$interval} month"));
            break;
    }

    if ($nextDue) {
        $stmt = $pdo->prepare("
            INSERT INTO reminders
            (user_id, category_id, title, description, due_date, is_recurrent, recurrence_type, recurrence_interval)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $reminder['user_id'],
            $reminder['category_id'],
            $reminder['title'],
            $reminder['description'],
            $nextDue,
            1, // is_recurrent
            $type,
            $interval
        ]);

        $newReminderId = $pdo->lastInsertId();

        // Copiamos colaboradores, si hay
        $colStmt = $pdo->prepare("SELECT user_id FROM reminder_collaborators WHERE reminder_id=?");
        $colStmt->execute([$reminder['id']]);
        $collabs = $colStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($collabs as $c) {
            $insCol = $pdo->prepare("INSERT INTO reminder_collaborators (reminder_id, user_id) VALUES (?, ?)");
            $insCol->execute([$newReminderId, $c]);
        }
    }
}
