<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/repo.php';

$u = current_user();
$over = $u ? overdue_count($pdo, (int)$u['id']) : 0;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function is_active(string $p, string $path): string { return $p===$path ? 'active' : ''; }
?>
<div class="cool-bg"></div>
<div class="container">
  <div class="topbar">
    <div class="brand">
      <span style="width:34px;height:34px;border-radius:12px;background:rgba(53,208,201,.25);display:inline-flex;align-items:center;justify-content:center;">📚</span>
      <span>Библиотека</span>
    </div>

    <div class="nav">
      <a class="<?=is_active('/index.php',$path)?>" href="/index.php">Каталог</a>
      <a class="<?=is_active('/search.php',$path)?>" href="/search.php">Поиск</a>
      <a class="<?=is_active('/my.php',$path)?>" href="/my.php">Мои книги</a>
      <?php if($u && ($u['role'] ?? '')==='admin'): ?>
        <a class="<?=is_active('/admin.php',$path)?>" href="/admin.php">Управление</a>
      <?php endif; ?>

      <?php if($u): ?>
        <span class="badge">⏰ Просрочено: <?=$over?></span>
        <span class="muted"><?=$u['full_name']?></span>
        <a href="/logout.php" class="btn secondary" style="padding:10px 12px;">Выход</a>
      <?php else: ?>
        <a href="/login.php" class="btn secondary" style="padding:10px 12px;">Вход</a>
        <a href="/register.php" class="btn" style="padding:10px 12px;">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
</div>
