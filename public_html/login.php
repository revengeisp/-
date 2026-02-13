<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';

$msg = null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT id,email,password_hash,role,full_name FROM users WHERE email=?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if(!$u || !password_verify($pass, $u['password_hash'])) $msg = 'Неверный логин или пароль';
  else{
    $_SESSION['user'] = ['id'=>$u['id'],'email'=>$u['email'],'role'=>$u['role'],'full_name'=>$u['full_name']];
    header('Location: /index.php'); exit;
  }
}
?>
<!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Вход</title>
<link rel="stylesheet" href="/assets/styles.css"/>
</head><body>
<?php include __DIR__ . '/_header.php'; ?>
<div class="container fade-in-up">
  <h1>Вход</h1>
  <?php if($msg): ?><div class="card" style="border-color:rgba(255,127,178,.35)"><?=$msg?></div><?php endif; ?>
  <form method="post" class="card" style="margin-top:12px;">
    <div class="row">
      <input class="input" name="email" placeholder="Email" required>
      <input class="input" type="password" name="password" placeholder="Пароль" required>
      <button class="btn" type="submit">Войти</button>
    </div>
  </form>
</div>
</body></html>
