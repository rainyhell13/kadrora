<div class="row g-3">
  <div class="col-lg-8">

    <!-- Group header -->
    <div style="border-radius:var(--radius);overflow:hidden;margin-bottom:16px;border:1px solid var(--border)">
      <div class="group-profile-cover"
           style="<?= $group['cover'] ? 'background:url('.BASE_URL.'/uploads/photos/'.htmlspecialchars($group['cover']).') center/cover' : '' ?>">
      </div>
      <div style="background:var(--bg-card);padding:0 20px 18px">
        <div class="d-flex align-items-end justify-content-between" style="margin-top:-36px">
          <div>
            <?php if ($group['avatar']): ?>
            <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($group['avatar']) ?>"
                 class="rounded-circle" width="90" height="90"
                 style="object-fit:cover;border:4px solid var(--bg-base)" alt="">
            <?php else: ?>
            <div class="avatar-placeholder" style="width:90px;height:90px;font-size:2.2rem;border:4px solid var(--bg-base)">
              <?= mb_strtoupper(mb_substr($group['name'],0,1)) ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-2 pb-1">
            <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>/groups/<?= htmlspecialchars($group['slug']) ?>/edit"
               class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-pencil me-1"></i>Управление
            </a>
            <?php elseif ($isMember): ?>
            <button class="btn btn-outline-secondary btn-sm"
                    onclick="leaveGroupPage(<?= $group['id'] ?>)">
              <i class="bi bi-box-arrow-right me-1"></i>Покинуть
            </button>
            <?php elseif ($hasRequest): ?>
            <button class="btn btn-outline-secondary btn-sm"
                    onclick="cancelGroupRequest(<?= $group['id'] ?>)">
              <i class="bi bi-clock me-1"></i>Заявка отправлена
            </button>
            <?php elseif ($isPrivate): ?>
            <button class="btn btn-primary btn-sm"
                    onclick="joinGroupPage(<?= $group['id'] ?>, this)">
              <i class="bi bi-key me-1"></i>Подать заявку
            </button>
            <?php else: ?>
            <button class="btn btn-primary btn-sm"
                    onclick="joinGroupPage(<?= $group['id'] ?>, this)">
              <i class="bi bi-plus-circle me-1"></i>Вступить
            </button>
            <?php endif; ?>
            <?php if ((int)$group['owner_id'] !== (int)$me['id']): ?>
            <button class="btn btn-outline-secondary btn-sm" title="Пожаловаться"
                    onclick="openReport('group', <?= $group['id'] ?>)">
              <i class="bi bi-flag"></i>
            </button>
            <?php endif; ?>
          </div>
        </div>
        <h3 class="fw-bold mt-2 mb-0" style="color:var(--text-primary)">
          <?= htmlspecialchars($group['name']) ?>
        </h3>
        <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:6px">
          <i class="bi bi-people me-1"></i><?= $group['members_count'] ?> участников
          · <i class="bi bi-<?= $group['privacy']==='public'?'globe2':'lock' ?> me-1"></i>
          <?= $group['privacy']==='public' ? 'Открытое' : 'Закрытое' ?>
          · <?= $group['posts_count'] ?> записей
        </div>
        <?php if ($group['description']): ?>
        <p style="color:var(--text-secondary);font-size:.9rem;margin:0;max-width:600px">
          <?= nl2br(htmlspecialchars($group['description'])) ?>
        </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Composer (only members) -->
    <?php if ($isMember): ?>
    <div class="post-composer mb-3">
      <div class="d-flex gap-2">
        <?php if ($me['avatar']): ?>
        <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($me['avatar']) ?>"
             class="rounded-circle flex-shrink-0" width="38" height="38" style="object-fit:cover" alt="">
        <?php else: ?>
        <div class="avatar-placeholder flex-shrink-0" style="width:38px;height:38px;font-size:.85rem">
          <?= mb_strtoupper(mb_substr($me['first_name'],0,1)) ?>
        </div>
        <?php endif; ?>
        <textarea id="gpostContent" class="form-control" placeholder="Написать запись в сообществе..." rows="2"></textarea>
      </div>
      <div class="composer-actions">
        <label class="btn btn-outline-secondary btn-sm mb-0" for="gpostImage">
          <i class="bi bi-image me-1"></i>Фото
        </label>
        <input type="file" id="gpostImage" class="d-none" accept="image/*">
        <button class="btn btn-primary btn-sm px-3 fw-semibold" onclick="submitGroupPost(<?= $group['id'] ?>)">
          <i class="bi bi-send-fill me-1"></i>Опубликовать
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Заявки на вступление (для администрации) -->
    <?php if ($isAdmin && !empty($requests)): ?>
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-person-plus me-1" style="color:var(--warning)"></i>Заявки на вступление <span class="badge bg-warning text-dark"><?= count($requests) ?></span></div>
      <div class="widget-body">
        <?php foreach ($requests as $r): ?>
        <div class="friend-item" id="greq-<?= $r['user_id'] ?>">
          <?php if ($r['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($r['avatar']) ?>"
               class="rounded-circle" width="40" height="40" style="object-fit:cover;flex-shrink:0" alt="">
          <?php else: ?>
          <div class="avatar-placeholder flex-shrink-0" style="width:40px;height:40px;font-size:.9rem">
            <?= mb_strtoupper(mb_substr($r['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($r['username']) ?>"
             class="flex-grow-1 text-decoration-none fw-medium" style="color:var(--text-primary);font-size:.875rem">
            <?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?>
          </a>
          <button class="btn btn-success btn-sm" onclick="acceptGroupReq(<?= $group['id'] ?>,<?= $r['user_id'] ?>)">
            <i class="bi bi-check-lg"></i>
          </button>
          <button class="btn btn-outline-secondary btn-sm" onclick="declineGroupReq(<?= $group['id'] ?>,<?= $r['user_id'] ?>)">
            <i class="bi bi-x"></i>
          </button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!$canView): ?>
    <!-- Закрытое сообщество -->
    <div class="card text-center py-5" style="color:var(--text-muted)">
      <i class="bi bi-lock" style="font-size:2.8rem;opacity:.3;display:block;margin-bottom:12px"></i>
      <p class="mb-1 fw-semibold" style="color:var(--text-secondary)">Это закрытое сообщество</p>
      <small>Записи доступны только участникам. Подайте заявку, чтобы вступить.</small>
    </div>
    <?php else: ?>

    <!-- Posts -->
    <div id="group-posts">
      <?php foreach ($posts as $post): ?>
        <?php include BASE_PATH . '/app/views/groups/partials/group_post_card.php'; ?>
      <?php endforeach; ?>

      <?php if (empty($posts)): ?>
      <div class="card text-center py-5" style="color:var(--text-muted)">
        <i class="bi bi-pencil-square" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:10px"></i>
        <p class="mb-0">Записей пока нет</p>
      </div>
      <?php endif; ?>
    </div>

    <?php if (count($posts) === POSTS_PER_PAGE): ?>
    <div class="text-center mt-3">
      <a href="?page=<?= $page+1 ?>" class="btn btn-outline-secondary btn-sm">Показать ещё</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <div class="widget sticky-top" style="top:70px">
      <div class="card-header"><i class="bi bi-people me-1"></i>Участники <span style="color:var(--text-muted)"><?= $group['members_count'] ?></span></div>
      <div class="widget-body">
        <?php foreach ($members as $m): ?>
        <div class="friend-item">
          <?php if ($m['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($m['avatar']) ?>"
               class="rounded-circle" width="36" height="36" style="object-fit:cover;flex-shrink:0" alt="">
          <?php else: ?>
          <div class="avatar-placeholder flex-shrink-0" style="width:36px;height:36px;font-size:.8rem">
            <?= mb_strtoupper(mb_substr($m['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($m['username']) ?>"
               class="text-decoration-none fw-medium text-truncate d-block" style="color:var(--text-primary);font-size:.85rem">
              <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
            </a>
            <?php if ($m['role'] !== 'member'): ?>
            <span class="badge bg-primary" style="font-size:.65rem">
              <?= $m['role'] === 'admin' ? 'Администратор' : 'Модератор' ?>
            </span>
            <?php endif; ?>
          </div>
          <?php if ($m['is_online'] ?? false): ?>
          <span class="online-dot"></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script>
function joinGroupPage(id, btn) {
  postAction(BASE_URL+'/groups/join',{group_id:id},res=>{
    if (res.status === 'requested') showToast('Заявка отправлена администратору', 'success');
    setTimeout(()=>location.reload(), 600);
  });
}
function cancelGroupRequest(id) {
  postAction(BASE_URL+'/groups/request/cancel',{group_id:id},()=>location.reload());
}
function acceptGroupReq(gid, uid) {
  postAction(BASE_URL+'/groups/request/accept',{group_id:gid,user_id:uid},()=>{
    document.getElementById('greq-'+uid)?.remove();
    showToast('Заявка принята','success');
  });
}
function declineGroupReq(gid, uid) {
  postAction(BASE_URL+'/groups/request/decline',{group_id:gid,user_id:uid},()=>{
    document.getElementById('greq-'+uid)?.remove();
    showToast('Заявка отклонена','info');
  });
}
function leaveGroupPage(id) {
  if(!confirm('Покинуть сообщество?')) return;
  postAction(BASE_URL+'/groups/leave',{group_id:id},()=>location.reload());
}
function submitGroupPost(groupId) {
  const content = document.getElementById('gpostContent').value.trim();
  const fileInp = document.getElementById('gpostImage');
  if (!content && !fileInp.files.length) { showToast('Напишите что-нибудь', 'warning'); return; }
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('group_id', groupId);
  fd.append('content', content);
  if (fileInp.files[0]) fd.append('image', fileInp.files[0]);
  fetch(BASE_URL+'/groups/post/create',{method:'POST',body:fd})
    .then(r=>r.json()).then(data=>{
      if (data.success) {
        document.getElementById('gpostContent').value = '';
        document.getElementById('group-posts').insertAdjacentHTML('afterbegin', data.html);
        showToast('Запись опубликована!','success');
      } else showToast(data.error||'Ошибка','danger');
    });
}
function deleteGroupPost(id) {
  if(!confirm('Удалить запись?')) return;
  postAction(BASE_URL+'/groups/post/delete',{post_id:id},()=>{
    document.getElementById('gpost-'+id)?.remove();
    showToast('Запись удалена','info');
  });
}
function likeGroupPost(postId, btn) {
  postAction(BASE_URL+'/groups/post/like',{post_id:postId},res=>{
    btn.classList.toggle('liked', res.liked);
    btn.innerHTML = `<i class="bi bi-${res.liked?'heart-fill':'heart'}"></i> Нравится`;
    const el = document.getElementById('glikes-count-'+postId);
    if (el) el.innerHTML = res.count > 0
      ? `<i class="bi bi-heart-fill me-1" style="color:var(--danger)"></i>${res.count}` : '';
  });
}
</script>
