<?php
declare(strict_types=1);

function create_request(PDO $pdo, int $userId, int $bookId): array {
  $stmt = $pdo->prepare("SELECT COUNT(*) c FROM loans WHERE book_id=? AND returned_at IS NULL");
  $stmt->execute([$bookId]);
  if ((int)$stmt->fetch()['c'] > 0) return ['ok'=>false,'msg'=>'Книга уже выдана'];

  $stmt = $pdo->prepare("SELECT COUNT(*) c FROM requests WHERE user_id=? AND book_id=? AND status='pending'");
  $stmt->execute([$userId,$bookId]);
  if ((int)$stmt->fetch()['c'] > 0) return ['ok'=>false,'msg'=>'Заявка уже отправлена администратору'];

  $stmt = $pdo->prepare("INSERT INTO requests(user_id, book_id) VALUES(?,?)");
  $stmt->execute([$userId,$bookId]);
  return ['ok'=>true,'msg'=>'Заявка отправлена администратору'];
}

function approve_request(PDO $pdo, int $adminId, int $requestId, int $days=14): array {
  $pdo->beginTransaction();

  $r = $pdo->prepare("SELECT * FROM requests WHERE id=? FOR UPDATE");
  $r->execute([$requestId]);
  $req = $r->fetch();
  if (!$req || $req['status'] !== 'pending') { $pdo->rollBack(); return ['ok'=>false,'msg'=>'Заявка не найдена/уже обработана']; }

  $stmt = $pdo->prepare("SELECT COUNT(*) c FROM loans WHERE book_id=? AND returned_at IS NULL FOR UPDATE");
  $stmt->execute([(int)$req['book_id']]);
  if ((int)$stmt->fetch()['c'] > 0) { $pdo->rollBack(); return ['ok'=>false,'msg'=>'Книга уже выдана']; }

  $upd = $pdo->prepare("UPDATE requests SET status='approved', processed_by=?, processed_at=NOW() WHERE id=?");
  $upd->execute([$adminId,$requestId]);

  $ins = $pdo->prepare("
    INSERT INTO loans(user_id, book_id, issued_at, due_at, created_from_request_id)
    VALUES(?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), ?)
  ");
  $ins->execute([(int)$req['user_id'], (int)$req['book_id'], $days, $requestId]);

  $pdo->commit();
  return ['ok'=>true,'msg'=>'Книга выдана'];
}

function reject_request(PDO $pdo, int $adminId, int $requestId): array {
  $stmt = $pdo->prepare("UPDATE requests SET status='rejected', processed_by=?, processed_at=NOW() WHERE id=? AND status='pending'");
  $stmt->execute([$adminId,$requestId]);
  return ['ok'=>($stmt->rowCount()>0),'msg'=>($stmt->rowCount()>0?'Заявка отклонена':'Не удалось отклонить')];
}

function extend_loan(PDO $pdo, int $loanId, int $days): array {
  $stmt = $pdo->prepare("
    UPDATE loans
    SET due_at = DATE_ADD(due_at, INTERVAL ? DAY),
        extended_count = extended_count + 1
    WHERE id=? AND returned_at IS NULL
  ");
  $stmt->execute([$days, $loanId]);
  return ['ok'=>($stmt->rowCount()>0),'msg'=>($stmt->rowCount()>0?'Срок продлён':'Не удалось продлить')];
}

function accept_return(PDO $pdo, int $loanId): array {
  $stmt = $pdo->prepare("UPDATE loans SET returned_at=NOW() WHERE id=? AND returned_at IS NULL");
  $stmt->execute([$loanId]);
  return ['ok'=>($stmt->rowCount()>0),'msg'=>($stmt->rowCount()>0?'Книга принята':'Не удалось принять')];
}

function add_book(PDO $pdo, array $b): array {
  $stmt = $pdo->prepare("INSERT INTO books(title,author,genre,isbn,cover_color) VALUES(?,?,?,?,?)");
  $stmt->execute([
    trim($b['title'] ?? ''),
    trim($b['author'] ?? ''),
    trim($b['genre'] ?? ''),
    trim($b['isbn'] ?? ''),
    ($b['cover_color'] ?? 'mint'),
  ]);
  return ['ok'=>true,'msg'=>'Книга добавлена'];
}
