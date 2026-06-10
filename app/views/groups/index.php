<div class="row g-3">
  <div class="col-lg-9">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="fw-bold mb-0"><i class="bi bi-collection-fill me-2" style="color:var(--accent)"></i>Сообщества</h4>
      <a href="<?= BASE_URL ?>/groups/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Создать сообщество
      </a>
    </div>

    <!-- Tabs -->
    <div class="d-flex gap-2 mb-3">
      <a href="<?= BASE_URL ?>/groups"
         class="btn btn-sm <?= $tab === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">
        Все сообщества
      </a>
      <a href="<?= BASE_URL ?>/groups?tab=my"
         class="btn btn-sm <?= $tab === 'my' ? 'btn-primary' : 'btn-outline-secondary' ?>">
        Мои сообщества
      </a>
    </div>

    <?php if (empty($groups)): ?>
    <div class="card text-center py-5" style="color:var(--text-muted)">
      <i class="bi bi-collection" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px"></i>
      <p class="mb-2">Сообществ пока нет</p>
      <a href="<?= BASE_URL ?>/groups/create" class="btn btn-primary btn-sm mx-auto" style="width:fit-content">
        Создать первое
      </a>
    </div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($groups as $g): ?>
      <div class="col-sm-6 col-md-4">
        <div class="group-card">
          <a href="<?= BASE_URL ?>/groups/<?= htmlspecialchars($g['slug']) ?>" class="text-decoration-none">
          <div class="group-cover"
               style="cursor:pointer;<?= $g['cover'] ? 'background:url('.BASE_URL.'/uploads/photos/'.htmlspecialchars($g['cover']).') center/cover' : '' ?>">
          </div>
          </a>
          <div class="group-avatar-wrap">
            <?php if ($g['avatar']): ?>
            <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($g['avatar']) ?>"
                 class="rounded-circle" width="54" height="54"
                 style="object-fit:cover;border:3px solid var(--bg-base)" alt="">
            <?php else: ?>
            <div class="avatar-placeholder" style="width:54px;height:54px;font-size:1.3rem;border:3px solid var(--bg-base)">
              <?= mb_strtoupper(mb_substr($g['name'],0,1)) ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="group-card-body">
            <a href="<?= BASE_URL ?>/groups/<?= htmlspecialchars($g['slug']) ?>"
               class="group-card-name text-decoration-none d-block">
              <?= htmlspecialchars($g['name']) ?>
            </a>
            <div class="group-card-meta mb-2">
              <i class="bi bi-people me-1"></i><?= $g['members_count'] ?> участников
              · <?= $g['privacy'] === 'public' ? 'Открытое' : 'Закрытое' ?>
            </div>
            <?php if ($g['description']): ?>
            <div class="mb-2" style="font-size:.8rem;color:var(--text-muted);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
              <?= htmlspecialchars($g['description']) ?>
            </div>
            <?php endif; ?>
            <?php if ($g['is_member']): ?>
            <button class="btn btn-outline-secondary btn-sm w-100"
                    onclick="leaveGroup(<?= $g['id'] ?>, this)">
              <i class="bi bi-box-arrow-right me-1"></i>Вы участник
            </button>
            <?php elseif ($g['privacy'] === 'private'): ?>
            <button class="btn btn-outline-primary btn-sm w-100"
                    onclick="joinGroup(<?= $g['id'] ?>, this)">
              <i class="bi bi-key me-1"></i>Подать заявку
            </button>
            <?php else: ?>
            <button class="btn btn-outline-primary btn-sm w-100"
                    onclick="joinGroup(<?= $g['id'] ?>, this)">
              <i class="bi bi-plus-circle me-1"></i>Вступить
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>

  <div class="col-lg-3 d-none d-lg-block">
    <div class="widget sticky-top" style="top:70px">
      <div class="widget-header"><i class="bi bi-lightning-fill me-1"></i>Быстрые действия</div>
      <div class="widget-body">
        <a href="<?= BASE_URL ?>/groups/create" class="friend-item text-decoration-none" style="color:var(--text-secondary)">
          <i class="bi bi-plus-circle" style="font-size:1.1rem;width:20px;color:var(--accent)"></i>
          <span style="font-size:.875rem">Создать сообщество</span>
        </a>
        <a href="<?= BASE_URL ?>/groups?tab=my" class="friend-item text-decoration-none" style="color:var(--text-secondary)">
          <i class="bi bi-collection" style="font-size:1.1rem;width:20px;color:var(--accent)"></i>
          <span style="font-size:.875rem">Мои сообщества</span>
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function joinGroup(id, btn) {
  postAction(BASE_URL + '/groups/join', { group_id: id }, res => {
    if (res.status === 'requested') {
      btn.className = 'btn btn-outline-secondary btn-sm w-100';
      btn.innerHTML = '<i class="bi bi-clock me-1"></i>Заявка отправлена';
      btn.disabled = true;
      showToast('Заявка отправлена администратору', 'success');
      return;
    }
    btn.className = 'btn btn-outline-secondary btn-sm w-100';
    btn.innerHTML = '<i class="bi bi-box-arrow-right me-1"></i>Вы участник';
    btn.onclick = function() { leaveGroup(id, this); };
    showToast('Вы вступили в сообщество', 'success');
  });
}
function leaveGroup(id, btn) {
  if (!confirm('Покинуть сообщество?')) return;
  postAction(BASE_URL + '/groups/leave', { group_id: id }, res => {
    btn.className = 'btn btn-outline-primary btn-sm w-100';
    btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Вступить';
    btn.onclick = function() { joinGroup(id, this); };
    showToast('Вы покинули сообщество', 'info');
  });
}
</script>
