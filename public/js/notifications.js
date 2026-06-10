/* ============================================================
   Kadrora — Polling уведомлений и сообщений
   ============================================================ */

'use strict';

// Опрос каждые 30 секунд
setInterval(pollBadges, 30000);

function pollBadges() {
  fetch(BASE_URL + '/notifications/count')
    .then(r => r.json())
    .then(data => {
      updateBadge('notif-badge', data.notif_count);
      updateBadge('msg-badge',   data.message_count);
    })
    .catch(() => {});
}

function updateBadge(id, count) {
  let badge = document.getElementById(id);
  if (count > 0) {
    const parent = badge?.parentElement || document.querySelector(
      id === 'notif-badge' ? 'a[href$="/notifications"]' : 'a[href$="/messages"]'
    );
    if (!badge && parent) {
      badge = document.createElement('span');
      badge.id = id;
      badge.className = 'badge bg-danger badge-nav';
      parent.style.position = 'relative';
      parent.appendChild(badge);
    }
    if (badge) badge.textContent = count;
  } else if (badge) {
    badge.remove();
  }
}
