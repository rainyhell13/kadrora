<div class="row justify-content-center">
  <div class="col-lg-8">

    <h4 class="fw-bold mb-4"><i class="bi bi-gear me-2"></i>Настройки профиля</h4>

    <ul class="nav nav-tabs mb-4" id="settingsTabs">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile">
          <i class="bi bi-person me-1"></i>Профиль
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-anketa">
          <i class="bi bi-card-list me-1"></i>Анкета
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-privacy">
          <i class="bi bi-shield-lock me-1"></i>Приватность
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-password">
          <i class="bi bi-lock me-1"></i>Безопасность
        </button>
      </li>
    </ul>

    <div class="tab-content">

      <!-- Вкладка: Профиль -->
      <div class="tab-pane fade show active" id="tab-profile">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/profile/update" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

              <!-- Аватар -->
              <div class="mb-4 text-center">
                <div class="position-relative d-inline-block">
                  <?php if ($me['avatar']): ?>
                  <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($me['avatar']) ?>"
                       id="avatarPreview"
                       class="rounded-circle border" width="110" height="110" style="object-fit:cover" alt="">
                  <?php else: ?>
                  <div class="avatar-placeholder rounded-circle mx-auto" id="avatarPlaceholder"
                       style="width:110px;height:110px;font-size:2.5rem">
                    <?= mb_strtoupper(mb_substr($me['first_name'],0,1)) ?>
                  </div>
                  <?php endif; ?>
                  <label for="avatarInput"
                         class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0"
                         style="width:32px;height:32px;padding:0;line-height:32px">
                    <i class="bi bi-camera"></i>
                  </label>
                  <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*"
                         onchange="previewAvatar(this)">
                </div>
                <div class="text-muted small mt-1">Нажмите на камеру для смены аватара</div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-medium">Имя *</label>
                  <input type="text" name="first_name" class="form-control"
                         value="<?= htmlspecialchars($me['first_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Фамилия *</label>
                  <input type="text" name="last_name" class="form-control"
                         value="<?= htmlspecialchars($me['last_name']) ?>" required>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">О себе</label>
                  <textarea name="bio" class="form-control" rows="3"
                            placeholder="Расскажите о себе..."><?= htmlspecialchars($me['bio'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Город</label>
                  <input type="text" name="city" class="form-control"
                         value="<?= htmlspecialchars($me['city'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Дата рождения</label>
                  <input type="date" name="birth_date" class="form-control"
                         value="<?= htmlspecialchars($me['birth_date'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Пол</label>
                  <select name="gender" class="form-select">
                    <option value="">Не указан</option>
                    <option value="male"   <?= $me['gender']==='male'   ? 'selected':'' ?>>Мужской</option>
                    <option value="female" <?= $me['gender']==='female' ? 'selected':'' ?>>Женский</option>
                    <option value="other"  <?= $me['gender']==='other'  ? 'selected':'' ?>>Другой</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Сайт</label>
                  <input type="url" name="website" class="form-control"
                         value="<?= htmlspecialchars($me['website'] ?? '') ?>" placeholder="https://...">
                </div>

                <!-- Обложка -->
                <div class="col-12">
                  <label class="form-label fw-medium">Обложка профиля</label>
                  <?php if ($me['cover_photo']): ?>
                  <div class="mb-2">
                    <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($me['cover_photo']) ?>"
                         class="img-fluid rounded" style="max-height:120px" alt="">
                  </div>
                  <?php endif; ?>
                  <input type="file" name="cover_photo" class="form-control" accept="image/*">
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-check-lg me-1"></i>Сохранить изменения
                </button>
                <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($_SESSION['username']) ?>"
                   class="btn btn-outline-secondary ms-2">Отмена</a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Вкладка: Анкета -->
      <div class="tab-pane fade" id="tab-anketa">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/profile/anketa">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-medium">Семейное положение</label>
                  <select name="relationship" class="form-select">
                    <?php
                    $rels = [
                      ''=>'Не указано','single'=>'Не женат / не замужем','dating'=>'Встречается',
                      'engaged'=>'Помолвлен(а)','married'=>'Женат / замужем','complicated'=>'Всё сложно',
                      'in_search'=>'В активном поиске','in_love'=>'Влюблён(а)','civil'=>'В гражданском браке',
                    ];
                    foreach ($rels as $k=>$v):
                    ?>
                    <option value="<?= $k ?>" <?= ($me['relationship']??'')===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Деятельность</label>
                  <input type="text" name="activities" class="form-control"
                         value="<?= htmlspecialchars($me['activities'] ?? '') ?>" placeholder="Чем занимаетесь">
                </div>

                <div class="col-12">
                  <label class="form-label fw-medium">Интересы</label>
                  <textarea name="interests" class="form-control" rows="2"
                            placeholder="Ваши увлечения..."><?= htmlspecialchars($me['interests'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-medium">Любимая музыка</label>
                  <input type="text" name="fav_music" class="form-control"
                         value="<?= htmlspecialchars($me['fav_music'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Любимые фильмы</label>
                  <input type="text" name="fav_films" class="form-control"
                         value="<?= htmlspecialchars($me['fav_films'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Любимые книги</label>
                  <input type="text" name="fav_books" class="form-control"
                         value="<?= htmlspecialchars($me['fav_books'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Любимые игры</label>
                  <input type="text" name="fav_games" class="form-control"
                         value="<?= htmlspecialchars($me['fav_games'] ?? '') ?>">
                </div>

                <div class="col-12">
                  <label class="form-label fw-medium">Любимые цитаты</label>
                  <textarea name="fav_quotes" class="form-control" rows="2"><?= htmlspecialchars($me['fav_quotes'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-medium">Главное в жизни</label>
                  <select name="life_main" class="form-select">
                    <?php
                    $lifes = [
                      ''=>'Не указано','family'=>'Семья и дети','career'=>'Карьера и деньги',
                      'entertainment'=>'Развлечения и отдых','science'=>'Наука и исследования',
                      'improve'=>'Совершенствование мира','self'=>'Саморазвитие',
                      'beauty'=>'Красота и искусство','fame'=>'Слава и влияние',
                    ];
                    foreach ($lifes as $k=>$v):
                    ?>
                    <option value="<?= $k ?>" <?= ($me['life_main']??'')===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Главное в людях</label>
                  <select name="people_main" class="form-select">
                    <?php
                    $peoples = [
                      ''=>'Не указано','intellect'=>'Ум и креативность','kindness'=>'Доброта и честность',
                      'health'=>'Здоровье и красота','power'=>'Власть и богатство',
                      'courage'=>'Смелость и упорство','humor'=>'Юмор и жизнелюбие',
                    ];
                    foreach ($peoples as $k=>$v):
                    ?>
                    <option value="<?= $k ?>" <?= ($me['people_main']??'')===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-check-lg me-1"></i>Сохранить анкету
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Вкладка: Приватность -->
      <div class="tab-pane fade" id="tab-privacy">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <p style="color:var(--text-muted);font-size:.85rem">
              Настройте, кто может видеть вашу страницу и взаимодействовать с ней.
            </p>
            <form method="POST" action="<?= BASE_URL ?>/profile/privacy">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

              <?php
              $allFriends = ['all'=>'Все пользователи','friends'=>'Только друзья'];
              $wallOpts   = ['all'=>'Все пользователи','friends'=>'Только друзья','nobody'=>'Никто'];
              $privacyRows = [
                ['privacy_profile',  'Кто видит мою страницу',       $allFriends, $me['privacy_profile']  ?? 'all'],
                ['privacy_friends',  'Кто видит список друзей',       $allFriends, $me['privacy_friends']  ?? 'all'],
                ['privacy_photos',   'Кто видит мои фотографии',      $allFriends, $me['privacy_photos']   ?? 'all'],
                ['privacy_messages', 'Кто может писать мне сообщения', $allFriends, $me['privacy_messages'] ?? 'all'],
                ['privacy_wall',     'Кто может писать на моей стене', $wallOpts,   $me['privacy_wall']     ?? 'friends'],
              ];
              foreach ($privacyRows as [$name, $label, $opts, $cur]):
              ?>
              <div class="row align-items-center mb-3">
                <div class="col-md-7">
                  <label class="form-label fw-medium mb-0"><?= $label ?></label>
                </div>
                <div class="col-md-5">
                  <select name="<?= $name ?>" class="form-select">
                    <?php foreach ($opts as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $cur === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <?php endforeach; ?>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">
                  <i class="bi bi-check-lg me-1"></i>Сохранить настройки
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Вкладка: Пароль -->
      <div class="tab-pane fade" id="tab-password">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/profile/password">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

              <div class="mb-3">
                <label class="form-label fw-medium">Текущий пароль</label>
                <input type="password" name="old_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-medium">Новый пароль</label>
                <input type="password" name="new_password" class="form-control"
                       placeholder="Минимум 6 символов" required>
              </div>
              <div class="mb-4">
                <label class="form-label fw-medium">Повторите новый пароль</label>
                <input type="password" name="new_password2" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-warning px-4">
                <i class="bi bi-shield-lock me-1"></i>Изменить пароль
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById('avatarPreview');
      const placeholder = document.getElementById('avatarPlaceholder');
      if (img) {
        img.src = e.target.result;
      } else if (placeholder) {
        const newImg = document.createElement('img');
        newImg.id = 'avatarPreview';
        newImg.className = 'rounded-circle border';
        newImg.width = newImg.height = 110;
        newImg.style = 'object-fit:cover';
        newImg.src = e.target.result;
        placeholder.replaceWith(newImg);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
