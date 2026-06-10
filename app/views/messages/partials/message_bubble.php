<?php
$uid  = $uid  ?? $_SESSION['user_id'] ?? 0;
$isMe = (int)$msg['sender_id'] === (int)$uid;
$time = isset($msg['created_at']) ? date('H:i', strtotime($msg['created_at'])) : '';
?>
<div class="bubble-wrap <?= $isMe ? 'mine' : '' ?>" id="msg-<?= $msg['id'] ?>">
  <?php if (!$isMe): ?>
  <div class="me-2">
    <?php if (!empty($msg['avatar'])): ?>
    <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($msg['avatar']) ?>"
         class="rounded-circle" width="30" height="30" style="object-fit:cover;flex-shrink:0" alt="">
    <?php else: ?>
    <div class="avatar-placeholder" style="width:30px;height:30px;font-size:.7rem;flex-shrink:0">
      <?= mb_strtoupper(mb_substr($msg['first_name'] ?? 'U', 0, 1)) ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div style="max-width:65%;min-width:0">
    <?php if (!empty($msg['image'])): ?>
    <div class="bubble" style="padding:6px">
      <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($msg['image']) ?>"
           class="rounded cursor-pointer" style="max-height:200px;max-width:100%;display:block"
           onclick="openPhotoModal(this.src)" alt="">
    </div>
    <?php endif; ?>
    <?php if ($msg['content'] && $msg['content'] !== '📷 Фото'): ?>
    <div class="bubble"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
    <?php endif; ?>
    <div class="bubble-time"><?= $time ?></div>
  </div>
</div>
