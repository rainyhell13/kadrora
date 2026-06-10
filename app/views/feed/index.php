<div class="row g-3">

  <!-- CENTER: feed -->
  <div class="col-lg-8">

    <!-- Composer -->
    <div class="post-composer">
      <div class="d-flex gap-2">
        <?php if ($me['avatar']): ?>
        <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($me['avatar']) ?>"
             class="rounded-circle flex-shrink-0" width="40" height="40" style="object-fit:cover" alt="">
        <?php else: ?>
        <div class="avatar-placeholder flex-shrink-0" style="width:40px;height:40px;font-size:.9rem">
          <?= mb_strtoupper(mb_substr($me['first_name'],0,1)) ?>
        </div>
        <?php endif; ?>
        <textarea id="postContent" class="form-control flex-grow-1"
                  placeholder="Что у вас нового, <?= htmlspecialchars($me['first_name']) ?>?"
                  rows="2"></textarea>
      </div>

      <div id="postImagePreview" class="mt-2 d-none">
        <div class="position-relative d-inline-block">
          <img id="postImgThumb" src="" class="rounded" style="max-height:110px;border:1px solid var(--border)" alt="">
          <button class="btn btn-sm position-absolute top-0 end-0"
                  style="background:rgba(0,0,0,.6);color:#fff;border-radius:50%;padding:2px 6px;margin:3px"
                  onclick="clearPostImage()"><i class="bi bi-x"></i></button>
        </div>
      </div>

      <div class="composer-actions">
        <div class="d-flex gap-2 align-items-center">
          <label class="btn btn-outline-secondary btn-sm mb-0" for="postImageInput">
            <i class="bi bi-image me-1"></i>Фото
          </label>
          <input type="file" id="postImageInput" class="d-none" accept="image/*" onchange="previewPostImage(this)">
          <select id="postPrivacy" class="form-select form-select-sm" style="width:auto">
            <option value="public"><i class="bi bi-globe2"></i> Для всех</option>
            <option value="friends">Для друзей</option>
            <option value="private">Только я</option>
          </select>
        </div>
        <button class="btn btn-primary btn-sm px-3 fw-semibold" onclick="submitPost()">
          <i class="bi bi-send-fill me-1"></i>Опубликовать
        </button>
      </div>
    </div>

    <!-- Posts -->
    <div id="feed-posts">
      <?php foreach ($posts as $post): ?>
        <?php include BASE_PATH . '/app/views/feed/partials/post_card.php'; ?>
      <?php endforeach; ?>

      <?php if (empty($posts)): ?>
      <div class="text-center py-5" style="color:var(--text-muted)">
        <i class="bi bi-newspaper" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px"></i>
        <p class="mb-1">Лента пуста</p>
        <small>Добавьте друзей или вступите в сообщества</small>
      </div>
      <?php endif; ?>
    </div>

    <?php if (count($posts) === POSTS_PER_PAGE): ?>
    <div class="text-center mt-3">
      <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-down-circle me-1"></i>Показать ещё
      </a>
    </div>
    <?php endif; ?>

    <!-- Интересное: случайные публичные записи -->
    <?php if (!empty($discover)): ?>
    <div class="d-flex align-items-center gap-2 my-3">
      <hr class="flex-grow-1" style="border-color:var(--border)">
      <span style="font-size:.8rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">
        <i class="bi bi-stars me-1" style="color:var(--accent)"></i>Интересное
      </span>
      <hr class="flex-grow-1" style="border-color:var(--border)">
    </div>
    <?php foreach ($discover as $post): ?>
      <?php include BASE_PATH . '/app/views/feed/partials/post_card.php'; ?>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- RIGHT: suggestions -->
  <div class="col-lg-4 d-none d-lg-block">
    <?php if (!empty($suggested)): ?>
    <div class="widget sticky-top" style="top:70px">
      <div class="widget-header"><i class="bi bi-person-plus me-1"></i>Возможно знакомы</div>
      <div class="widget-body">
        <?php foreach ($suggested as $sug): ?>
        <div class="friend-item">
          <?php if ($sug['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($sug['avatar']) ?>"
               class="rounded-circle" width="38" height="38" style="object-fit:cover;flex-shrink:0" alt="">
          <?php else: ?>
          <div class="avatar-placeholder flex-shrink-0" style="width:38px;height:38px;font-size:.85rem">
            <?= mb_strtoupper(mb_substr($sug['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($sug['username']) ?>"
               class="text-decoration-none fw-semibold text-truncate d-block" style="color:var(--text-primary);font-size:.85rem">
              <?= htmlspecialchars($sug['first_name'] . ' ' . $sug['last_name']) ?>
            </a>
            <?php if ($sug['city']): ?>
            <div class="text-truncate" style="font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($sug['city']) ?></div>
            <?php endif; ?>
          </div>
          <button class="btn btn-outline-primary btn-sm px-2" style="font-size:.8rem"
                  onclick="sendFriendRequest(<?= $sug['id'] ?>, this)" title="Добавить в друзья">
            <i class="bi bi-person-plus"></i>
          </button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>

<script>
function previewPostImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('postImgThumb').src = e.target.result;
      document.getElementById('postImagePreview').classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function clearPostImage() {
  document.getElementById('postImageInput').value = '';
  document.getElementById('postImagePreview').classList.add('d-none');
}
function submitPost() {
  const content  = document.getElementById('postContent').value.trim();
  const fileInput = document.getElementById('postImageInput');
  const privacy  = document.getElementById('postPrivacy').value;
  if (!content && !fileInput.files.length) { showToast('Напишите что-нибудь', 'warning'); return; }
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('content', content);
  fd.append('privacy', privacy);
  if (fileInput.files[0]) fd.append('image', fileInput.files[0]);
  fetch(BASE_URL + '/post/create', { method:'POST', body:fd })
    .then(r => r.json()).then(data => {
      if (data.success) {
        document.getElementById('postContent').value = '';
        clearPostImage();
        document.getElementById('feed-posts').insertAdjacentHTML('afterbegin', data.html);
        showToast('Пост опубликован!', 'success');
      } else showToast(data.error||'Ошибка','danger');
    });
}
function sharePost(id) {
  const url = window.location.origin + BASE_URL.replace(window.location.origin,'') + '/feed#post-' + id;
  navigator.clipboard?.writeText(url).then(()=>showToast('Ссылка скопирована','success'));
}
</script>
