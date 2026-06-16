<?php include BASE_PATH . '/app/views/admin/_nav.php'; ?>
<?php
$catNames = ['spam'=>'Спам','insult'=>'Оскорбления/травля','violence'=>'Насилие','adult'=>'Порнография (18+)','fraud'=>'Мошенничество','hate'=>'Разжигание вражды','other'=>'Другое'];
$typeNames = ['post'=>'Запись','comment'=>'Комментарий','user'=>'Пользователь','group'=>'Сообщество','message'=>'Сообщение'];
?>

<?php if (empty($queue)): ?>
<div class="card text-center py-5" style="color:var(--text-muted)">
  <i class="bi bi-check2-circle" style="font-size:3rem;color:var(--success);opacity:.6;display:block;margin-bottom:12px"></i>
  <p class="mb-0">Очередь жалоб пуста — нерассмотренных обращений нет.</p>
</div>
<?php else: ?>

<!-- Панель массовых действий -->
<div class="bulk-bar" id="bulkBar" style="display:none">
  <span id="bulkCount" style="font-weight:600">Выбрано: 0</span>
  <div class="d-flex gap-2 ms-auto">
    <button class="btn btn-sm btn-warning" onclick="bulkAction('hide')"><i class="bi bi-eye-slash me-1"></i>Скрыть</button>
    <button class="btn btn-sm btn-danger" onclick="bulkAction('remove')"><i class="bi bi-trash me-1"></i>Удалить</button>
    <button class="btn btn-sm btn-outline-secondary" onclick="bulkAction('dismiss')"><i class="bi bi-check-lg me-1"></i>Отклонить</button>
  </div>
</div>
<div class="mb-2">
  <label style="font-size:.85rem;color:var(--text-muted);cursor:pointer">
    <input type="checkbox" id="selAll" onchange="toggleAll(this)"> Выбрать все
  </label>
</div>

<div class="d-flex flex-column gap-3">
  <?php foreach ($queue as $r):
    $p = $r['preview']; $type = $r['target_type']; $tid = (int)$r['target_id'];
    $cats = explode(',', $r['categories']); ?>
  <div class="card" id="report-<?= $type ?>-<?= $tid ?>">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
          <input type="checkbox" class="rep-check me-2" data-type="<?= $type ?>" data-id="<?= $tid ?>" onchange="updateBulk()">
          <span class="badge bg-secondary me-1"><?= $typeNames[$type] ?? $type ?></span>
          <?php foreach ($cats as $c): ?>
          <span class="badge bg-danger me-1"><?= $catNames[$c] ?? $c ?></span>
          <?php endforeach; ?>
          <span class="ms-1" style="color:var(--text-muted);font-size:.8rem">
            <i class="bi bi-flag-fill"></i> <?= $r['reports_count'] ?> жалоб · <?= timeAgo($r['last_reported']) ?>
          </span>
        </div>
      </div>

      <?php if (empty($p['exists'])): ?>
      <div class="p-2 rounded" style="background:var(--bg-input);color:var(--text-muted);font-size:.85rem">
        <i class="bi bi-exclamation-triangle me-1"></i>Объект уже удалён.
      </div>
      <?php else: ?>
      <div class="p-3 rounded mb-2" style="background:var(--bg-input)">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <a href="<?= $p['link'] ?>" target="_blank" class="fw-semibold text-decoration-none" style="color:var(--text-primary)">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($p['author']) ?>
            <?php if (!empty($p['username'])): ?><span style="color:var(--text-muted);font-size:.8rem">@<?= htmlspecialchars($p['username']) ?></span><?php endif; ?>
          </a>
          <?php if (!empty($p['status']) && $p['status'] !== 'active'): ?>
          <span class="badge bg-warning text-dark">статус: <?= $p['status'] ?></span>
          <?php endif; ?>
        </div>
        <div style="color:var(--text-secondary);font-size:.9rem;white-space:pre-wrap"><?= htmlspecialchars($p['text'] ?: '(без текста)') ?></div>
      </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2 mt-2">
        <?php if (in_array($type, ['post','comment'])): ?>
        <button class="btn btn-sm btn-warning" onclick="resolveReport('<?= $type ?>',<?= $tid ?>,'hide',this)">
          <i class="bi bi-eye-slash me-1"></i>Скрыть
        </button>
        <button class="btn btn-sm btn-danger" onclick="resolveReport('<?= $type ?>',<?= $tid ?>,'remove',this)">
          <i class="bi bi-trash me-1"></i>Удалить
        </button>
        <?php endif; ?>
        <?php if (!empty($p['authorId'])): ?>
        <a href="<?= BASE_URL ?>/admin/users?q=<?= urlencode($p['username'] ?? '') ?>" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-person-x me-1"></i>К автору
        </a>
        <?php endif; ?>
        <button class="btn btn-sm btn-outline-secondary" onclick="resolveReport('<?= $type ?>',<?= $tid ?>,'dismiss',this)">
          <i class="bi bi-check-lg me-1"></i>Отклонить жалобы
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function resolveReport(type, id, action, btn) {
  if (action === 'remove' && !confirm('Удалить контент?')) return;
  postAction(BASE_URL + '/admin/report/resolve', { target_type: type, target_id: id, action: action }, () => {
    document.getElementById('report-' + type + '-' + id)?.remove();
    showToast(action === 'dismiss' ? 'Жалобы отклонены' : 'Контент обработан', 'success');
    updateBulk();
  });
}

function selectedItems() {
  return [...document.querySelectorAll('.rep-check:checked')].map(c => ({ type: c.dataset.type, id: parseInt(c.dataset.id) }));
}
function updateBulk() {
  const items = selectedItems();
  document.getElementById('bulkCount').textContent = 'Выбрано: ' + items.length;
  document.getElementById('bulkBar').style.display = items.length ? 'flex' : 'none';
}
function toggleAll(cb) {
  document.querySelectorAll('.rep-check').forEach(c => c.checked = cb.checked);
  updateBulk();
}
function bulkAction(action) {
  const items = selectedItems();
  if (!items.length) return;
  if (action === 'remove' && !confirm('Удалить ' + items.length + ' объект(ов)?')) return;
  postAction(BASE_URL + '/admin/report/bulk', { action: action, items: JSON.stringify(items) }, res => {
    items.forEach(it => document.getElementById('report-' + it.type + '-' + it.id)?.remove());
    document.getElementById('selAll').checked = false;
    updateBulk();
    showToast('Обработано: ' + res.count, 'success');
  });
}
</script>
