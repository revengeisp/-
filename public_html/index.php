<?php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/repo.php';
require_once __DIR__ . '/src/actions.php';

$u = current_user();
$msg = null;

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'request') {
  if (!$u) { header('Location: /login.php'); exit; }
  $res = create_request($pdo, (int)$u['id'], (int)($_POST['book_id'] ?? 0));
  $msg = $res['msg'];
}

$q = trim($_GET['q'] ?? '');
$books = catalog($pdo, $q);
$stats = stats_global($pdo);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Библиотека — Каталог</title>
  <link rel="stylesheet" href="/assets/styles.css"/>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="container fade-in-up">
  <h1>Каталог</h1>
  <div class="muted">Найдите книгу и подайте заявку на выдачу (клик по карточке).</div>

  <div class="stats fade-in-up">
    <div class="stat"><div class="k">Всего книг</div><div class="v"><?=$stats['total_books']?></div></div>
    <div class="stat"><div class="k">Выдано</div><div class="v"><?=$stats['issued_books']?></div></div>
    <div class="stat"><div class="k">Просрочено</div><div class="v"><?=$stats['overdue_books']?></div></div>
  </div>

  <?php if($msg): ?>
    <div style="margin-top:12px;padding:10px 12px;border-radius:12px;background:rgba(53,208,201,.12);border:1px solid rgba(53,208,201,.28);">
      <?=$msg?>
    </div>
  <?php endif; ?>

  <form class="search-row" method="get">
    <input class="input" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Быстрый поиск: название, автор, жанр, ISBN"/>
    <button class="btn" type="submit">Найти</button>
    <a class="btn secondary" href="/search.php">Расширенный поиск</a>
  </form>

  <div class="grid">
    <?php foreach($books as $b): $issued = (int)$b['is_issued']===1; ?>
      <div class="card clickable fade-in-up"
           data-book-modal="1"
           data-id="<?=$b['id']?>"
           data-title="<?=htmlspecialchars($b['title'], ENT_QUOTES)?>"
           data-author="<?=htmlspecialchars($b['author'], ENT_QUOTES)?>"
           data-genre="<?=htmlspecialchars($b['genre'], ENT_QUOTES)?>"
           data-isbn="<?=htmlspecialchars($b['isbn'], ENT_QUOTES)?>"
           data-issued="<?=$issued?1:0?>">
        <div class="tag <?=$issued?'busy':''?>"><?=$issued?'Выдана':'Доступна'?></div>

        <div class="book-row">
          <?php
            $g = mb_strtolower($b['genre']);
            $emoji = '📘';
            if (str_contains($g,'фэнт')) $emoji = '✨';
            else if (str_contains($g,'фантаст')) $emoji = '🪐';
            else if (str_contains($g,'класс')) $emoji = '📕';
            else if (str_contains($g,'антиут')) $emoji = '📖';
            else if (str_contains($g,'филос')) $emoji = '🌟';
            else if (str_contains($g,'проз')) $emoji = '📗';
          ?>
          <div class="cover <?=$b['cover_color']==='pink'?'pink':'mint'?>"><?=$emoji?></div>

          <div style="flex:1;">
            <h2 class="title"><?=htmlspecialchars($b['title'])?></h2>
            <div class="author"><?=htmlspecialchars($b['author'])?></div>
            <div class="meta">Жанр: <b><?=htmlspecialchars($b['genre'])?></b></div>
            <div class="meta">ISBN: <?=htmlspecialchars($b['isbn'])?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="modal-backdrop" id="mBook">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div style="font-family:Montserrat;font-weight:800;" id="mBookTitle"></div>
        <div class="muted" id="mBookAuthor"></div>
      </div>
      <button class="x" onclick="closeModal('mBook')">×</button>
    </div>

    <div class="row" style="margin-top:10px;">
      <div class="badge">Статус: <span id="mBookStatus"></span></div>
      <div class="badge" style="background:rgba(53,208,201,.12);color:#0f766e;">Жанр: <span id="mBookGenre"></span></div>
      <div class="badge" style="background:rgba(0,0,0,.04);color:#374151;">ISBN: <span id="mBookIsbn"></span></div>
    </div>

    <form method="post" class="row" style="margin-top:14px;">
      <input type="hidden" name="action" value="request"/>
      <input type="hidden" name="book_id" id="mBookId" value="0"/>
      <button class="btn" id="mBookBtn" type="submit">Взять книгу</button>
      <button class="btn secondary" type="button" onclick="closeModal('mBook')">Отмена</button>
    </form>
  </div>
</div>

<script src="/assets/app.js"></script>
</body>
</html>
