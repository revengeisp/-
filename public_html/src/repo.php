<?php
declare(strict_types=1);

function overdue_count(PDO $pdo, int $userId): int {
  $stmt = $pdo->prepare("
    SELECT COUNT(*) c
    FROM loans
    WHERE user_id=? AND returned_at IS NULL AND due_at < NOW()
  ");
  $stmt->execute([$userId]);
  return (int)$stmt->fetch()['c'];
}

function catalog(PDO $pdo, string $q = ''): array {
  $qLike = '%' . $q . '%';
  $stmt = $pdo->prepare("
    SELECT b.*,
      EXISTS(
        SELECT 1 FROM loans l
        WHERE l.book_id=b.id AND l.returned_at IS NULL
      ) AS is_issued
    FROM books b
    WHERE (? = '' OR b.title LIKE ? OR b.author LIKE ? OR b.genre LIKE ? OR b.isbn LIKE ?)
    ORDER BY b.id DESC
  ");
  $stmt->execute([$q, $qLike, $qLike, $qLike, $qLike]);
  return $stmt->fetchAll();
}

function advanced_search(PDO $pdo, array $f): array {
  $title = trim($f['title'] ?? '');
  $author = trim($f['author'] ?? '');
  $genre = trim($f['genre'] ?? '');
  $isbn = trim($f['isbn'] ?? '');

  $stmt = $pdo->prepare("
    SELECT b.*,
      EXISTS(SELECT 1 FROM loans l WHERE l.book_id=b.id AND l.returned_at IS NULL) AS is_issued
    FROM books b
    WHERE ( ?='' OR b.title LIKE CONCAT('%', ?, '%') )
      AND ( ?='' OR b.author LIKE CONCAT('%', ?, '%') )
      AND ( ?='' OR b.genre  LIKE CONCAT('%', ?, '%') )
      AND ( ?='' OR b.isbn   LIKE CONCAT('%', ?, '%') )
    ORDER BY b.title
  ");
  $stmt->execute([$title,$title,$author,$author,$genre,$genre,$isbn,$isbn]);
  return $stmt->fetchAll();
}

function my_loans(PDO $pdo, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT l.*, b.title, b.author,
      (l.returned_at IS NULL AND l.due_at < NOW()) AS is_overdue
    FROM loans l
    JOIN books b ON b.id=l.book_id
    WHERE l.user_id=? AND l.returned_at IS NULL
    ORDER BY l.due_at ASC
  ");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function pending_requests(PDO $pdo): array {
  $stmt = $pdo->query("
    SELECT r.*, u.full_name, u.ticket_no, u.email, b.title, b.author, b.isbn
    FROM requests r
    JOIN users u ON u.id=r.user_id
    JOIN books b ON b.id=r.book_id
    WHERE r.status='pending'
    ORDER BY r.created_at ASC
  ");
  return $stmt->fetchAll();
}

function admin_users_with_loans(PDO $pdo): array {
  $stmt = $pdo->query("
    SELECT u.id, u.full_name, u.ticket_no, u.email,
      SUM(l.returned_at IS NULL) AS active_cnt,
      SUM(l.returned_at IS NULL AND l.due_at < NOW()) AS overdue_cnt
    FROM users u
    LEFT JOIN loans l ON l.user_id=u.id
    GROUP BY u.id
    ORDER BY overdue_cnt DESC, active_cnt DESC, u.full_name
  ");
  return $stmt->fetchAll();
}

function admin_active_loans_for_user(PDO $pdo, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT l.*, b.title, b.author,
      (l.due_at < NOW()) AS is_overdue
    FROM loans l
    JOIN books b ON b.id=l.book_id
    WHERE l.user_id=? AND l.returned_at IS NULL
    ORDER BY l.due_at ASC
  ");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function stats_global(PDO $pdo): array {
  $stmt = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM books) AS total_books,
      (SELECT COUNT(*) FROM loans WHERE returned_at IS NULL) AS issued_books,
      (SELECT COUNT(*) FROM loans WHERE returned_at IS NULL AND due_at < NOW()) AS overdue_books
  ");
  $row = $stmt->fetch();
  return [
    'total_books'  => (int)$row['total_books'],
    'issued_books' => (int)$row['issued_books'],
    'overdue_books'=> (int)$row['overdue_books'],
  ];
}

function stats_for_user(PDO $pdo, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT
      (SELECT COUNT(*) FROM loans WHERE user_id=? AND returned_at IS NULL) AS my_active,
      (SELECT COUNT(*) FROM loans WHERE user_id=? AND returned_at IS NULL AND due_at < NOW()) AS my_overdue
  ");
  $stmt->execute([$userId, $userId]);
  $row = $stmt->fetch();
  return [
    'my_active'  => (int)$row['my_active'],
    'my_overdue' => (int)$row['my_overdue'],
  ];
}
