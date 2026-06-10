<div class="row justify-content-center">
  <div class="col-lg-10">
    <h4 class="fw-bold mb-3"><i class="bi bi-search me-2" style="color:var(--accent)"></i>Поиск</h4>

    <!-- Форма поиска -->
    <form class="mb-3" action="<?= BASE_URL ?>/search" method="GET">
      <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
      <div class="input-group input-group-lg">
        <span class="input-group-text"><i class="bi bi-search text-muted"></i></span>
        <input type="search" name="q" class="form-control"
               placeholder="Люди, сообщества, записи..."
               value="<?= htmlspecialchars($query) ?>" autofocus>
        <button class="btn btn-primary px-4" type="submit">Найти</button>
      </div>
    </form>

    <?php if ($query !== ''): ?>
    <!-- Вкладки -->
    <ul class="nav nav-tabs mb-3">
      <?php
      $tabs = [
        'people' => ['Люди', count($users)],
        'groups' => ['Сообщества', count($groups)],
        'posts'  => ['Записи', count($posts)],
      ];
      foreach ($tabs as $key => [$label, $cnt]):
      ?>
      <li class="nav-item">
        <a class="nav-link <?= $tab === $key ? 'active' : '' ?>"
           href="<?= BASE_URL ?>/search?q=<?= urlencode($query) ?>&tab=<?= $key ?>">
          <?= $label ?> <span style="color:var(--text-muted)"><?= $cnt ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <?php if ($tab === 'people'): ?>
      <?php if (empty($users)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
          <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
          <p>Пользователи не найдены</p>
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($users as $user): ?>
          <div class="col-sm-6 col-md-4">
            <div class="user-search-card h-100">
              <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($user['username']) ?>" class="text-decoration-none">
                <?php if ($user['avatar']): ?>
                <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>"
                     class="rounded-circle mb-2" width="72" height="72" style="object-fit:cover" alt="">
                <?php else: ?>
                <div class="avatar-placeholder mx-auto mb-2" style="width:72px;height:72px;font-size:1.8rem">
                  <?= mb_strtoupper(mb_substr($user['first_name'],0,1)) ?>
                </div>
                <?php endif; ?>
                <div class="fw-semibold" style="color:var(--text-primary)"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
                <div style="font-size:.78rem;color:var(--text-muted)">@<?= htmlspecialchars($user['username']) ?></div>
                <?php if ($user['city']): ?>
                <div style="font-size:.78rem;color:var(--text-muted)"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($user['city']) ?></div>
                <?php endif; ?>
              </a>
              <div class="mt-2">
                <?php if ($user['is_friend']): ?>
                <span class="btn btn-sm btn-success disabled w-100"><i class="bi bi-person-check me-1"></i>Друг</span>
                <?php else: ?>
                <button class="btn btn-sm btn-outline-primary w-100" onclick="sendFriendRequest(<?= $user['id'] ?>, this)">
                  <i class="bi bi-person-plus me-1"></i>Добавить
                </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($tab === 'groups'): ?>
      <?php if (empty($groups)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
          <i class="bi bi-collection fs-1 d-block mb-2 opacity-50"></i>
          <p>Сообщества не найдены</p>
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($groups as $g): ?>
          <div class="col-sm-6 col-md-4">
            <a href="<?= BASE_URL ?>/groups/<?= htmlspecialchars($g['slug']) ?>"
               class="d-block text-decoration-none user-search-card h-100">
              <?php if ($g['avatar']): ?>
              <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($g['avatar']) ?>"
                   class="rounded-circle mb-2" width="72" height="72" style="object-fit:cover" alt="">
              <?php else: ?>
              <div class="avatar-placeholder mx-auto mb-2" style="width:72px;height:72px;font-size:1.8rem">
                <?= mb_strtoupper(mb_substr($g['name'],0,1)) ?>
              </div>
              <?php endif; ?>
              <div class="fw-semibold" style="color:var(--text-primary)"><?= htmlspecialchars($g['name']) ?></div>
              <div style="font-size:.78rem;color:var(--text-muted)">
                <i class="bi bi-people me-1"></i><?= $g['members_count'] ?> участников
              </div>
              <?php if ($g['is_member']): ?>
              <div class="mt-1"><span class="badge bg-success">Вы участник</span></div>
              <?php endif; ?>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <?php if (empty($posts)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
          <i class="bi bi-file-text fs-1 d-block mb-2 opacity-50"></i>
          <p>Записи не найдены</p>
        </div>
      <?php else: ?>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <?php foreach ($posts as $post): ?>
              <?php include BASE_PATH . '/app/views/feed/partials/post_card.php'; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center py-5" style="color:var(--text-muted)">
      <i class="bi bi-search fs-1 d-block mb-2 opacity-25"></i>
      <p>Введите запрос для поиска людей, сообществ и записей</p>
    </div>
    <?php endif; ?>
  </div>
</div>
