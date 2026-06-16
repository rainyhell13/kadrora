<?php
$uid  = $uid  ?? $_SESSION['user_id']    ?? 0;
$csrf = $csrf ?? $_SESSION['csrf_token'] ?? '';
?>
<div class="comment-item" id="comment-<?= $comment['id'] ?>">
  <?php if ($comment['avatar']): ?>
  <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($comment['avatar']) ?>"
       class="rounded-circle flex-shrink-0" width="30" height="30" style="object-fit:cover" alt="">
  <?php else: ?>
  <div class="avatar-placeholder flex-shrink-0" style="width:30px;height:30px;font-size:.7rem">
    <?= mb_strtoupper(mb_substr($comment['first_name'],0,1)) ?>
  </div>
  <?php endif; ?>

  <div class="comment-bubble">
    <div class="author">
      <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($comment['username']) ?>"
         class="text-decoration-none" style="color:var(--text-primary)">
        <?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?>
      </a>
      <span style="color:var(--text-muted);font-size:.72rem;margin-left:6px"><?= timeAgo($comment['created_at']) ?></span>
      <?php $cLiked = !empty($comment['liked_by_me']); $cLikes = (int)($comment['likes_count'] ?? 0); ?>
      <button class="comment-like-btn ms-2 <?= $cLiked ? 'liked' : '' ?>" id="clike-<?= $comment['id'] ?>"
              onclick="likeComment(<?= $comment['id'] ?>, this)" title="Нравится">
        <i class="bi bi-heart<?= $cLiked ? '-fill' : '' ?>"></i>
        <span class="clike-count"><?= $cLikes > 0 ? $cLikes : '' ?></span>
      </button>
      <?php if ((int)$comment['user_id'] === (int)$uid): ?>
      <button class="btn btn-link p-0 ms-2" style="font-size:.72rem;color:var(--danger);text-decoration:none"
              onclick="deleteComment(<?= $comment['id'] ?>)">удалить</button>
      <?php else: ?>
      <button class="btn btn-link p-0 ms-2" style="font-size:.72rem;color:var(--text-muted);text-decoration:none"
              onclick="openReport('comment', <?= $comment['id'] ?>)" title="Пожаловаться">пожаловаться</button>
      <?php endif; ?>
    </div>
    <div class="text"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
  </div>
</div>
