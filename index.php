<?php
// index.php
require_once 'includes/session_check.php';
require_once 'includes/db.php';

// Obtenemos el username
$username = $_SESSION['username'];

// Consulta: Recordatorios pendientes para los próximos 7 días
$today = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+7 days'));
$stmt = $pdo->prepare("SELECT title, due_date FROM reminders WHERE user_id = ? AND is_completed = 0 AND due_date BETWEEN ? AND ? ORDER BY due_date ASC");
$stmt->execute([$_SESSION['user_id'], $today, $end_date]);
$pendingReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contamos el número de recordatorios pendientes
$pendingCount = count($pendingReminders);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestor de Recordatorios</title>
  <link rel="stylesheet" href="css/styles.css">

  <!-- FullCalendar (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

  <!-- Chart.js (para estadísticas) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script src="js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var pendingCount = <?php echo $pendingCount; ?>;
      if (pendingCount > 0) {
        if (pendingCount = 1) {
          alert("Tienes 1 recordatorio pendiente que caduca en los próximos 7 días.");
        } else {
          alert("Tienes " + pendingCount + " recordatorios que caducan en los próximos 7 días.");
        }
      }
    });
  </script>
  
</head>
<body>
<div class="container">
  <h1>Gestor de Recordatorios</h1>
  <p>Bienvenido, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>

  <nav>
    <ul class="tabs-nav">
      <li><a href="#" onclick="openTab(event, 'tab-lista')">Lista</a></li>
      <li><a href="#" onclick="openTab(event, 'tab-calendario')">Calendario</a></li>
      <li><a href="#" onclick="openTab(event, 'tab-estadisticas')">Estadísticas</a></li>
    </ul>
  </nav>

  <p>
    <a class="btn" href="create.php">Crear Recordatorio</a>
    <a class="btn" href="logout.php">Cerrar Sesión</a>
  </p>

  <!-- Tab: Lista de recordatorios -->
  <div id="tab-lista" class="tab-content">
    <?php include 'partials/list_tab.php'; ?>
  </div>

  <!-- Tab: Calendario -->
  <div id="tab-calendario" class="tab-content" style="display:none;">
    <div id="calendar"></div>
  </div>

  <!-- Tab: Estadísticas -->
  <div id="tab-estadisticas" class="tab-content" style="display:none;">
    <?php include 'partials/stats_tab.php'; ?>
  </div>

</div>
</body>
</html>
