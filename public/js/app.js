'use strict';

/* ---- Toast ---- */
function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success:'check-circle-fill', danger:'x-circle-fill', warning:'exclamation-triangle-fill', info:'info-circle-fill' };
  const toast = document.createElement('div');
  toast.className = `toast-item ${type}`;
  toast.innerHTML = `<i class="bi bi-${icons[type]||icons.info}" style="flex-shrink:0"></i><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity='0'; toast.style.transform='translateY(10px)'; toast.style.transition='.25s'; setTimeout(()=>toast.remove(),250); }, 3200);
}

/* ---- AJAX helper ---- */
function postAction(url, data, onSuccess) {
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  for (const [k, v] of Object.entries(data)) fd.append(k, v);
  fetch(url, { method:'POST', body:fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) onSuccess(res);
      else showToast(res.error || 'Ошибка', 'danger');
    })
    .catch(() => showToast('Ошибка соединения', 'danger'));
}

/* ---- Like post ---- */
function likePost(postId, btn) {
  postAction(BASE_URL + '/post/like', { post_id: postId }, res => {
    btn.classList.toggle('liked', res.liked);
    const icon = btn.querySelector('i');
    if (icon) icon.className = 'bi bi-' + (res.liked ? 'heart-fill' : 'heart');
    const el = document.getElementById('likes-count-' + postId);
    if (el) el.textContent = res.count > 0 ? res.count : '';
  });
}

/* ---- Delete post ---- */
function deletePost(postId) {
  if (!confirm('Удалить пост?')) return;
  postAction(BASE_URL + '/post/delete', { post_id: postId }, () => {
    const el = document.getElementById('post-' + postId);
    if (el) { el.style.transition='opacity .25s'; el.style.opacity='0'; setTimeout(()=>el.remove(),250); }
    showToast('Пост удалён', 'info');
  });
}

/* ---- Comments ---- */
const loadedComments = new Set();

function toggleComments(postId) {
  const section = document.getElementById('comments-' + postId);
  if (!section) return;
  const hidden = section.classList.contains('d-none');
  section.classList.toggle('d-none', !hidden);
  if (hidden && !loadedComments.has(postId)) loadComments(postId);
}

function loadComments(postId) {
  fetch(BASE_URL + '/comment/list?post_id=' + postId)
    .then(r => r.json())
    .then(res => {
      const list = document.getElementById('comments-list-' + postId);
      if (list) list.innerHTML = res.html || '<div style="color:var(--text-muted);font-size:.8rem;padding:4px 0">Нет комментариев</div>';
      loadedComments.add(postId);
    });
}

function submitComment(postId) {
  const input   = document.getElementById('comment-input-' + postId);
  const content = input?.value?.trim();
  if (!content) return;
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('post_id', postId);
  fd.append('content', content);
  fetch(BASE_URL + '/comment/create', { method:'POST', body:fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        document.getElementById('comments-list-' + postId)?.insertAdjacentHTML('beforeend', res.html);
        input.value = '';
        loadedComments.add(postId);
        // увеличить счётчик комментариев
        const cc = document.getElementById('comm-count-' + postId);
        if (cc) cc.textContent = (parseInt(cc.textContent || '0', 10) || 0) + 1;
      } else showToast(res.error||'Ошибка','danger');
    });
}

function deleteComment(id) {
  if (!confirm('Удалить комментарий?')) return;
  postAction(BASE_URL + '/comment/delete', { comment_id: id }, () => {
    document.getElementById('comment-' + id)?.remove();
  });
}

function likeComment(id, btn) {
  postAction(BASE_URL + '/comment/like', { comment_id: id }, res => {
    btn.classList.toggle('liked', res.liked);
    const icon = btn.querySelector('i');
    if (icon) icon.className = 'bi bi-heart' + (res.liked ? '-fill' : '');
    const c = btn.querySelector('.clike-count');
    if (c) c.textContent = res.count > 0 ? res.count : '';
  });
}

/* ---- Friends ---- */
function sendFriendRequest(userId, btn) {
  postAction(BASE_URL + '/friend/add', { user_id: userId }, () => {
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="bi bi-clock me-1"></i>Отправлено';
      btn.className = btn.className.replace('btn-outline-primary','btn-outline-secondary');
    }
    showToast('Заявка отправлена', 'success');
  });
}

/* ---- Photo modal ---- */
function openPhotoModal(src) {
  document.getElementById('photoModalImg').src = src;
  new bootstrap.Modal(document.getElementById('photoModal')).show();
}

/* ---- Переключение темы ---- */
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  html.setAttribute('data-bs-theme', next);
  // иконка кнопки
  const btn = document.getElementById('themeToggleBtn');
  if (btn) btn.innerHTML = next === 'light'
    ? '<i class="bi bi-moon-stars-fill"></i>'
    : '<i class="bi bi-sun-fill"></i>';
  // сохранить выбор на сервере (cookie)
  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('theme', next);
  fetch(BASE_URL + '/theme/set', { method: 'POST', body: fd }).catch(() => {});
}

/* ---- Закладка записи ---- */
function toggleBookmark(postId, btn) {
  postAction(BASE_URL + '/bookmark/toggle', { post_id: postId }, res => {
    btn.classList.toggle('bookmarked', res.bookmarked);
    btn.innerHTML = `<i class="bi bi-bookmark${res.bookmarked ? '-star-fill' : ''}"></i> ` +
      (res.bookmarked ? 'В закладках' : 'В закладки');
    showToast(res.bookmarked ? 'Добавлено в закладки' : 'Убрано из закладок',
      res.bookmarked ? 'success' : 'info');
  });
}

/* ---- Жалобы ---- */
function openReport(type, id) {
  document.getElementById('reportType').value = type;
  document.getElementById('reportTargetId').value = id;
  document.getElementById('reportCategory').value = 'spam';
  document.getElementById('reportComment').value = '';
  new bootstrap.Modal(document.getElementById('reportModal')).show();
}
function submitReport() {
  const type = document.getElementById('reportType').value;
  const id   = document.getElementById('reportTargetId').value;
  postAction(BASE_URL + '/report', {
    target_type: type, target_id: id,
    category: document.getElementById('reportCategory').value,
    comment: document.getElementById('reportComment').value.trim(),
  }, () => {
    bootstrap.Modal.getInstance(document.getElementById('reportModal'))?.hide();
    showToast('Жалоба отправлена. Спасибо!', 'success');
  });
}
