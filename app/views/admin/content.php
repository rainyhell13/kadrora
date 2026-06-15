<?php include BASE_PATH . '/app/views/admin/_nav.php'; ?>

<div class="d-flex gap-2 mb-3">
  <a href="<?= BASE_URL ?>/admin/content?tab=flagged" class="btn btn-sm <?= $tab==='flagged'?'btn-primary':'btn-outline-secondary' ?>">Помеченные / скрытые</a>
  <a href="<?= BASE_URL ?>/admin/content?tab=all" class="btn btn-sm <?= $tab==='all'?'btn-primary':'btn-outline-secondary' ?>">Все записи</a>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($posts)): ?>
    <div class="text-center py-5" style="color:var(--text-muted)"><?= $tab==='flagged'?'Помеченных записей нет':'Записей нет' ?></div>
    <?php else: ?>
    <?php foreach ($posts as $p):
      $statusBadge = ['active'=>['','—'],'flagged'=>['bg-warning text-dark','автофильтр'],'hidden'=>['bg-secondary','скрыт'],'removed'=>['bg-danger','удалён']]; ?>
    <div class="p-3" style="border-bottom:1px solid var(--border-light)" id="cpost-<?= $p['id'] ?>">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
          <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($p['username']) ?>" target="_blank" class="fw-semibold text-decoration-none" style="color:var(--text-primary)">
            <?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?>
          </a>
          <span style="font-size:.78rem;color:var(--text-muted)"><?= timeAgo($p['created_at']) ?></span>
          <?php if (($p['status']??'active')!=='active'): $sb=$statusBadge[$p['status']]; ?>
          <span class="badge <?= $sb[0] ?> ms-1"><?= $sb[1] ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="mb-2" style="color:var(--text-secondary);font-size:.9rem;white-space:pre-wrap"><?= htmlspecialchars(mb_substr($p['content']??'',0,300)) ?></div>
      <div class="d-flex gap-2">
        <?php if (($p['status']??'active')!=='active'): ?>
        <button class="btn btn-sm btn-success" onclick="cAction(<?= $p['id'] ?>,'active')"><i class="bi bi-arrow-counterclockwise me-1"></i>Восстановить</button>
        <?php endif; ?>
        <?php if (($p['status']??'active')!=='hidden'): ?>
        <button class="btn btn-sm btn-warning" onclick="cAction(<?= $p['id'] ?>,'hidden')"><i class="bi bi-eye-slash me-1"></i>Скрыть</button>
        <?php endif; ?>
        <button class="btn btn-sm btn-danger" onclick="cAction(<?= $p['id'] ?>,'removed')"><i class="bi bi-trash me-1"></i>Удалить</button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
function cAction(id, status) {
  if (status==='removed' && !confirm('Удалить запись?')) return;
  postAction(BASE_URL + '/admin/content/action', { post_id: id, status: status }, () => {
    showToast('Готово', 'success'); setTimeout(()=>location.reload(), 500);
  });
}
</script>
