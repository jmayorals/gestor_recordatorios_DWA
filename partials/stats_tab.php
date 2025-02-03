<?php
// partials/stats_tab.php
$user_id = $_SESSION['user_id'];

// Conteo de completados vs pendientes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reminders WHERE user_id=? AND is_completed=1");
$stmt->execute([$user_id]);
$completed = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reminders WHERE user_id=? AND is_completed=0");
$stmt->execute([$user_id]);
$pending = $stmt->fetchColumn();
?>
<h2>Estadísticas</h2>

<canvas id="statsChart" width="400" height="200"></canvas>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('statsChart').getContext('2d');
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Completados', 'Pendientes'],
      datasets: [{
        data: [<?php echo $completed; ?>, <?php echo $pending; ?>],
        backgroundColor: ['#06b06b', '#c72b2b']
      }]
    }
  });
});
</script>
