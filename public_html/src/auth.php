<?php
declare(strict_types=1);

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function require_login(): void {
  if (!current_user()) {
    header('Location: /login.php');
    exit;
  }
}

function require_admin(): void {
  require_login();
  if ((current_user()['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Доступ запрещён";
    exit;
  }
}
