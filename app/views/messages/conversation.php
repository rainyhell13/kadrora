<div class="messages-wrap">

  <!-- Conversations list -->
  <div class="conv-list d-none d-md-flex flex-column">
    <div class="conv-list-header">
      <i class="bi bi-chat-fill me-2" style="color:var(--accent)"></i>Сообщения
    </div>
    <div class="conv-list-body">
      <?php foreach ($conversations as $conv): ?>
      <a href="<?= BASE_URL ?>/messages/<?= $conv['id'] ?>"
         class="conv-item text-decoration-none <?= (int)$conv['id']===(int)$target['id']?'active':'' ?>">
        <div class="position-relative flex-shrink-0">
          <?php if ($conv['avatar']): ?>
          <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($conv['avatar']) ?>"
               class="rounded-circle" width="42" height="42" style="object-fit:cover" alt="">
          <?php else: ?>
          <div class="avatar-placeholder" style="width:42px;height:42px;font-size:.9rem">
            <?= mb_strtoupper(mb_substr($conv['first_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <?php if ($conv['is_online']): ?>
          <span class="online-dot position-absolute" style="bottom:1px;right:1px"></span>
          <?php endif; ?>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex justify-content-between">
            <span class="conv-name"><?= htmlspecialchars($conv['first_name'].' '.$conv['last_name']) ?></span>
            <span style="font-size:.7rem;color:var(--text-muted)">
              <?= $conv['last_message_at'] ? timeAgo($conv['last_message_at']) : '' ?>
            </span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="conv-preview"><?= htmlspecialchars(mb_substr($conv['last_message'] ?? '', 0, 30)) ?></span>
            <?php if ($conv['unread_count'] > 0): ?>
            <span class="badge bg-primary rounded-pill ms-1"><?= $conv['unread_count'] ?></span>
            <?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Chat -->
  <div class="chat-area">
    <div class="chat-header">
      <a href="<?= BASE_URL ?>/messages" class="btn btn-sm btn-outline-secondary border-0 d-md-none me-1">
        <i class="bi bi-arrow-left"></i>
      </a>
      <div class="position-relative">
        <?php if ($target['avatar']): ?>
        <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($target['avatar']) ?>"
             class="rounded-circle" width="38" height="38" style="object-fit:cover" alt="">
        <?php else: ?>
        <div class="avatar-placeholder" style="width:38px;height:38px;font-size:.85rem">
          <?= mb_strtoupper(mb_substr($target['first_name'],0,1)) ?>
        </div>
        <?php endif; ?>
        <?php if ($target['is_online']): ?>
        <span class="online-dot position-absolute" style="bottom:1px;right:1px"></span>
        <?php endif; ?>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($target['username']) ?>"
           class="fw-semibold text-decoration-none d-block" style="color:var(--text-primary)">
          <?= htmlspecialchars($target['first_name'] . ' ' . $target['last_name']) ?>
        </a>
        <div style="font-size:.75rem;color:<?= $target['is_online']?'var(--success)':'var(--text-muted)' ?>">
          <?= $target['is_online'] ? '● онлайн' : 'был(а) '.timeAgo($target['last_seen']) ?>
        </div>
      </div>
    </div>

    <div class="chat-messages" id="messages-container">
      <?php foreach ($messages as $msg): ?>
        <?php include BASE_PATH . '/app/views/messages/partials/message_bubble.php'; ?>
      <?php endforeach; ?>
    </div>

    <!-- Превью прикреплённого фото -->
    <div id="msgAttachPreview" class="msg-attach-preview d-none">
      <div class="position-relative d-inline-block">
        <img id="msgAttachThumb" src="" alt="">
        <button type="button" class="msg-attach-remove" onclick="clearMsgAttach()" title="Убрать">
          <i class="bi bi-x"></i>
        </button>
      </div>
      <span class="msg-attach-name" id="msgAttachName"></span>
    </div>

    <div class="chat-input-area">
      <div class="dropdown dropup">
        <button class="chat-attach-btn" type="button" data-bs-toggle="dropdown" title="Прикрепить">
          <i class="bi bi-paperclip"></i>
        </button>
        <ul class="dropdown-menu">
          <li><button class="dropdown-item" type="button" onclick="document.getElementById('msgImageInput').click()">
            <i class="bi bi-image me-2 text-accent"></i>Фотография
          </button></li>
        </ul>
      </div>
      <input type="file" id="msgImageInput" class="d-none" accept="image/*" onchange="previewMsgAttach(this)">
      <input type="text" id="msgInput" class="chat-input"
             placeholder="Написать сообщение..."
             onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage()}">
      <button class="chat-send-btn" onclick="sendMessage()">
        <i class="bi bi-send-fill" style="font-size:.85rem"></i>
      </button>
    </div>
  </div>
</div>

<script>
const CONV_ID   = <?= $conv_id ?>;
const TARGET_ID = <?= $target['id'] ?>;
let lastMsgId   = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;

function scrollToBottom() {
  const c = document.getElementById('messages-container');
  c.scrollTop = c.scrollHeight;
}
scrollToBottom();

function previewMsgAttach(input) {
  if (input.files && input.files[0]) {
    const f = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('msgAttachThumb').src = e.target.result;
      document.getElementById('msgAttachName').textContent = f.name;
      document.getElementById('msgAttachPreview').classList.remove('d-none');
    };
    reader.readAsDataURL(f);
  }
}
function clearMsgAttach() {
  document.getElementById('msgImageInput').value = '';
  document.getElementById('msgAttachPreview').classList.add('d-none');
  document.getElementById('msgAttachThumb').src = '';
}

function sendMessage() {
  const input   = document.getElementById('msgInput');
  const content = input.value.trim();
  const fileInp = document.getElementById('msgImageInput');
  if (!content && !fileInp.files.length) return;

  const fd = new FormData();
  fd.append('csrf_token', CSRF_TOKEN);
  fd.append('target_id', TARGET_ID);
  fd.append('content', content);
  if (fileInp.files[0]) fd.append('image', fileInp.files[0]);

  input.value = '';

  fetch(BASE_URL + '/messages/send', { method:'POST', body:fd })
    .then(r => r.json()).then(data => {
      if (data.success) {
        document.getElementById('messages-container').insertAdjacentHTML('beforeend', data.html);
        lastMsgId = data.msg_id;
        clearMsgAttach();
        scrollToBottom();
      } else {
        showToast(data.error || 'Не удалось отправить', 'danger');
      }
    });
}

setInterval(() => {
  fetch(`${BASE_URL}/messages/poll?conv_id=${CONV_ID}&last_id=${lastMsgId}`)
    .then(r=>r.json()).then(data=>{
      if (data.count > 0) {
        document.getElementById('messages-container').insertAdjacentHTML('beforeend', data.html);
        lastMsgId = data.last_id;
        scrollToBottom();
      }
    });
}, 3000);
</script>
