<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/repo.php';

require_login();
$u = current_user();
$loans = my_loans($pdo, (int)$u['id']);
$myStats = stats_for_user($pdo, (int)$u['id']);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Библиотека — Мои книги</title>
  <link rel="stylesheet" href="/assets/styles.css"/>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="container fade-in-up">
  <h1>Мои книги</h1>
  <div class="muted">Список активных выдач.</div>

  <div class="stats fade-in-up">
    <div class="stat"><div class="k">Мои активные</div><div class="v"><?=$myStats['my_active']?></div></div>
    <div class="stat"><div class="k">Мои просрочено</div><div class="v"><?=$myStats['my_overdue']?></div></div>
    <div class="stat"><div class="k">Подсказка</div>
      <div class="v" style="font-size:14px;font-weight:700;color:var(--muted);margin-top:8px;">Просрочка подсвечивается розовым</div>
    </div>
  </div>

  <div style="margin-top:12px; display:flex; flex-direction:column; gap:12px;">
    <?php foreach($loans as $l): ?>
      <div class="card">
        <div class="tag <?=$l['is_overdue']?'busy':''?>"><?=$l['is_overdue']?'Просрочено':'В срок'?></div>
        <h2 class="title"><?=htmlspecialchars($l['title'])?></h2>
        <div class="author"><?=htmlspecialchars($l['author'])?></div>
        <div style="margin-top:10px;font-size:13px;">
          Взята: <b><?=htmlspecialchars($l['issued_at'])?></b> · Вернуть до: <b><?=htmlspecialchars($l['due_at'])?></b>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if(!$loans): ?>
      <div class="muted" style="margin-top:14px;">Пока нет выданных книг.</div>
    <?php endif; ?>
  </div>
</div>

<script src="/assets/app.js"></script>
</body>
</html>
