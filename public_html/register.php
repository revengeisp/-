<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/auth.php';

$msg = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $name  = trim($_POST['full_name'] ?? '');
  $ticket= trim($_POST['ticket_no'] ?? '');

  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $msg='Некорректный email';
  elseif(strlen($pass) < 6) $msg='Пароль минимум 6 символов';
  else{
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    try{
      $stmt = $pdo->prepare("INSERT INTO users(email,password_hash,role,full_name,ticket_no) VALUES(?,?,?,?,?)");
      $stmt->execute([$email,$hash,'reader',$name,$ticket]);
      $_SESSION['user'] = ['id'=>$pdo->lastInsertId(),'email'=>$email,'role'=>'reader','full_name'=>$name];
      header('Location: /index.php'); exit;
    } catch(Exception $e){
      $msg = 'Пользователь уже существует';
    }
  }
}
?>
<!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Регистрация</title>
<link rel="stylesheet" href="/assets/styles.css"/>
</head><body>
<?php include __DIR__ . '/_header.php'; ?>
<div class="container fade-in-up">
  <h1>Регистрация</h1>
  <?php if($msg): ?><div class="card" style="border-color:rgba(255,127,178,.35)"><?=$msg?></div><?php endif; ?>
  <form method="post" class="card" style="margin-top:12px;">
    <div class="row">
      <input class="input" name="full_name" placeholder="ФИО" required>
      <input class="input" name="ticket_no" placeholder="Номер билета (например: 12345)">
      <input class="input" name="email" placeholder="Email (логин)" required>
      <input class="input" type="password" name="password" placeholder="Пароль" required>
      <button class="btn" type="submit">Создать аккаунт</button>
    </div>
  </form>
</div>
</body></html>
