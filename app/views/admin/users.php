<?php include BASE_PATH . '/app/views/admin/_nav.php'; ?>
<?php $um = new User(); ?>

<form class="mb-3" method="GET" action="<?= BASE_URL ?>/admin/users">
  <div class="row g-2">
    <div class="col-md-6">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control" placeholder="Имя, username, email..." value="<?= htmlspecialchars($q) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="filter" class="form-select" onchange="this.form.submit()">
        <?php foreach (['all'=>'Все','banned'=>'Заблокированные','muted'=>'С мьютом','staff'=>'Модерация'] as $k=>$v): ?>
        <option value="<?= $k ?>" <?= $filter===$k?'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Найти</button></div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($list)): ?>
    <div class="text-center py-5" style="color:var(--text-muted)">Пользователи не найдены</div>
    <?php else: ?>
    <?php foreach ($list as $u):
      $banned = $um->isCurrentlyBanned($u); $muted = $um->isCurrentlyMuted($u);
      $isAdmin = $u['role'] === 'admin'; $isSelf = $u['id'] == ($me['id'] ?? 0); ?>
    <div class="d-flex align-items-center gap-3 p-3" style="border-bottom:1px solid var(--border-light)" id="urow-<?= $u['id'] ?>">
      <?php if ($u['avatar']): ?>
      <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($u['avatar']) ?>" class="rounded-circle" width="44" height="44" style="object-fit:cover;flex-shrink:0" alt="">
      <?php else: ?>
      <div class="avatar-placeholder flex-shrink-0" style="width:44px;height:44px;font-size:1rem"><?= mb_strtoupper(mb_substr($u['first_name'],0,1)) ?></div>
      <?php endif; ?>
      <div class="flex-grow-1 min-w-0">
        <div>
          <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($u['username']) ?>" target="_blank" class="fw-semibold text-decoration-none" style="color:var(--text-primary)">
            <?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?>
          </a>
          <?php if (!empty($u['is_verified'])): ?><i class="bi bi-patch-check-fill" style="color:var(--accent)" title="Верифицирован"></i><?php endif; ?>
          <?php if ($u['role']!=='user'): ?><span class="badge bg-primary ms-1"><?= $u['role']==='admin'?'Админ':'Модератор' ?></span><?php endif; ?>
          <?php if ($banned): ?><span class="badge bg-danger ms-1">Бан</span><?php endif; ?>
          <?php if ($muted): ?><span class="badge bg-warning text-dark ms-1">Мьют</span><?php endif; ?>
          <?php if ((int)$u['warnings_count']>0): ?><span class="badge bg-secondary ms-1" title="Предупреждения">⚠ <?= $u['warnings_count'] ?></span><?php endif; ?>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted)">@<?= htmlspecialchars($u['username']) ?> · <?= htmlspecialchars($u['email']) ?> · рег. <?= date('d.m.Y', strtotime($u['created_at'])) ?></div>
      </div>

      <?php if (!$isAdmin && !$isSelf): ?>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if (!$banned): ?>
          <li><button class="dropdown-item text-danger" onclick="uAction(<?= $u['id'] ?>,'ban')"><i class="bi bi-slash-circle me-2"></i>Заблокировать</button></li>
          <?php else: ?>
          <li><button class="dropdown-item text-success" onclick="uAction(<?= $u['id'] ?>,'unban')"><i class="bi bi-check-circle me-2"></i>Разблокировать</button></li>
          <?php endif; ?>
          <?php if (!$muted): ?>
          <li><button class="dropdown-item" onclick="uAction(<?= $u['id'] ?>,'mute')"><i class="bi bi-mic-mute me-2"></i>Запретить публикации</button></li>
          <?php else: ?>
          <li><button class="dropdown-item" onclick="uAction(<?= $u['id'] ?>,'unmute')"><i class="bi bi-mic me-2"></i>Снять запрет</button></li>
          <?php endif; ?>
          <li><button class="dropdown-item text-warning" onclick="uAction(<?= $u['id'] ?>,'warn')"><i class="bi bi-exclamation-triangle me-2"></i>Предупреждение</button></li>
          <li><button class="dropdown-item" onclick="uAction(<?= $u['id'] ?>,'verify',{value: <?= empty($u['is_verified'])?1:0 ?>})">
            <i class="bi bi-patch-check me-2"></i><?= empty($u['is_verified'])?'Верифицировать':'Снять верификацию' ?></button></li>
          <?php if (($role ?? '')==='admin'): ?>
          <li><hr class="dropdown-divider"></li>
          <li><button class="dropdown-item" onclick="uAction(<?= $u['id'] ?>,'role',{role: <?= $u['role']==='moderator'?"'user'":"'moderator'" ?>})">
            <i class="bi bi-shield me-2"></i><?= $u['role']==='moderator'?'Снять модератора':'Назначить модератором' ?></button></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php else: ?>
      <span style="font-size:.75rem;color:var(--text-muted)"><?= $isSelf ? 'это вы' : 'администратор' ?></span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php if (count($list) === 30): ?>
<div class="text-center mt-3"><a href="?q=<?= urlencode($q) ?>&filter=<?= $filter ?>&page=<?= $page+1 ?>" class="btn btn-outline-secondary btn-sm">Показать ещё</a></div>
<?php endif; ?>

<script>
function uAction(id, action, extra) {
  const data = { user_id: id, action: action, ...(extra||{}) };
  if (action === 'ban') {
    const days = prompt('Срок блокировки в днях (0 или пусто — навсегда):', '0');
    if (days === null) return;
    data.days = parseInt(days) || 0;
    data.reason = prompt('Причина блокировки:', '') || '';
  } else if (action === 'mute') {
    const days = prompt('Срок запрета публикаций в днях (0 — бессрочно):', '3');
    if (days === null) return;
    data.days = parseInt(days) || 0;
  } else if (action === 'warn') {
    data.reason = prompt('Причина предупреждения:', '') || '';
  }
  postAction(BASE_URL + '/admin/user/action', data, () => {
    showToast('Готово', 'success');
    setTimeout(() => location.reload(), 600);
  });
}
</script>
