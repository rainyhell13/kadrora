<?php include BASE_PATH . '/app/views/admin/_nav.php'; ?>

<div class="row g-3 mb-2">
  <?php
  $cards = [
    ['Пользователей', $stats['users'],   'people-fill',     'var(--accent)'],
    ['Онлайн',        $stats['online'],  'circle-fill',     'var(--success)'],
    ['Новых за неделю', $stats['new_week'], 'person-plus-fill', 'var(--accent)'],
    ['Записей',       $stats['posts'],   'card-text',       'var(--accent)'],
    ['Сообществ',     $stats['groups'],  'collection-fill', 'var(--accent)'],
    ['Заблокировано', $stats['banned'],  'slash-circle',    'var(--danger)'],
  ];
  foreach ($cards as [$label, $val, $icon, $col]): ?>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="card h-100 text-center p-3">
      <i class="bi bi-<?= $icon ?>" style="font-size:1.5rem;color:<?= $col ?>"></i>
      <div class="fw-bold mt-1" style="font-size:1.6rem;color:var(--text-primary)"><?= $val ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)"><?= $label ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-flag me-1" style="color:var(--danger)"></i>Открытые жалобы</span>
        <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-sm btn-outline-primary">Перейти к очереди</a>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-baseline gap-2 mb-3">
          <span style="font-size:2.4rem;font-weight:800;color:<?= $pending ? 'var(--danger)' : 'var(--success)' ?>"><?= $pending ?></span>
          <span style="color:var(--text-muted)">жалоб ожидают рассмотрения</span>
        </div>
        <?php if (!empty($byCategory)): ?>
        <?php
        $catNames = ['spam'=>'Спам','insult'=>'Оскорбления','violence'=>'Насилие','adult'=>'18+','fraud'=>'Мошенничество','hate'=>'Разжигание вражды','other'=>'Другое'];
        foreach ($byCategory as $c): ?>
        <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--border-light)">
          <span style="font-size:.875rem"><?= $catNames[$c['category']] ?? $c['category'] ?></span>
          <span class="badge bg-secondary"><?= $c['c'] ?></span>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p style="color:var(--text-muted);font-size:.875rem;margin:0">Жалоб нет — всё спокойно.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-info-circle me-1"></i>О панели модерации</div>
      <div class="card-body" style="font-size:.875rem;color:var(--text-secondary)">
        <p>Действий модерации за 7 дней: <b style="color:var(--text-primary)"><?= $modToday ?></b></p>
        <p class="mb-1">Ваша роль: <span class="badge bg-primary"><?= $role === 'admin' ? 'Администратор' : 'Модератор' ?></span></p>
        <ul class="mt-2 mb-0" style="padding-left:18px;line-height:1.8">
          <li>Жалобы — очередь обращений на контент и пользователей</li>
          <li>Пользователи — баны, мьюты, предупреждения<?= $role === 'admin' ? ', роли' : '' ?></li>
          <li>Контент — помеченные автофильтром и скрытые записи</li>
          <li>Стоп-слова — настройка автоматической фильтрации</li>
          <li>Журнал — все действия модераторов</li>
        </ul>
      </div>
    </div>
  </div>
</div>
