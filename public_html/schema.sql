SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('reader','admin') NOT NULL DEFAULT 'reader',
  full_name VARCHAR(190) NOT NULL,
  ticket_no VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  genre VARCHAR(120) NOT NULL,
  isbn VARCHAR(32) NOT NULL UNIQUE,
  cover_color VARCHAR(20) DEFAULT 'mint',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_by INT NULL,
  processed_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (status),
  INDEX (user_id),
  INDEX (book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  issued_at DATETIME NOT NULL,
  due_at DATETIME NOT NULL,
  returned_at DATETIME NULL,
  extended_count INT NOT NULL DEFAULT 0,
  created_from_request_id INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (created_from_request_id) REFERENCES requests(id) ON DELETE SET NULL,
  INDEX (user_id, returned_at),
  INDEX (book_id, returned_at),
  INDEX (due_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO books (title, author, genre, isbn, cover_color) VALUES
('1984', 'Джордж Оруэлл', 'Антиутопия', '978-5-17-090825-6', 'mint'),
('Мастер и Маргарита', 'Михаил Булгаков', 'Классика', '978-5-17-090826-3', 'pink'),
('Гарри Поттер', 'Дж. К. Роулинг', 'Фэнтези', '978-5-17-090827-0', 'mint'),
('Война и мир', 'Лев Толстой', 'Классика', '978-5-17-090828-7', 'pink'),
('Три товарища', 'Эрих Мария Ремарк', 'Проза', '978-5-17-090829-4', 'mint'),
('Алхимик', 'Пауло Коэльо', 'Философия', '978-5-17-090830-0', 'pink'),
('Убить пересмешника', 'Харпер Ли', 'Классика', '978-5-17-090831-7', 'mint'),
('Дюна', 'Фрэнк Герберт', 'Фантастика', '978-5-17-090832-4', 'pink')
ON DUPLICATE KEY UPDATE title=VALUES(title);
