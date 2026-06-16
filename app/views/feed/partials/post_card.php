<?php
$uid  = $uid  ?? $_SESSION['user_id']    ?? 0;
$csrf = $csrf ?? $_SESSION['csrf_token'] ?? '';
// Нормализация ключей — карточка устойчива к любому источнику данных
$post['liked_by_me']      = $post['liked_by_me']      ?? false;
$post['bookmarked_by_me'] = $post['bookmarked_by_me'] ?? false;
$post['comments_count']   = $post['comments_count']   ?? 0;
$post['likes_count']      = $post['likes_count']      ?? 0;
?>
<div class="post-card" id="post-<?= $post['id'] ?>">

  <!-- Header -->
  <div class="post-header">
    <div class="d-flex align-items-center gap-2">
      <?php if ($post['avatar']): ?>
      <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($post['avatar']) ?>"
           class="rounded-circle" width="42" height="42" style="object-fit:cover;border:2px solid var(--border)" alt="">
      <?php else: ?>
      <div class="avatar-placeholder" style="width:42px;height:42px;font-size:.95rem">
        <?= mb_strtoupper(mb_substr($post['first_name'],0,1)) ?>
      </div>
      <?php endif; ?>
      <div>
        <span class="fw-semibold">
          <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($post['username']) ?>"
             class="text-decoration-none" style="color:var(--text-primary)">
            <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>
          </a>
          <?php if (!empty($post['wall_owner_id']) && (int)$post['wall_owner_id'] !== (int)$post['user_id']): ?>
          <i class="bi bi-arrow-right mx-1" style="font-size:.7rem;color:var(--text-muted)"></i>
          <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($post['wall_username']) ?>"
             class="text-decoration-none" style="color:var(--text-secondary)">
            <?= htmlspecialchars($post['wall_first_name'] . ' ' . $post['wall_last_name']) ?>
          </a>
          <?php endif; ?>
        </span>
        <div class="d-flex align-items-center gap-1" style="font-size:.75rem;color:var(--text-muted)">
          <span><?= timeAgo($post['created_at']) ?></span>
          <?php if ($post['privacy'] !== 'public'): ?>
          <span>·</span>
          <i class="bi bi-<?= $post['privacy'] === 'friends' ? 'people' : 'lock' ?>"></i>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php if ((int)$post['user_id'] === (int)$uid): ?>
    <div class="dropdown">
      <button class="btn btn-sm border-0 px-2" data-bs-toggle="dropdown"
              style="background:none;color:var(--text-muted)">
        <i class="bi bi-three-dots"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <button class="dropdown-item text-danger" onclick="deletePost(<?= $post['id'] ?>)">
            <i class="bi bi-trash me-2"></i>Удалить
          </button>
        </li>
      </ul>
    </div>
    <?php else: ?>
    <div class="dropdown">
      <button class="btn btn-sm border-0 px-2" data-bs-toggle="dropdown" style="background:none;color:var(--text-muted)">
        <i class="bi bi-three-dots"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <button class="dropdown-item text-danger" onclick="openReport('post', <?= $post['id'] ?>)">
            <i class="bi bi-flag me-2"></i>Пожаловаться
          </button>
        </li>
      </ul>
    </div>
    <?php endif; ?>
  </div>

  <!-- Body -->
  <?php if ($post['content']): ?>
  <div class="post-body"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
  <?php endif; ?>

  <!-- Image -->
  <?php if ($post['image']): ?>
  <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($post['image']) ?>"
       class="post-image" onclick="openPhotoModal(this.src)" alt="">
  <?php endif; ?>

  <!-- Actions со счётчиками (как в старом ВК) -->
  <div class="post-actions">
    <button class="post-action-btn <?= $post['liked_by_me'] ? 'liked' : '' ?>"
            id="like-btn-<?= $post['id'] ?>"
            onclick="likePost(<?= $post['id'] ?>, this)">
      <i class="bi bi-<?= $post['liked_by_me'] ? 'heart-fill' : 'heart' ?>"></i>
      <span>Нравится</span>
      <span class="act-count" id="likes-count-<?= $post['id'] ?>"><?= $post['likes_count'] > 0 ? $post['likes_count'] : '' ?></span>
    </button>
    <button class="post-action-btn" onclick="toggleComments(<?= $post['id'] ?>)">
      <i class="bi bi-chat"></i>
      <span>Комментарии</span>
      <span class="act-count" id="comm-count-<?= $post['id'] ?>"><?= $post['comments_count'] > 0 ? $post['comments_count'] : '' ?></span>
    </button>
    <?php $bm = !empty($post['bookmarked_by_me']); ?>
    <button class="post-action-btn <?= $bm ? 'bookmarked' : '' ?>" onclick="toggleBookmark(<?= $post['id'] ?>, this)">
      <i class="bi bi-bookmark<?= $bm ? '-star-fill' : '' ?>"></i>
      <span><?= $bm ? 'В закладках' : 'В закладки' ?></span>
    </button>
  </div>

  <!-- Comments section -->
  <div class="d-none" id="comments-<?= $post['id'] ?>">
    <div class="comments-section">
      <div id="comments-list-<?= $post['id'] ?>">
        <div class="text-center py-2"><div class="spinner-border spinner-border-sm" style="color:var(--accent)"></div></div>
      </div>
      <div class="comment-input-row mt-2">
        <div class="avatar-placeholder" style="width:30px;height:30px;font-size:.7rem;flex-shrink:0">
          <?= mb_strtoupper(mb_substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
        </div>
        <input type="text" placeholder="Написать комментарий..."
               id="comment-input-<?= $post['id'] ?>"
               onkeydown="if(event.key==='Enter')submitComment(<?= $post['id'] ?>)">
        <button class="comment-send-btn" onclick="submitComment(<?= $post['id'] ?>)">
          <i class="bi bi-send-fill" style="font-size:.8rem"></i>
        </button>
      </div>
    </div>
  </div>

</div>
