<div class="row justify-content-center">
  <div class="col-lg-7">
    <h4 class="fw-bold mb-4"><i class="bi bi-bell-fill me-2" style="color:var(--accent)"></i>Уведомления</h4>

    <div class="card" style="overflow:hidden">
      <?php if (empty($notifications)): ?>
      <div class="text-center py-5" style="color:var(--text-muted)">
        <i class="bi bi-bell-slash" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px"></i>
        <p>Уведомлений нет</p>
      </div>
      <?php else: ?>
      <?php foreach ($notifications as $n):
        $type = match($n['type']) {
          'post_like'      => 'like',
          'comment'        => 'comment',
          'friend_request',
          'friend_accept'  => 'friend',
          default          => 'default',
        };
        $icon = match($type) {
          'like'    => 'heart-fill',
          'comment' => 'chat-fill',
          'friend'  => 'person-check-fill',
          default   => 'bell-fill',
        };
        $link = match($n['entity_type'] ?? '') {
          'post' => BASE_URL . '/feed#post-' . $n['entity_id'],
          'user' => BASE_URL . '/profile/' . $n['username'],
          default => '#',
        };
      ?>
      <a href="<?= $link ?>" class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
        <div class="position-relative flex-shrink-0">
          <?php if ($n['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($n['avatar']) ?>"
               class="rounded-circle" width="44" height="44" style="object-fit:cover" alt="">
          <?php else: ?>
          <div class="avatar-placeholder" style="width:44px;height:44px;font-size:1rem">
            <?= mb_strtoupper(mb_substr($n['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="notif-icon <?= $type ?> position-absolute"
               style="width:20px;height:20px;font-size:.7rem;bottom:-2px;right:-2px">
            <i class="bi bi-<?= $icon ?>"></i>
          </div>
        </div>
        <div class="flex-grow-1">
          <div style="font-size:.875rem"><?= htmlspecialchars($n['message']) ?></div>
          <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px"><?= timeAgo($n['created_at']) ?></div>
        </div>
        <?php if (!$n['is_read']): ?>
        <div style="width:8px;height:8px;background:var(--accent);border-radius:50%;flex-shrink:0"></div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if (count($notifications) === 20): ?>
    <div class="text-center mt-3">
      <a href="?page=<?= $page+1 ?>" class="btn btn-outline-secondary btn-sm">Показать ещё</a>
    </div>
    <?php endif; ?>
  </div>
</div>
