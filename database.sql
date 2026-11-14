CREATE DATABASE IF NOT EXISTS perpus1;
USE perpus1;

CREATE TABLE IF NOT EXISTS anggota (
  id_anggota VARCHAR(20) PRIMARY KEY,
  nama_anggota VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  kelas VARCHAR(30) NOT NULL
);

CREATE TABLE IF NOT EXISTS buku (
  id_buku VARCHAR(20) PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  pengarang VARCHAR(100) NOT NULL,
  penerbit VARCHAR(100) NOT NULL,
  tahun YEAR NOT NULL,
  stok INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS peminjaman (
  id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota VARCHAR(20) NOT NULL,
  id_buku VARCHAR(20) NOT NULL,
  tgl_pinjam DATE NOT NULL,
  tgl_kembali DATE,
  status ENUM('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota),
  FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
);

-- password: admin123
INSERT IGNORE INTO anggota VALUES ('0079457812','Rossi','admin','$2y$10$wH8Qm0pXo1l8Zr3yq9Zr2.uJv8Kq2vJ0nJ1kqk9mUQ1o6l0yq3b1K','XI RPL 7');

INSERT IGNORE INTO buku VALUES
  ('BK001','Laskar Pelangi','Andrea Hirata','Bentang Pustaka',2005,5),
  ('BK002','Bumi Manusia','Pramoedya Ananta Toer','Lentera Dipantara',1980,3),
  ('BK003','Negeri 5 Menara','Ahmad Fuadi','Gramedia',2009,4);
