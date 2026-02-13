<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/repo.php';
require_once __DIR__ . '/src/actions.php';

require_admin();
$u = current_user();
$msg = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';

  if($action==='approve'){
    $res = approve_request($pdo, (int)$u['id'], (int)$_POST['request_id'], (int)($_POST['days'] ?? 14));
    $msg = $res['msg'];
  }
  if($action==='reject'){
    $res = reject_request($pdo, (int)$u['id'], (int)$_POST['request_id']);
    $msg = $res['msg'];
  }
  if($action==='extend'){
    $res = extend_loan($pdo, (int)$_POST['loan_id'], (int)$_POST['days']);
    $msg = $res['msg'];
  }
  if($action==='accept'){
    $res = accept_return($pdo, (int)$_POST['loan_id']);
    $msg = $res['msg'];
  }
  if($action==='add_book'){
    $res = add_book($pdo, $_POST);
    $msg = $res['msg'];
  }
}

$pending = pending_requests($pdo);
$users = admin_users_with_loans($pdo);
$selectedUserId = (int)($_GET['user_id'] ?? 0);
$selectedLoans = $selectedUserId ? admin_active_loans_for_user($pdo, $selectedUserId) : [];
$stats = stats_global($pdo);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Библиотека — Управление</title>
  <link rel="stylesheet" href="/assets/styles.css"/>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

<div class="container fade-in-up">
  <h1>Управление</h1>
  <div class="muted">Заявки, выдача/продление/возврат, добавление книг.</div>

  <div class="stats fade-in-up">
    <div class="stat"><div class="k">Всего книг</div><div class="v"><?=$stats['total_books']?></div></div>
    <div class="stat"><div class="k">Выдано</div><div class="v"><?=$stats['issued_books']?></div></div>
    <div class="stat"><div class="k">Просрочено</div><div class="v"><?=$stats['overdue_books']?></div></div>
  </div>

  <?php if($msg): ?>
    <div style="margin-top:12px;padding:10px 12px;border-radius:12px;background:rgba(255,127,178,.12);border:1px solid rgba(255,127,178,.28);">
      <?=$msg?>
    </div>
  <?php endif; ?>

  <div class="row" style="margin-top:14px;">
    <button class="btn" type="button" onclick="openModal('mAddBook')">Добавить книгу</button>
  </div>

  <h2 style="margin-top:18px;">Заявки на выдачу</h2>
  <div style="display:flex; flex-direction:column; gap:12px;">
    <?php foreach($pending as $r): ?>
      <div class="card">
        <div class="muted"><?=htmlspecialchars($r['full_name'])?> · билет <?=htmlspecialchars($r['ticket_no'] ?? '-')?></div>
        <h2 class="title" style="margin-top:6px;"><?=htmlspecialchars($r['title'])?></h2>
        <div class="author"><?=htmlspecialchars($r['author'])?> · ISBN <?=htmlspecialchars($r['isbn'])?></div>

        <div class="row">
          <form method="post" style="margin:0;">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="request_id" value="<?=$r['id']?>">
            <input class="input" type="number" name="days" value="14" min="1" style="max-width:140px;">
            <button class="btn" type="submit">Выдать</button>
          </form>

          <form method="post" style="margin:0;">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="request_id" value="<?=$r['id']?>">
            <button class="btn secondary" type="submit">Отклонить</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if(!$pending): ?>
      <div class="muted">Нет новых заявок.</div>
    <?php endif; ?>
  </div>

  <h2 style="margin-top:18px;">Управление читателями</h2>

  <div class="row" style="align-items:flex-start;">
    <div style="flex:1; min-width:280px;">
      <?php foreach($users as $us): ?>
        <a href="/admin.php?user_id=<?=$us['id']?>" style="text-decoration:none;">
          <div class="card" style="margin-bottom:12px;">
            <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <div>
                <div style="font-weight:800; font-family:Montserrat;"><?=htmlspecialchars($us['full_name'])?></div>
                <div class="muted"><?=htmlspecialchars($us['email'])?> · билет <?=htmlspecialchars($us['ticket_no'] ?? '-')?></div>
              </div>
              <div class="badge">Активных: <?=$us['active_cnt']?> · Просрочено: <?=$us['overdue_cnt']?></div>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div style="flex:1; min-width:280px;">
      <div class="card">
        <div style="font-weight:800; font-family:Montserrat;">Активные книги читателя</div>
        <div class="muted">Выберите читателя слева, чтобы продлить или принять книгу.</div>

        <div style="margin-top:12px; display:flex; flex-direction:column; gap:10px;">
          <?php foreach($selectedLoans as $l): ?>
            <div style="padding:12px;border:1px solid rgba(0,0,0,.06);border-radius:14px;">
              <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                <div>
                  <div style="font-weight:800; font-family:Montserrat;"><?=htmlspecialchars($l['title'])?></div>
                  <div class="muted"><?=htmlspecialchars($l['author'])?></div>
                  <div style="margin-top:6px;font-size:13px;">
                    До: <b><?=htmlspecialchars($l['due_at'])?></b>
                    <?php if($l['is_overdue']): ?>
                      <span style="margin-left:8px;color:#b42366;font-weight:800;">Просрочено</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="row" style="margin:0;">
                  <form method="post" style="margin:0;">
                    <input type="hidden" name="action" value="extend">
                    <input type="hidden" name="loan_id" value="<?=$l['id']?>">
                    <input class="input" type="number" name="days" value="14" min="1" style="max-width:120px;">
                    <button class="btn" type="submit">Продлить</button>
                  </form>

                  <form method="post" style="margin:0;">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="loan_id" value="<?=$l['id']?>">
                    <button class="btn secondary" type="submit">Принять</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if($selectedUserId && !$selectedLoans): ?>
            <div class="muted">У читателя нет активных книг.</div>
          <?php endif; ?>
          <?php if(!$selectedUserId): ?>
            <div class="muted">Выберите читателя для управления.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="mAddBook">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div style="font-family:Montserrat;font-weight:800;">Добавить книгу</div>
        <div class="muted">Заполните поля и сохраните.</div>
      </div>
      <button class="x" onclick="closeModal('mAddBook')">×</button>
    </div>

    <form method="post" class="row" style="margin-top:12px;">
      <input type="hidden" name="action" value="add_book">
      <input class="input" name="title" placeholder="Название" required>
      <input class="input" name="author" placeholder="Автор" required>
      <input class="input" name="genre" placeholder="Жанр" required>
      <input class="input" name="isbn" placeholder="ISBN" required>
      <select class="input" name="cover_color" style="min-width:220px;">
        <option value="mint">Mint</option>
        <option value="pink">Pink</option>
      </select>
      <button class="btn" type="submit">Сохранить</button>
      <button class="btn secondary" type="button" onclick="closeModal('mAddBook')">Отмена</button>
    </form>
  </div>
</div>

<script src="/assets/app.js"></script>
</body>
</html>
