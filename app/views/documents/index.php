<?php
function docExtClass(string $ext): string {
    return match (strtolower($ext)) {
        'pdf'                       => 'doc-ext-pdf',
        'doc','docx','rtf','odt'    => 'doc-ext-doc',
        'xls','xlsx','csv','ods'    => 'doc-ext-xls',
        'ppt','pptx'                => 'doc-ext-ppt',
        'zip','rar','7z'            => 'doc-ext-zip',
        'jpg','jpeg','png','gif','webp' => 'doc-ext-img',
        'txt'                       => 'doc-ext-txt',
        default                     => '',
    };
}
function docIcon(string $ext): string {
    return match (strtolower($ext)) {
        'pdf'                       => 'file-earmark-pdf',
        'doc','docx','rtf','odt'    => 'file-earmark-word',
        'xls','xlsx','csv','ods'    => 'file-earmark-spreadsheet',
        'ppt','pptx'                => 'file-earmark-slides',
        'zip','rar','7z'            => 'file-earmark-zip',
        'jpg','jpeg','png','gif','webp' => 'file-earmark-image',
        default                     => 'file-earmark-text',
    };
}
function humanSize(int $b): string {
    if ($b >= 1048576) return round($b / 1048576, 1) . ' МБ';
    if ($b >= 1024)    return round($b / 1024) . ' КБ';
    return $b . ' Б';
}
?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="fw-bold mb-0">
        <i class="bi bi-file-earmark-text me-2" style="color:var(--accent)"></i>Документы
      </h4>
      <span style="font-size:.85rem;color:var(--text-muted)"><?= count($docs) ?> файл(ов)</span>
    </div>

    <!-- Форма загрузки -->
    <div class="card mb-3">
      <div class="card-body p-3">
        <form method="POST" action="<?= BASE_URL ?>/documents/upload" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label">Название (необязательно)</label>
              <input type="text" name="title" class="form-control form-control-sm" placeholder="Например: Курсовая работа">
            </div>
            <div class="col-md-5">
              <label class="form-label">Файл</label>
              <input type="file" name="document" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-upload me-1"></i>Загрузить
              </button>
            </div>
          </div>
          <div class="mt-1" style="font-size:.72rem;color:var(--text-muted)">
            PDF, Word, Excel, PowerPoint, TXT, архивы, изображения. Максимум 25 МБ.
          </div>
        </form>
      </div>
    </div>

    <!-- Список документов -->
    <div class="card">
      <div class="card-body p-0">
        <?php if (empty($docs)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
          <i class="bi bi-folder2-open" style="font-size:2.8rem;opacity:.2;display:block;margin-bottom:12px"></i>
          <p class="mb-0">Документов пока нет</p>
        </div>
        <?php else: ?>
        <?php foreach ($docs as $d): ?>
        <div class="doc-item" id="doc-<?= $d['id'] ?>">
          <div class="doc-icon <?= docExtClass($d['ext']) ?>">
            <i class="bi bi-<?= docIcon($d['ext']) ?>"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= BASE_URL ?>/documents/<?= $d['id'] ?>/download"
               class="fw-semibold text-decoration-none d-block text-truncate" style="color:var(--text-primary)">
              <?= htmlspecialchars($d['title']) ?>
            </a>
            <div style="font-size:.75rem;color:var(--text-muted)">
              <span class="text-uppercase"><?= htmlspecialchars($d['ext']) ?></span>
              · <?= humanSize((int)$d['size_bytes']) ?>
              · <?= timeAgo($d['created_at']) ?>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/documents/<?= $d['id'] ?>/download"
             class="btn btn-sm btn-outline-secondary border-0" title="Скачать">
            <i class="bi bi-download"></i>
          </a>
          <button class="btn btn-sm border-0 px-2" style="background:none;color:var(--text-muted)"
                  onclick="deleteDoc(<?= $d['id'] ?>)" title="Удалить">
            <i class="bi bi-trash"></i>
          </button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4 d-none d-lg-block">
    <div class="widget">
      <div class="card-header"><i class="bi bi-info-circle me-1"></i>О документах</div>
      <div class="p-3" style="font-size:.85rem;color:var(--text-secondary)">
        Загружайте и храните личные файлы: документы, презентации, изображения и архивы.
        Файлы доступны только вам и доступны для скачивания в любой момент.
      </div>
    </div>
  </div>
</div>

<script>
function deleteDoc(id) {
  if (!confirm('Удалить документ?')) return;
  postAction(BASE_URL + '/documents/delete', { doc_id: id }, () => {
    document.getElementById('doc-' + id)?.remove();
    showToast('Документ удалён', 'info');
  });
}
</script>
