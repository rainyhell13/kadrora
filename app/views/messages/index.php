<div class="messages-wrap">

  <!-- Список диалогов -->
  <div class="conv-list">
    <div class="conv-list-header">
      <i class="bi bi-chat-fill me-2" style="color:var(--accent)"></i>Сообщения
    </div>
    <div class="conv-list-body">
      <?php if (empty($conversations)): ?>
      <div class="text-center py-5" style="color:var(--text-muted)">
        <i class="bi bi-chat" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:10px"></i>
        <p class="small mb-0">Нет диалогов</p>
      </div>
      <?php else: ?>
      <?php foreach ($conversations as $conv): ?>
      <a href="<?= BASE_URL ?>/messages/<?= $conv['id'] ?>" class="conv-item text-decoration-none">
        <div class="position-relative flex-shrink-0">
          <?php if ($conv['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($conv['avatar']) ?>"
               class="rounded-circle" width="46" height="46" style="object-fit:cover" alt="">
          <?php else: ?>
          <div class="avatar-placeholder" style="width:46px;height:46px">
            <?= mb_strtoupper(mb_substr($conv['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <?php if ($conv['is_online']): ?>
          <span class="online-dot position-absolute" style="bottom:1px;right:1px"></span>
          <?php endif; ?>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex justify-content-between">
            <span class="conv-name text-truncate">
              <?= htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']) ?>
            </span>
            <small style="color:var(--text-muted);flex-shrink:0;margin-left:4px;font-size:.7rem">
              <?= $conv['last_message_at'] ? timeAgo($conv['last_message_at']) : '' ?>
            </small>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="conv-preview">
              <?php if ($conv['last_sender_id'] == $me['id']): ?>
              <i class="bi bi-check2" style="color:var(--accent)"></i>
              <?php endif; ?>
              <?= htmlspecialchars(mb_substr($conv['last_message'] ?? 'Диалог начат', 0, 32)) ?>
            </span>
            <?php if ($conv['unread_count'] > 0): ?>
            <span class="badge bg-primary rounded-pill ms-1"><?= $conv['unread_count'] ?></span>
            <?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Плейсхолдер -->
  <div class="chat-area d-none d-md-flex align-items-center justify-content-center" style="color:var(--text-muted)">
    <div class="text-center">
      <i class="bi bi-chat-square-text" style="font-size:2.8rem;opacity:.2;display:block;margin-bottom:10px"></i>
      <p class="mb-0">Выберите диалог для начала переписки</p>
    </div>
  </div>
</div>
