<?php
require __DIR__ . '/../src/config.php';

// Создаёт/обновляет админа. Откройте 1 раз и удалите файл.
$email = 'admin@site.ru';
$pass  = 'Admin123!';
$name  = 'Администратор';

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
  INSERT INTO users(email,password_hash,role,full_name,ticket_no)
  VALUES(?,?,?,?,NULL)
  ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role='admin', full_name=VALUES(full_name)
");
$stmt->execute([$email,$hash,'admin',$name]);

echo "OK. Admin: $email / $pass. Удалите tools/make_admin.php";
