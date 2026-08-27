CREATE DATABASE IF NOT EXISTS perpustakaan;
USE perpustakaan;

-- Tabel Pengguna
CREATE TABLE pengguna (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  level ENUM('admin','petugas','siswa') NOT NULL,
  no_hp VARCHAR(20),
  alamat TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Buku
CREATE TABLE buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  penulis VARCHAR(100) NOT NULL,
  penerbit VARCHAR(100),
  tahun VARCHAR(4),
  isbn VARCHAR(30),
  jumlah INT NOT NULL DEFAULT 1,
  lokasi VARCHAR(50)
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  id_buku INT NOT NULL,
  tgl_pinjam DATE NOT NULL,
  tgl_kembali DATE,
  status ENUM('dipinjam','dikembalikan','terlambat') DEFAULT 'dipinjam',
  FOREIGN KEY (id_siswa) REFERENCES pengguna(id),
  FOREIGN KEY (id_buku) REFERENCES buku(id)
);

-- Data Awal
INSERT INTO pengguna (nama,username,password,level) VALUES
('Administrator','admin',MD5('admin123'),'admin'),
('Petugas Perpus','petugas',MD5('petugas123'),'petugas'),
('Siswa Satu','siswa',MD5('siswa123'),'siswa');