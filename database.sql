CREATE DATABASE IF NOT EXISTS warmindo CHARACTER SET utf8mb4;
USE warmindo;

DROP TABLE IF EXISTS detail_transaksi, transaksi, menu, kategori, bahan, users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir'
);

CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(50) NOT NULL
);

CREATE TABLE menu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_kategori INT NOT NULL,
  nama_menu VARCHAR(100) NOT NULL,
  harga INT NOT NULL,
  status ENUM('tersedia','habis') NOT NULL DEFAULT 'tersedia',
  FOREIGN KEY (id_kategori) REFERENCES kategori(id)
);

CREATE TABLE bahan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_bahan VARCHAR(100) NOT NULL,
  stok DECIMAL(10,2) NOT NULL DEFAULT 0,
  satuan VARCHAR(20) NOT NULL,
  stok_minimum DECIMAL(10,2) NOT NULL DEFAULT 5
);

CREATE TABLE transaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi VARCHAR(30) NOT NULL UNIQUE,
  id_user INT NOT NULL,
  tanggal DATETIME NOT NULL,
  total INT NOT NULL,
  bayar INT NOT NULL,
  kembalian INT NOT NULL,
  FOREIGN KEY (id_user) REFERENCES users(id)
);

CREATE TABLE detail_transaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT NOT NULL,
  id_menu INT NULL,
  nama_menu VARCHAR(100) NOT NULL,
  jumlah INT NOT NULL,
  harga_satuan INT NOT NULL,
  subtotal INT NOT NULL,
  FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
  FOREIGN KEY (id_menu) REFERENCES menu(id) ON DELETE SET NULL
);

-- Akun awal: admin / admin123 dan kasir / kasir123
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$gJU2zdJlPtqN7VbL30OyVuGDgTXEHnoitZKPOu195OGBcHM6lIG56', 'admin'),
('Kasir Motekar', 'kasir', '$2y$10$ghKHr5SaJR2fvkdVyBZRyercAamOLXWD38DSpOn52xnBRj5QLyBX6', 'kasir');

INSERT INTO kategori (nama_kategori) VALUES ('Makanan'), ('Minuman'), ('Tambahan');

INSERT INTO menu (id_kategori, nama_menu, harga, status) VALUES
(1, 'Indomie Goreng', 8000, 'tersedia'),
(1, 'Indomie Kuah', 8000, 'tersedia'),
(1, 'Indomie Goreng Telur', 11000, 'tersedia'),
(1, 'Indomie Kuah Telur', 11000, 'tersedia'),
(1, 'Nasi Telur', 10000, 'tersedia'),
(1, 'Nasi Sarden', 12000, 'tersedia'),
(2, 'Es Teh', 4000, 'tersedia'),
(2, 'Teh Hangat', 3000, 'tersedia'),
(2, 'Kopi Hitam', 4000, 'tersedia'),
(2, 'Susu Jahe', 6000, 'tersedia'),
(2, 'Es Jeruk', 5000, 'tersedia'),
(3, 'Telur Ceplok', 4000, 'tersedia'),
(3, 'Sosis Goreng', 3000, 'tersedia'),
(3, 'Kerupuk', 1000, 'tersedia');

INSERT INTO bahan (nama_bahan, stok, satuan, stok_minimum) VALUES
('Mi instan', 60, 'bungkus', 20),
('Telur', 40, 'butir', 15),
('Beras', 12, 'kg', 5),
('Teh celup', 80, 'kantong', 20),
('Kopi bubuk', 2, 'kg', 1),
('Gula pasir', 6, 'kg', 2),
('Sosis', 25, 'buah', 10);
