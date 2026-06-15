<?php
$fs       = $friendStatus;
$isFriend = $fs && $fs['status'] === 'accepted';
$isPending= $fs && $fs['status'] === 'pending';
$iSent    = $isPending && (int)$fs['requester'] === (int)$me['id'];
$theyAsked= $isPending && (int)$fs['addressee'] === (int)$me['id'];
?>

<!-- Cover + Info -->
<div style="border-radius:var(--radius);overflow:hidden;margin-bottom:16px;border:1px solid var(--border)">
  <!-- Cover -->
  <div class="profile-cover"
       style="<?= $profile['cover_photo'] ? 'background:url('.BASE_URL.'/uploads/photos/'.htmlspecialchars($profile['cover_photo']).') center/cover no-repeat' : '' ?>">
  </div>

  <!-- Info bar -->
  <div class="profile-info-card">
    <div class="d-flex flex-column flex-md-row gap-3 align-items-start">

      <div class="profile-avatar-wrap">
        <?php if ($profile['avatar']): ?>
        <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($profile['avatar']) ?>"
             class="rounded-circle" width="110" height="110"
             style="object-fit:cover;border:4px solid var(--bg-base)" alt="">
        <?php else: ?>
        <div class="avatar-placeholder" style="width:110px;height:110px;font-size:2.8rem;border:4px solid var(--bg-base)">
          <?= mb_strtoupper(mb_substr($profile['first_name'],0,1)) ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="flex-grow-1 pt-2">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
          <div>
            <h2 class="profile-name mb-0">
              <?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?>
              <?php if ($profile['is_online']): ?>
              <span class="badge bg-success ms-1" style="font-size:.6rem;vertical-align:middle">онлайн</span>
              <?php endif; ?>
            </h2>
            <div class="profile-username">@<?= htmlspecialchars($profile['username']) ?></div>

            <!-- Статус -->
            <?php if ($isOwner): ?>
            <div class="profile-status-edit mt-1" id="statusBox">
              <span id="statusText" style="font-size:.85rem;color:<?= $profile['status'] ? 'var(--text-secondary)' : 'var(--text-muted)' ?>;cursor:pointer"
                    onclick="editStatus()">
                <i class="bi bi-pencil-square me-1" style="font-size:.75rem"></i><?= $profile['status'] ? htmlspecialchars($profile['status']) : 'Установить статус...' ?>
              </span>
              <div id="statusEdit" class="d-none mt-1">
                <div class="input-group input-group-sm" style="max-width:360px">
                  <input type="text" id="statusInput" class="form-control" maxlength="255"
                         value="<?= htmlspecialchars($profile['status'] ?? '') ?>" placeholder="Что у вас нового?">
                  <button class="btn btn-primary" onclick="saveStatus()"><i class="bi bi-check-lg"></i></button>
                  <button class="btn btn-outline-secondary" onclick="cancelStatus()"><i class="bi bi-x"></i></button>
                </div>
              </div>
            </div>
            <?php elseif ($profile['status']): ?>
            <div class="mt-1" style="font-size:.85rem;color:var(--text-secondary);font-style:italic">
              «<?= htmlspecialchars($profile['status']) ?>»
            </div>
            <?php endif; ?>

            <?php if ($profile['bio']): ?>
            <p class="mt-2 mb-0" style="color:var(--text-secondary);font-size:.9rem;max-width:480px">
              <?= nl2br(htmlspecialchars($profile['bio'])) ?>
            </p>
            <?php endif; ?>

            <?php
            // Краткая информация
            $months = [1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',
                       7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'];
            $bdate = '';
            if (!empty($profile['birth_date'])) {
                $ts = strtotime($profile['birth_date']);
                $bdate = (int)date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts) . ' г.';
            }
            $brief = array_filter([
                ['День рождения', $bdate],
                ['Город',         $profile['city'] ?? ''],
                ['Место работы',  $profile['activities'] ?? ''],
                ['Веб-сайт',      $profile['website'] ?? ''],
            ], fn($r) => $r[1] !== '');
            ?>
            <?php if (!empty($brief)): ?>
            <table class="profile-info-table mt-3" style="max-width:480px">
              <?php foreach ($brief as [$lbl, $val]): ?>
              <tr>
                <td class="label"><?= $lbl ?>:</td>
                <td class="value">
                  <?php if ($lbl === 'Веб-сайт'): ?>
                  <a href="<?= htmlspecialchars($val) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($val) ?></a>
                  <?php else: ?>
                  <?= htmlspecialchars($val) ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php
            // Полная анкета (раскрывается)
            $relMap = ['single'=>'Не женат / не замужем','dating'=>'Встречается','engaged'=>'Помолвлен(а)',
              'married'=>'Женат / замужем','complicated'=>'Всё сложно','in_search'=>'В активном поиске',
              'in_love'=>'Влюблён(а)','civil'=>'В гражданском браке'];
            $lifeMap = ['family'=>'Семья и дети','career'=>'Карьера и деньги','entertainment'=>'Развлечения и отдых',
              'science'=>'Наука и исследования','improve'=>'Совершенствование мира','self'=>'Саморазвитие',
              'beauty'=>'Красота и искусство','fame'=>'Слава и влияние'];
            $peopleMap = ['intellect'=>'Ум и креативность','kindness'=>'Доброта и честность','health'=>'Здоровье и красота',
              'power'=>'Власть и богатство','courage'=>'Смелость и упорство','humor'=>'Юмор и жизнелюбие'];
            $detailed = array_filter([
                ['Семейное положение', $relMap[$profile['relationship']] ?? ''],
                ['Интересы',           $profile['interests'] ?? ''],
                ['Любимая музыка',     $profile['fav_music'] ?? ''],
                ['Любимые фильмы',     $profile['fav_films'] ?? ''],
                ['Любимые книги',      $profile['fav_books'] ?? ''],
                ['Любимые игры',       $profile['fav_games'] ?? ''],
                ['Любимые цитаты',     $profile['fav_quotes'] ?? ''],
                ['Главное в жизни',    $lifeMap[$profile['life_main']] ?? ''],
                ['Главное в людях',    $peopleMap[$profile['people_main']] ?? ''],
            ], fn($r) => $r[1] !== '');
            ?>
            <?php if (!empty($detailed)): ?>
            <table class="profile-info-table profile-detailed mt-1" id="profileDetailed" style="max-width:480px">
              <?php foreach ($detailed as [$lbl, $val]): ?>
              <tr>
                <td class="label"><?= $lbl ?>:</td>
                <td class="value"><?= nl2br(htmlspecialchars($val)) ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
            <a href="#" class="d-inline-block mt-2" style="font-size:.82rem" id="detailToggle"
               onclick="event.preventDefault();toggleDetailed()">Показать подробную информацию »</a>
            <?php endif; ?>
          </div>

          <!-- Action buttons -->
          <div class="d-flex flex-wrap gap-2" id="friend-actions">
            <?php if ($isOwner): ?>
            <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-pencil me-1"></i>Редактировать
            </a>
            <?php elseif ($isFriend): ?>
            <span class="btn btn-success btn-sm disabled"><i class="bi bi-person-check me-1"></i>В друзьях</span>
            <button class="btn btn-outline-danger btn-sm" onclick="removeFriend(<?= $profile['id'] ?>)">
              <i class="bi bi-person-x"></i>
            </button>
            <a href="<?= BASE_URL ?>/messages/<?= $profile['id'] ?>" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-chat me-1"></i>Написать
            </a>
            <?php elseif ($iSent): ?>
            <span class="btn btn-outline-secondary btn-sm disabled"><i class="bi bi-clock me-1"></i>Заявка отправлена</span>
            <?php elseif ($theyAsked): ?>
            <button class="btn btn-success btn-sm" onclick="acceptFriend(<?= $profile['id'] ?>)">
              <i class="bi bi-check-lg me-1"></i>Принять
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="declineFriend(<?= $profile['id'] ?>)">Отклонить</button>
            <?php else: ?>
            <button class="btn btn-primary btn-sm" onclick="sendFriendRequest(<?= $profile['id'] ?>, this)">
              <i class="bi bi-person-plus me-1"></i>Добавить в друзья
            </button>
            <a href="<?= BASE_URL ?>/messages/<?= $profile['id'] ?>" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-chat me-1"></i>Написать
            </a>
            <?php endif; ?>

            <?php if (!$isOwner): ?>
            <button class="btn btn-outline-secondary btn-sm <?= $isBookmarked ? 'text-accent' : '' ?>"
                    id="person-bm-btn" title="Сохранить в закладки"
                    onclick="togglePersonBookmark(<?= $profile['id'] ?>, this)">
              <i class="bi bi-bookmark<?= $isBookmarked ? '-star-fill' : '' ?>"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm" title="Пожаловаться"
                    onclick="openReport('user', <?= $profile['id'] ?>)">
              <i class="bi bi-flag"></i>
            </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Stats -->
        <div class="d-flex gap-4 mt-3">
          <div>
            <span class="profile-stat-val"><?= $stats['posts'] ?></span>
            <span class="profile-stat-lbl ms-1">постов</span>
          </div>
          <div>
            <span class="profile-stat-val"><?= $stats['friends'] ?></span>
            <span class="profile-stat-lbl ms-1">друзей</span>
          </div>
          <div>
            <span class="profile-stat-val"><?= $stats['photos'] ?></span>
            <span class="profile-stat-lbl ms-1">фото</span>
          </div>
          <?php if ($mutualCount > 0): ?>
          <div>
            <span class="profile-stat-val"><?= $mutualCount ?></span>
            <span class="profile-stat-lbl ms-1">общих</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Content -->
<?php if (!$canViewProfile): ?>
<div class="card text-center py-5" style="color:var(--text-muted)">
  <i class="bi bi-shield-lock" style="font-size:2.8rem;opacity:.3;display:block;margin-bottom:12px"></i>
  <p class="mb-1 fw-semibold" style="color:var(--text-secondary)">Это закрытый профиль</p>
  <small>Пользователь ограничил доступ к своей странице. Добавьте его в друзья, чтобы видеть записи.</small>
</div>
<?php else: ?>
<div class="row g-3">
  <!-- Posts / Wall -->
  <div class="col-lg-8">

    <!-- Форма записи на стену -->
    <?php if ($canPostToWall): ?>
    <div class="post-composer mb-3">
      <div class="d-flex gap-2">
        <?php if ($me['avatar']): ?>
        <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($me['avatar']) ?>"
             class="rounded-circle flex-shrink-0" width="40" height="40" style="object-fit:cover" alt="">
        <?php else: ?>
        <div class="avatar-placeholder flex-shrink-0" style="width:40px;height:40px;font-size:.9rem">
          <?= mb_strtoupper(mb_substr($me['first_name'],0,1)) ?>
        </div>
        <?php endif; ?>
        <textarea id="wallContent" class="form-control"
                  placeholder="<?= $isOwner ? 'Что у вас нового?' : 'Написать на стене...' ?>" rows="2"></textarea>
      </div>
      <div id="wallImagePreview" class="mt-2 d-none">
        <div class="position-relative d-inline-block">
          <img id="wallImgThumb" src="" class="rounded" style="max-height:110px;border:1px solid var(--border)" alt="">
          <button class="btn btn-sm position-absolute top-0 end-0" style="background:rgba(0,0,0,.6);color:#fff;border-radius:50%;padding:2px 6px;margin:3px"
                  onclick="clearWallImage()"><i class="bi bi-x"></i></button>
        </div>
      </div>
      <div class="composer-actions">
        <label class="btn btn-outline-secondary btn-sm mb-0" for="wallImageInput">
          <i class="bi bi-image me-1"></i>Фото
        </label>
        <input type="file" id="wallImageInput" class="d-none" accept="image/*" onchange="previewWallImage(this)">
        <button class="btn btn-primary btn-sm px-3 fw-semibold" onclick="submitWallPost()">
          <i class="bi bi-send-fill me-1"></i>Отправить
        </button>
      </div>
    </div>
    <?php endif; ?>

    <div id="wall-posts">
    <?php foreach ($posts as $post): ?>
      <?php include BASE_PATH . '/app/views/feed/partials/post_card.php'; ?>
    <?php endforeach; ?>
    </div>

    <?php if (empty($posts)): ?>
    <div class="card text-center py-5" style="color:var(--text-muted)">
      <i class="bi bi-file-text" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:10px"></i>
      <p class="mb-0">На стене пока нет записей</p>
    </div>
    <?php endif; ?>

    <?php if (count($posts) === POSTS_PER_PAGE): ?>
    <div class="text-center mt-3">
      <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary btn-sm">Показать ещё</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">

    <!-- Разделы (аудио/видео/фото) -->
    <div class="widget mb-3">
      <div class="widget-body">
        <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($profile['username']) ?>/photos"
           class="friend-item text-decoration-none" style="color:var(--text-secondary)">
          <i class="bi bi-images" style="font-size:1.1rem;width:22px;color:var(--accent)"></i>
          <span style="font-size:.875rem;flex-grow:1">Фотографии</span>
          <span class="badge bg-secondary"><?= $stats['photos'] ?></span>
        </a>
        <a href="<?= BASE_URL ?>/video/<?= htmlspecialchars($profile['username']) ?>"
           class="friend-item text-decoration-none" style="color:var(--text-secondary)">
          <i class="bi bi-camera-video" style="font-size:1.1rem;width:22px;color:var(--accent)"></i>
          <span style="font-size:.875rem;flex-grow:1">Видеозаписи</span>
          <span class="badge bg-secondary"><?= $videoCount ?></span>
        </a>
      </div>
    </div>

    <?php if (!empty($photos)): ?>
    <div class="widget mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-images me-1"></i>Фотографии</span>
        <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($profile['username']) ?>/photos"
           style="font-size:.78rem;color:var(--accent)">Все</a>
      </div>
      <div class="p-2">
        <div class="row g-1">
          <?php foreach (array_slice($photos, 0, 9) as $photo): ?>
          <div class="col-4">
            <div class="photo-grid-item">
              <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($photo['filename']) ?>"
                   onclick="openPhotoModal(this.src)" alt="">
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($friends)): ?>
    <div class="widget">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-1"></i>Друзья <span style="color:var(--text-muted)"><?= $stats['friends'] ?></span></span>
        <a href="<?= BASE_URL ?>/friends" style="font-size:.78rem;color:var(--accent)">Все</a>
      </div>
      <div class="p-2">
        <div class="row g-1">
          <?php foreach ($friends as $f): ?>
          <div class="col-4 text-center">
            <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($f['username']) ?>" class="text-decoration-none">
              <?php if ($f['avatar']): ?>
              <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($f['avatar']) ?>"
                   class="rounded-circle mb-1" width="52" height="52" style="object-fit:cover" alt="">
              <?php else: ?>
              <div class="avatar-placeholder mx-auto mb-1" style="width:52px;height:52px;font-size:1.1rem">
                <?= mb_strtoupper(mb_substr($f['first_name'],0,1)) ?>
              </div>
              <?php endif; ?>
              <div style="font-size:.72rem;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                <?= htmlspecialchars($f['first_name']) ?>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<script>
function acceptFriend(id) { postAction(BASE_URL+'/friend/accept',{user_id:id},()=>location.reload()); }
function declineFriend(id) { postAction(BASE_URL+'/friend/decline',{user_id:id},()=>location.reload()); }
function removeFriend(id) {
  if(!confirm('Удалить из друзей?')) return;
  postAction(BASE_URL+'/friend/remove',{user_id:id},()=>location.reload());
}
function toggleDetailed() {
  const d = document.getElementById('profileDetailed');
  const t = document.getElementById('detailToggle');
  const show = !d.classList.contains('show');
  d.classList.toggle('show', show);
  t.textContent = show ? 'Скрыть подробную информацию «' : 'Показать подробную информацию »';
}
function togglePersonBookmark(id, btn) {
  postAction(BASE_URL + '/bookmark/person', { user_id: id }, res => {
    btn.classList.toggle('text-accent', res.bookmarked);
    btn.innerHTML = '<i class="bi bi-bookmark' + (res.bookmarked ? '-star-fill' : '') + '"></i>';
    showToast(res.bookmarked ? 'Добавлено в закладки' : 'Убрано из закладок', res.bookmarked ? 'success' : 'info');
  });
}

/* ---- Статус ---- */
function editStatus() {
  document.getElementById('statusText').classList.add('d-none');
  document.getElementById('statusEdit').classList.remove('d-none');
  document.getElementById('statusInput').focus();
}
function cancelStatus() {
  document.getElementById('statusEdit').classList.add('d-none');
  document.getElementById('statusText').classList.remove('d-none');
}
function saveStatus() {
  const val = document.getElementById('statusInput').value.trim();
  postAction(BASE_URL+'/profile/status', {status: val}, res => {
    const t = document.getElementById('statusText');
    t.innerHTML = '<i class="bi bi-pencil-square me-1" style="font-size:.75rem"></i>' +
      (res.status ? res.status : 'Установить статус...');
    t.style.color = res.status ? 'var(--text-secondary)' : 'var(--text-muted)';
    cancelStatus();
    showToast('Статус обновлён', 'success');
  });
}

/* ---- Запись на стену ---- */
<?php if ($canPostToWall): ?>
const WALL_OWNER_ID = <?= $profile['id'] ?>;
function previewWallImage(input) {
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => {
      document.getElementById('wallImgThumb').src = e.target.result;
      document.getElementById('wallImagePreview').classList.remove('d-none');
    };
    r.readAsDataURL(input.files[0]);
  }
}
function clearWallImage() {
  document.getElementById('wallImageInput').value = '';
  document.getElementById('wallImagePreview').classList.add('d-none');
}
function submitWallPost() {
  const content = document.getElementById('wallContent').value.trim();
  const fileInp = document.getElementById('wallImageInput');
  if (!content && !fileInp.files.length) { showToast('Введите текст записи', 'warning'); return; }
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('owner_id', WALL_OWNER_ID);
  fd.append('content', content);
  if (fileInp.files[0]) fd.append('image', fileInp.files[0]);
  fetch(BASE_URL+'/wall/post', {method:'POST', body:fd})
    .then(r=>r.json()).then(data=>{
      if (data.success) {
        document.getElementById('wallContent').value = '';
        clearWallImage();
        document.getElementById('wall-posts').insertAdjacentHTML('afterbegin', data.html);
        showToast('Запись добавлена', 'success');
      } else showToast(data.error||'Ошибка','danger');
    });
}
<?php endif; ?>
</script>
