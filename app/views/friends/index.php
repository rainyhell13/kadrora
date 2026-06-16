<div class="row g-4">
  <div class="col-lg-8">

    <!-- Входящие заявки -->
    <?php if (!empty($pending)): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-person-exclamation me-1 text-warning"></i>
        Заявки в друзья
        <span class="badge bg-warning text-dark ms-1"><?= count($pending) ?></span>
      </div>
      <div class="card-body p-2">
        <?php foreach ($pending as $req): ?>
        <div class="d-flex align-items-center gap-3 p-2 rounded hover-bg" id="req-<?= $req['requester'] ?>">
          <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($req['username']) ?>" class="flex-shrink-0" title="Открыть профиль">
          <?php if ($req['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($req['avatar']) ?>"
               class="rounded-circle" width="52" height="52" style="object-fit:cover;cursor:pointer" alt="">
          <?php else: ?>
          <div class="avatar-placeholder rounded-circle" style="width:52px;height:52px;cursor:pointer">
            <?= mb_strtoupper(mb_substr($req['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          </a>
          <div class="flex-grow-1">
            <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($req['username']) ?>"
               class="fw-semibold text-dark text-decoration-none">
              <?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?>
            </a>
            <div class="small text-muted"><?= timeAgo($req['created_at']) ?></div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm"
                    onclick="acceptFriend(<?= $req['requester'] ?>)">
              <i class="bi bi-check-lg"></i> Принять
            </button>
            <button class="btn btn-outline-secondary btn-sm"
                    onclick="declineFriendReq(<?= $req['requester'] ?>)">
              Отклонить
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Список друзей -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-people me-1 text-primary"></i>
        Мои друзья
        <span class="badge bg-primary ms-1"><?= count($friends) ?></span>
      </div>
      <div class="card-body p-2">
        <?php if (empty($friends)): ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
          <p>У вас пока нет друзей. <a href="<?= BASE_URL ?>/search">Найдите знакомых!</a></p>
        </div>
        <?php else: ?>
        <div class="row g-2">
          <?php foreach ($friends as $friend): ?>
          <div class="col-sm-6 col-md-4" id="friend-<?= $friend['id'] ?>">
            <div class="d-flex align-items-center gap-2 p-2 rounded border hover-bg">
              <?php if ($friend['avatar']): ?>
              <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($friend['avatar']) ?>"
                   class="rounded-circle" width="48" height="48" style="object-fit:cover" alt="">
              <?php else: ?>
              <div class="avatar-placeholder rounded-circle" style="width:48px;height:48px">
                <?= mb_strtoupper(mb_substr($friend['first_name'],0,1)) ?>
              </div>
              <?php endif; ?>
              <div class="flex-grow-1 min-w-0">
                <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($friend['username']) ?>"
                   class="fw-medium text-dark text-decoration-none text-truncate d-block">
                  <?= htmlspecialchars($friend['first_name'] . ' ' . $friend['last_name']) ?>
                </a>
                <?php if ($friend['is_online']): ?>
                <small class="text-success"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> онлайн</small>
                <?php else: ?>
                <small class="text-muted"><?= timeAgo($friend['last_seen']) ?></small>
                <?php endif; ?>
              </div>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="dropdown">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="<?= BASE_URL ?>/messages/<?= $friend['id'] ?>">
                    <i class="bi bi-chat me-2"></i>Написать</a></li>
                  <li><button class="dropdown-item text-danger"
                              onclick="removeFriendFromList(<?= $friend['id'] ?>)">
                    <i class="bi bi-person-x me-2"></i>Удалить из друзей</button></li>
                </ul>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Боковой: отправленные заявки -->
  <div class="col-lg-4">
    <?php if (!empty($sent)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 fw-semibold">
        <i class="bi bi-send me-1 text-secondary"></i>Отправленные заявки
      </div>
      <div class="card-body p-2">
        <?php foreach ($sent as $s): ?>
        <div class="d-flex align-items-center gap-2 p-2 rounded hover-bg">
          <?php if ($s['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($s['avatar']) ?>"
               class="rounded-circle" width="40" height="40" style="object-fit:cover" alt="">
          <?php else: ?>
          <div class="avatar-placeholder rounded-circle" style="width:40px;height:40px;font-size:.9rem">
            <?= mb_strtoupper(mb_substr($s['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($s['username']) ?>"
               class="text-dark text-decoration-none small fw-medium text-truncate d-block">
              <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
            </a>
            <small class="text-muted"><?= timeAgo($s['created_at']) ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function acceptFriend(userId) {
  postAction(BASE_URL + '/friend/accept', { user_id: userId }, () => {
    document.getElementById('req-' + userId)?.remove();
    showToast('Заявка принята', 'success');
  });
}
function declineFriendReq(userId) {
  postAction(BASE_URL + '/friend/decline', { user_id: userId }, () => {
    document.getElementById('req-' + userId)?.remove();
  });
}
function removeFriendFromList(userId) {
  if (!confirm('Удалить из друзей?')) return;
  postAction(BASE_URL + '/friend/remove', { user_id: userId }, () => {
    document.getElementById('friend-' + userId)?.remove();
    showToast('Удалён из друзей', 'info');
  });
}
</script>
