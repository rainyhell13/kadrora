<?php
$uid     = $uid     ?? $_SESSION['user_id']    ?? 0;
$csrf    = $csrf    ?? $_SESSION['csrf_token'] ?? '';
$isAdmin = $isAdmin ?? false;
$post['liked_by_me'] = $post['liked_by_me'] ?? false;
$post['likes_count'] = $post['likes_count'] ?? 0;
?>
<div class="post-card" id="gpost-<?= $post['id'] ?>">
  <div class="post-header">
    <div class="d-flex align-items-center gap-2">
      <?php if ($post['avatar']): ?>
      <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($post['avatar']) ?>"
           class="rounded-circle" width="40" height="40" style="object-fit:cover;border:2px solid var(--border)" alt="">
      <?php else: ?>
      <div class="avatar-placeholder" style="width:40px;height:40px;font-size:.9rem">
        <?= mb_strtoupper(mb_substr($post['first_name'],0,1)) ?>
      </div>
      <?php endif; ?>
      <div>
        <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($post['username']) ?>"
           class="fw-semibold text-decoration-none" style="color:var(--text-primary);font-size:.9rem">
          <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>
        </a>
        <div style="font-size:.73rem;color:var(--text-muted)"><?= timeAgo($post['created_at']) ?></div>
      </div>
    </div>
    <?php if ((int)$post['user_id'] === (int)$uid || $isAdmin): ?>
    <div class="dropdown">
      <button class="btn btn-sm border-0 px-2" data-bs-toggle="dropdown" style="background:none;color:var(--text-muted)">
        <i class="bi bi-three-dots"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><button class="dropdown-item text-danger" onclick="deleteGroupPost(<?= $post['id'] ?>)">
          <i class="bi bi-trash me-2"></i>Удалить</button></li>
      </ul>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($post['content']): ?>
  <div class="post-body"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
  <?php endif; ?>

  <?php if ($post['image']): ?>
  <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($post['image']) ?>"
       class="post-image" onclick="openPhotoModal(this.src)" alt="">
  <?php endif; ?>

  <div class="post-stats">
    <span id="glikes-count-<?= $post['id'] ?>">
      <?php if ($post['likes_count'] > 0): ?>
      <i class="bi bi-heart-fill me-1" style="color:var(--danger)"></i><?= $post['likes_count'] ?>
      <?php endif; ?>
    </span>
    <span></span>
  </div>

  <div class="post-actions">
    <button class="post-action-btn <?= $post['liked_by_me'] ? 'liked' : '' ?>"
            id="glike-btn-<?= $post['id'] ?>"
            onclick="likeGroupPost(<?= $post['id'] ?>, this)">
      <i class="bi bi-<?= $post['liked_by_me'] ? 'heart-fill' : 'heart' ?>"></i> Нравится
    </button>
  </div>
</div>
