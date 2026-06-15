<?php include BASE_PATH . '/app/views/admin/_nav.php'; ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-plus-circle me-1"></i>Добавить стоп-слово</div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/words/add">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="mb-2">
            <label class="form-label">Слово или фраза</label>
            <input type="text" name="word" class="form-control" placeholder="например: казино" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Действие</label>
            <select name="action" class="form-select">
              <option value="block">Блокировать публикацию</option>
              <option value="flag">Помечать на проверку</option>
            </select>
          </div>
          <button class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Добавить</button>
        </form>
        <p class="mt-3 mb-0" style="font-size:.8rem;color:var(--text-muted)">
          «Блокировать» — запись с этим словом не публикуется. «Помечать» — публикуется, но попадает в раздел «Контент» на проверку модератору.
        </p>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between"><span><i class="bi bi-slash-circle me-1"></i>Список стоп-слов</span><span style="color:var(--text-muted)"><?= count($words) ?></span></div>
      <div class="card-body p-0">
        <?php if (empty($words)): ?>
        <div class="text-center py-4" style="color:var(--text-muted)">Список пуст</div>
        <?php else: ?>
        <?php foreach ($words as $w): ?>
        <div class="d-flex align-items-center justify-content-between p-2 px-3" style="border-bottom:1px solid var(--border-light)" id="word-<?= $w['id'] ?>">
          <span style="font-family:Consolas,monospace"><?= htmlspecialchars($w['word']) ?></span>
          <div class="d-flex align-items-center gap-2">
            <span class="badge <?= $w['action']==='block'?'bg-danger':'bg-warning text-dark' ?>"><?= $w['action']==='block'?'блок':'пометка' ?></span>
            <button class="btn btn-sm btn-outline-secondary border-0" onclick="delWord(<?= $w['id'] ?>)"><i class="bi bi-trash"></i></button>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function delWord(id) {
  postAction(BASE_URL + '/admin/words/remove', { id: id }, () => {
    document.getElementById('word-'+id)?.remove(); showToast('Удалено','info');
  });
}
</script>
