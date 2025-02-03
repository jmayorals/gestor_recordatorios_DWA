<!-- partials/list_tab.php -->
<?php
$user_id = $_SESSION['user_id'];
$filter_cat = $_GET['cat'] ?? '';

// Obtener categorías globales (para el filtro)
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta de recordatorios que incluye tanto los propios como los colaborativos
$sql = "
  SELECT DISTINCT r.*, 
         c.name as category_name, 
         c.color as category_color,
         CASE WHEN r.user_id = :uid THEN 1 ELSE 0 END as is_owner
  FROM reminders r
  LEFT JOIN categories c ON r.category_id = c.id
  LEFT JOIN reminder_collaborators rc ON r.id = rc.reminder_id
  WHERE r.user_id = :uid OR rc.user_id = :uid
";
$params = ['uid' => $user_id];

if ($filter_cat !== '') {
    $sql .= " AND r.category_id = :catid ";
    $params['catid'] = $filter_cat;
}

$sql .= " ORDER BY r.due_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Filtro por categoría -->
<form method="GET" style="margin-bottom:10px;">
  <label>Filtrar por Categoría:</label>
  <select name="cat">
    <option value="">Todas</option>
    <?php foreach($categories as $cat): ?>
      <option value="<?php echo $cat['id']; ?>" 
        <?php echo ($filter_cat == $cat['id']) ? 'selected' : ''; ?>>
        <?php echo htmlspecialchars($cat['name']); ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Filtrar</button>
</form>

<table>
  <thead>
    <tr>
      <th>Título</th>
      <th>Categoría</th>
      <th>Descripción</th>
      <th>Fecha Límite</th>
      <th>Recurrente</th>
      <th>Colaboradores</th>
      <th>Rol</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if(!empty($reminders)): ?>
      <?php foreach($reminders as $r): ?>
        <?php
        // Obtener los colaboradores para cada recordatorio
        $collabStmt = $pdo->prepare("
          SELECT u.username
          FROM reminder_collaborators rc
          JOIN users u ON rc.user_id = u.id
          WHERE rc.reminder_id = ?
        ");
        $collabStmt->execute([$r['id']]);
        $collabs = $collabStmt->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <tr>
          <td><?php echo htmlspecialchars($r['title']); ?></td>
          <td>
            <?php if ($r['category_name']): ?>
              <span style="color:<?php echo $r['category_color']; ?>;">
                <?php echo htmlspecialchars($r['category_name']); ?>
              </span>
            <?php else: ?>
              Sin categoría
            <?php endif; ?>
          </td>
          <td><?php echo htmlspecialchars($r['description']); ?></td>
          <td><?php echo $r['due_date']; ?></td>
          <td>
            <?php if($r['is_recurrent']): ?>
              <?php echo $r['recurrence_type'].' (cada '.$r['recurrence_interval'].')'; ?>
            <?php else: ?>
              No
            <?php endif; ?>
          </td>
          <td>
            <?php echo $collabs ? implode(', ', $collabs) : 'Nadie'; ?>
          </td>
          <td>
            <?php echo ($r['is_owner'] ? 'Propietario' : 'Colaborador'); ?>
          </td>
          <td>
            <?php if($r['is_completed']): ?>
              <span class="status-completed">Completado</span>
            <?php else: ?>
              <span class="status-pending">Pendiente</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!$r['is_completed']): ?>
              <a href="complete.php?id=<?php echo $r['id']; ?>">Completar</a> |
            <?php endif; ?>
            <a href="edit.php?id=<?php echo $r['id']; ?>">Editar</a> |
            <a href="delete.php?id=<?php echo $r['id']; ?>"
               onclick="return confirm('¿Deseas eliminar este recordatorio?');">
               Eliminar
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="9">No hay recordatorios que mostrar.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
