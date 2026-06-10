<div class="row g-3">
  <div class="col-lg-8">
    <h4 class="fw-bold mb-3">
      <i class="bi bi-bookmark-star me-2" style="color:var(--accent)"></i>Закладки
    </h4>

    <!-- Вкладки -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link <?= $tab === 'posts' ? 'active' : '' ?>" href="<?= BASE_URL ?>/bookmarks?tab=posts">
          Записи <span style="color:var(--text-muted)"><?= $postsCount ?></span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $tab === 'people' ? 'active' : '' ?>" href="<?= BASE_URL ?>/bookmarks?tab=people">
          Люди <span style="color:var(--text-muted)"><?= $peopleCount ?></span>
        </a>
      </li>
    </ul>

    <?php if ($tab === 'posts'): ?>
      <?php if (empty($posts)): ?>
      <div class="card text-center py-5" style="color:var(--text-muted)">
        <i class="bi bi-bookmark" style="font-size:2.8rem;opacity:.2;display:block;margin-bottom:12px"></i>
        <p class="mb-1">Сохранённых записей нет</p>
        <small>Сохраняйте записи кнопкой «В закладки»</small>
      </div>
      <?php else: ?>
        <?php foreach ($posts as $post): ?>
          <?php include BASE_PATH . '/app/views/feed/partials/post_card.php'; ?>
        <?php endforeach; ?>
        <?php if (count($posts) === POSTS_PER_PAGE): ?>
        <div class="text-center mt-3">
          <a href="?tab=posts&page=<?= $page + 1 ?>" class="btn btn-outline-secondary btn-sm">Показать ещё</a>
        </div>
        <?php endif; ?>
      <?php endif; ?>

    <?php else: ?>
      <?php if (empty($users)): ?>
      <div class="card text-center py-5" style="color:var(--text-muted)">
        <i class="bi bi-person-bookmark" style="font-size:2.8rem;opacity:.2;display:block;margin-bottom:12px"></i>
        <p class="mb-1">Сохранённых людей нет</p>
        <small>Добавляйте людей в закладки со страницы профиля</small>
      </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($users as $u): ?>
          <div class="col-sm-6 col-md-4">
            <div class="user-search-card h-100">
              <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($u['username']) ?>" class="text-decoration-none">
                <?php if ($u['avatar']): ?>
                <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($u['avatar']) ?>"
                     class="rounded-circle mb-2" width="72" height="72" style="object-fit:cover" alt="">
                <?php else: ?>
                <div class="avatar-placeholder mx-auto mb-2" style="width:72px;height:72px;font-size:1.8rem">
                  <?= mb_strtoupper(mb_substr($u['first_name'],0,1)) ?>
                </div>
                <?php endif; ?>
                <div class="fw-semibold" style="color:var(--text-primary)"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
                <div style="font-size:.78rem;color:var(--text-muted)">@<?= htmlspecialchars($u['username']) ?></div>
              </a>
              <button class="btn btn-sm btn-outline-secondary w-100 mt-2" onclick="removePersonBookmark(<?= $u['id'] ?>, this)">
                <i class="bi bi-bookmark-x me-1"></i>Убрать
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="col-lg-4 d-none d-lg-block">
    <div class="widget">
      <div class="card-header"><i class="bi bi-info-circle me-1"></i>О закладках</div>
      <div class="p-3" style="font-size:.85rem;color:var(--text-secondary)">
        Сохраняйте записи и людей, чтобы быстро вернуться к ним.
        Закладки видны только вам.
      </div>
    </div>
  </div>
</div>

<script>
function removePersonBookmark(id, btn) {
  postAction(BASE_URL + '/bookmark/person', { user_id: id }, () => {
    btn.closest('.col-sm-6').remove();
    showToast('Убрано из закладок', 'info');
  });
}
</script>
