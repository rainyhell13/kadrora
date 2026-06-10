<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">
      Фотографии — <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($profile['username']) ?>"
                       class="text-decoration-none"><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></a>
    </h4>
    <small class="text-muted"><?= count($photos) ?> фото</small>
  </div>
</div>

<div class="row g-2">
  <?php foreach ($photos as $photo): ?>
  <div class="col-6 col-sm-4 col-md-3 col-lg-2" id="photo-wrap-<?= $photo['id'] ?>">
    <div class="position-relative overflow-hidden rounded photo-grid-item">
      <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($photo['filename']) ?>"
           class="w-100 cursor-pointer"
           style="height:150px;object-fit:cover"
           onclick="openPhotoModal(this.src)" alt="">
      <?php if ($isOwner): ?>
      <button class="btn btn-sm btn-danger photo-delete-btn position-absolute top-0 end-0 m-1"
              onclick="deletePhoto(<?= $photo['id'] ?>)">
        <i class="bi bi-trash"></i>
      </button>
      <?php endif; ?>
      <?php if ($photo['caption']): ?>
      <div class="photo-caption position-absolute bottom-0 start-0 end-0 p-1 bg-dark bg-opacity-50 text-white small text-truncate">
        <?= htmlspecialchars($photo['caption']) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($photos)): ?>
  <div class="col-12 text-center py-5 text-muted">
    <i class="bi bi-image fs-1 d-block mb-2 opacity-50"></i>
    <p>Фотографий пока нет</p>
  </div>
  <?php endif; ?>
</div>

<?php if (count($photos) === USERS_PER_PAGE): ?>
<div class="text-center mt-4">
  <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-primary">Показать ещё</a>
</div>
<?php endif; ?>

<script>
function deletePhoto(id) {
  if (!confirm('Удалить фото?')) return;
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('photo_id', id);
  fetch(BASE_URL + '/photo/delete', { method:'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.getElementById('photo-wrap-' + id)?.remove();
        showToast('Фото удалено', 'success');
      }
    });
}
</script>
