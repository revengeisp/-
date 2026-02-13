<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/repo.php';

$f = [
  'title' => $_GET['title'] ?? '',
  'author'=> $_GET['author'] ?? '',
  'genre' => $_GET['genre'] ?? '',
  'isbn'  => $_GET['isbn'] ?? '',
];
$books = ($_GET ? advanced_search($pdo, $f) : []);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Библиотека — Расширенный поиск</title>
  <link rel="stylesheet" href="/assets/styles.css"/>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="container fade-in-up">
  <h1>Расширенный поиск</h1>
  <div class="muted">Ищите по нескольким критериям.</div>

  <form class="row" method="get" style="margin-top:12px;">
    <input class="input" name="title"  value="<?=htmlspecialchars($f['title'])?>"  placeholder="Название книги (например: 1984)"/>
    <input class="input" name="author" value="<?=htmlspecialchars($f['author'])?>" placeholder="Автор (например: Оруэлл)"/>
    <input class="input" name="genre"  value="<?=htmlspecialchars($f['genre'])?>"  placeholder="Жанр (например: Фэнтези)"/>
    <input class="input" name="isbn"   value="<?=htmlspecialchars($f['isbn'])?>"   placeholder="ISBN"/>
    <button class="btn" type="submit" style="min-width:160px;">Найти книги</button>
  </form>

  <?php if($_GET): ?>
    <div class="grid">
      <?php foreach($books as $b): $issued = (int)$b['is_issued']===1; ?>
        <div class="card fade-in-up">
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
              <div class="row" style="margin-top:12px;">
                <a class="btn secondary" href="/index.php?q=<?=urlencode($b['title'])?>">Открыть в каталоге</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script src="/assets/app.js"></script>
</body>
</html>
