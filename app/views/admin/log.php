<?php include BASE_PATH . '/app/views/admin/_nav.php'; ?>
<?php
$actNames = [
  'ban'=>'Блокировка','unban'=>'Разблокировка','mute'=>'Запрет публикаций','unmute'=>'Снятие запрета',
  'warn'=>'Предупреждение','set_role'=>'Смена роли','verify'=>'Верификация','unverify'=>'Снятие верификации',
  'hide_post'=>'Скрытие записи','remove_post'=>'Удаление записи','hide_comment'=>'Скрытие комментария',
  'remove_comment'=>'Удаление комментария','dismiss_reports'=>'Отклонение жалоб','add_word'=>'Добавлено стоп-слово',
  'remove_word'=>'Удалено стоп-слово','set_status_active'=>'Восстановление','set_status_hidden'=>'Скрытие',
  'set_status_removed'=>'Удаление',
];
?>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($entries)): ?>
    <div class="text-center py-5" style="color:var(--text-muted)">Журнал пуст</div>
    <?php else: ?>
    <?php foreach ($entries as $e): ?>
    <div class="d-flex align-items-center gap-3 p-2 px-3" style="border-bottom:1px solid var(--border-light)">
      <i class="bi bi-clock-history" style="color:var(--text-muted)"></i>
      <div class="flex-grow-1">
        <span class="fw-semibold" style="color:var(--text-primary);font-size:.875rem"><?= htmlspecialchars($e['first_name'].' '.$e['last_name']) ?></span>
        <span style="color:var(--text-secondary);font-size:.875rem"> — <?= $actNames[$e['action']] ?? $e['action'] ?></span>
        <?php if ($e['target_type']): ?><span class="badge bg-secondary ms-1"><?= $e['target_type'] ?> #<?= $e['target_id'] ?></span><?php endif; ?>
        <?php if ($e['details']): ?><span style="color:var(--text-muted);font-size:.8rem"> · <?= htmlspecialchars($e['details']) ?></span><?php endif; ?>
      </div>
      <span style="font-size:.75rem;color:var(--text-muted);flex-shrink:0"><?= date('d.m.Y H:i', strtotime($e['created_at'])) ?></span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php if (count($entries) === 80): ?>
<div class="text-center mt-3"><a href="?page=<?= $page+1 ?>" class="btn btn-outline-secondary btn-sm">Показать ещё</a></div>
<?php endif; ?>
