# 📚 Sistem Informasi Perpustakaan SMK Negeri 7 Yogyakarta

Sistem Informasi Perpustakaan SMK Negeri 7 Yogyakarta adalah aplikasi berbasis web yang dibuat untuk membantu proses pengelolaan perpustakaan secara digital.

Aplikasi ini menyediakan fasilitas untuk mengelola data buku, pengguna, peminjaman, pengembalian, ulasan buku, serta berbagai aktivitas perpustakaan berdasarkan hak akses pengguna.

> **Website:** https://perpustakaansmkn7yogyakarta.free.je/index.php

---

## 🏫 Tentang SMK Negeri 7 Yogyakarta

Sistem ini ditujukan untuk mendukung kegiatan perpustakaan di **SMK Negeri 7 Yogyakarta**.

SMK Negeri 7 Yogyakarta merupakan sekolah menengah kejuruan negeri yang beralamat di Jl. Gowongan Kidul JT III/416, Kota Yogyakarta. Sekolah ini memiliki NPSN **20403295** dan berstatus akreditasi **A**.

Informasi mengenai sekolah juga tersedia pada website resmi SMK Negeri 7 Yogyakarta.

Website resmi sekolah:
https://www.smkn7jogja.sch.id/

---

# 🎯 Tujuan Sistem

Aplikasi ini dibuat untuk:

* Mempermudah pengelolaan koleksi buku.
* Mempermudah pencarian buku.
* Membantu proses peminjaman dan pengembalian buku.
* Mengelola data anggota perpustakaan.
* Memantau status peminjaman.
* Membantu petugas dalam mengelola transaksi perpustakaan.
* Memberikan informasi buku kepada siswa.
* Menyediakan fitur ulasan dan rating buku.
* Mengurangi proses pencatatan perpustakaan secara manual.

---

# 👥 Hak Akses Pengguna

Sistem memiliki tiga jenis pengguna:

| Role          | Hak Akses                                                                |
| ------------- | ------------------------------------------------------------------------ |
| 👨‍💼 Admin   | Mengelola pengguna dan data sistem                                       |
| 👩‍💼 Petugas | Mengelola proses perpustakaan dan peminjaman                             |
| 👨‍🎓 Siswa   | Melihat katalog, meminjam buku, melihat peminjaman dan memberikan ulasan |

Setiap pengguna memiliki menu dan hak akses yang disesuaikan dengan rolenya.

---

# 👨‍💼 Admin

Admin merupakan pengguna yang memiliki hak akses untuk mengelola data pengguna dan bagian administrasi sistem.

### Fitur Admin

* Dashboard
* Melihat statistik perpustakaan
* Kelola pengguna
* Tambah pengguna
* Edit pengguna
* Hapus pengguna
* Melihat data buku
* Melihat data peminjaman
* Melihat riwayat peminjaman
* Melihat profil pengguna
* Pengelolaan data sesuai hak akses sistem

Admin tidak digunakan untuk menjalankan proses operasional peminjaman seperti konfirmasi peminjaman atau pemeriksaan kondisi buku.

---

# 👩‍💼 Petugas

Petugas bertanggung jawab terhadap operasional perpustakaan.

### Fitur Petugas

* Dashboard petugas
* Melihat data buku
* Mengelola proses peminjaman
* Melakukan konfirmasi peminjaman
* Memproses pengembalian
* Mengecek kondisi buku
* Melihat daftar buku yang sedang dipinjam
* Melihat riwayat peminjaman
* Melihat profil petugas
* Mengelola aktivitas perpustakaan sesuai hak akses

Petugas berperan dalam memastikan proses peminjaman dan pengembalian berjalan dengan benar.

---

# 👨‍🎓 Siswa

Siswa dapat menggunakan sistem untuk mencari dan meminjam buku serta memantau aktivitas peminjamannya sendiri.

### Fitur Siswa

* Dashboard siswa
* Katalog buku
* Pencarian buku
* Detail buku
* Meminjam buku
* Melihat peminjaman saya
* Melihat status peminjaman
* Perpanjangan peminjaman
* Melihat riwayat peminjaman
* Memberikan rating buku
* Memberikan ulasan buku
* Melihat ulasan sendiri
* Melihat profil siswa

---

# 📖 Katalog Buku

Katalog buku digunakan untuk menampilkan koleksi buku yang tersedia di perpustakaan.

Informasi buku yang dapat ditampilkan antara lain:

* Kode buku
* Judul buku
* Penulis
* Penerbit
* Tahun terbit
* Kategori
* Jumlah/stok buku
* Sampul atau gambar buku

Siswa dapat menggunakan pencarian untuk menemukan buku yang dibutuhkan dengan lebih mudah.

---

# 🔍 Pencarian Buku

Sistem menyediakan fitur pencarian buku sehingga pengguna dapat menemukan koleksi berdasarkan informasi buku yang tersedia.

Pencarian membantu pengguna menemukan buku tanpa harus melihat seluruh daftar koleksi secara manual.

---

# 📚 Detail Buku

Halaman detail buku menampilkan informasi yang lebih lengkap mengenai sebuah buku.

Informasi yang tersedia dapat meliputi:

* Judul
* Penulis
* Penerbit
* Tahun
* Kategori
* Ketersediaan buku
* Sampul buku
* Informasi lainnya

Untuk siswa yang memiliki akses, tersedia tombol untuk mengajukan peminjaman.

---

# 📥 Sistem Peminjaman

Alur peminjaman buku pada sistem:

```text
Siswa memilih buku
       ↓
Siswa mengajukan peminjaman
       ↓
Status = DIPROSES
       ↓
Petugas melakukan konfirmasi
       ↓
Status = DIPINJAM
       ↓
Siswa mengembalikan buku
       ↓
Petugas memproses pengembalian
       ↓
Status = DIKEMBALIKAN
```

Dengan alur tersebut, pengajuan peminjaman tidak langsung dianggap sebagai peminjaman aktif sebelum dikonfirmasi oleh petugas.

---

# 🔄 Status Peminjaman

Sistem menggunakan beberapa status peminjaman:

### `diproses`

Menandakan bahwa siswa telah mengajukan peminjaman tetapi masih menunggu konfirmasi petugas.

### `dipinjam`

Menandakan bahwa peminjaman telah dikonfirmasi oleh petugas dan buku sedang dipinjam oleh siswa.

### `dikembalikan`

Menandakan bahwa buku telah dikembalikan dan proses pengembalian telah selesai.

---

# 📅 Durasi Peminjaman

Saat siswa melakukan pengajuan peminjaman, sistem dapat menentukan:

* Tanggal pinjam secara otomatis.
* Tanggal kembali secara otomatis.
* Batas waktu peminjaman selama **7 hari**.

Contoh:

```text
Tanggal Pinjam : 01 September 2026
Tanggal Kembali: 08 September 2026
```

Tanggal tersebut digunakan sebagai acuan dalam proses peminjaman dan pengembalian.

---

# 🔁 Perpanjangan Peminjaman

Sistem menyediakan fitur perpanjangan peminjaman.

Peminjaman dapat diperpanjang maksimal:

```text
2 kali
```

Fitur ini digunakan ketika siswa masih membutuhkan buku yang sedang dipinjam.

---

# 💰 Denda

Sistem juga mendukung pencatatan denda apabila buku dikembalikan melewati batas waktu peminjaman.

Ketentuan denda yang digunakan:

```text
Rp1.000 / hari keterlambatan
```

Besarnya denda dihitung berdasarkan jumlah hari keterlambatan.

---

# ↩️ Pengembalian Buku

Ketika siswa ingin mengembalikan buku, proses pengembalian dilakukan melalui sistem.

Alurnya:

```text
Siswa mengajukan pengembalian
          ↓
Permintaan diproses
          ↓
Petugas memeriksa buku
          ↓
Petugas mengonfirmasi
          ↓
Status = dikembalikan
```

Petugas juga dapat melakukan pemeriksaan terhadap kondisi buku.

---

# 📝 Ulasan dan Rating

Siswa dapat memberikan ulasan terhadap buku yang telah dipinjam.

Fitur ulasan terdiri dari:

* Rating buku
* Komentar
* Daftar ulasan
* Ulasan saya
* Pencarian ulasan

Sistem dapat membatasi pemberian ulasan sehingga siswa hanya dapat memberikan ulasan terhadap buku yang pernah dipinjam sesuai aturan sistem.

---

# 👤 Profil Pengguna

Sistem menyediakan halaman profil berdasarkan pengguna yang sedang login.

Profil dapat menampilkan informasi seperti:

* Nama
* Username
* Nomor identitas/NIS
* Kelas
* Jurusan
* Nomor HP
* Alamat
* Role pengguna

Profil ditampilkan sesuai akun yang sedang login.

---

# 🗃️ Struktur Database

Sistem menggunakan database MySQL.

Beberapa tabel utama yang digunakan:

```text
buku
pengguna
peminjaman
ulasan
```

### Tabel `buku`

Digunakan untuk menyimpan data koleksi buku.

Contoh field:

```text
id
kode_buku
judul
penulis
penerbit
tahun
kategori
jumlah
gambar
sampul
```

### Tabel `pengguna`

Digunakan untuk menyimpan data pengguna sistem.

Contoh field:

```text
id
nis
nama
kelas
jurusan
level
username
password
no_hp
alamat
```

### Tabel `peminjaman`

Digunakan untuk menyimpan transaksi peminjaman buku.

Contoh field:

```text
id
id_siswa
id_buku
id_pengguna
tgl_pinjam
tgl_kembali
status
jumlah_perpanjang
denda
```

### Tabel `ulasan`

Digunakan untuk menyimpan rating dan komentar pengguna.

Contoh field:

```text
id
id_buku
id_pengguna
rating
komentar
dibuat_pada
```

---

# 🔐 Sistem Login

Sistem menggunakan session PHP untuk mengatur autentikasi dan hak akses pengguna.

Informasi role disimpan pada session:

```php
$_SESSION['level']
```

Role yang digunakan:

```text
admin
petugas
siswa
```

Halaman tertentu akan melakukan pengecekan hak akses sebelum dapat digunakan.

Contoh:

```php
cek_login();
```

Dengan adanya sistem hak akses, pengguna tidak dapat menggunakan halaman yang tidak diperuntukkan bagi rolenya.

---

# 🛠️ Teknologi yang Digunakan

Aplikasi dibangun menggunakan beberapa teknologi:

* **PHP** — bahasa pemrograman sisi server
* **MySQL** — database
* **HTML5** — struktur halaman
* **CSS3** — tampilan antarmuka
* **JavaScript** — interaksi pada halaman
* **Session PHP** — autentikasi dan pengelolaan login
* **Web Hosting** — publikasi aplikasi secara online

---

# 📂 Contoh Struktur Project

Struktur project secara umum:

```text
perpustakaan/
│
├── index.php
├── login.php
├── logout.php
├── config.php
│
├── beranda.php
├── beranda_siswa.php
│
├── buku.php
├── buku_tambah.php
├── katalog_buku.php
├── detail_buku_siswa.php
│
├── pengguna.php
├── pengguna_ubah.php
│
├── daftar_dipinjam.php
├── pinjaman_saya.php
├── riwayat_peminjaman.php
│
├── ulasan_saya.php
├── cari_ulasan.php
│
├── cetak_buku.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
└── README.md
```

> Struktur folder dapat berbeda sesuai versi project yang digunakan.

---

# ⚙️ Instalasi

## 1. Clone atau Download Project

Download project kemudian letakkan pada server lokal.

Contoh menggunakan XAMPP:

```text
C:\xampp\htdocs\perpustakaan
```

---

## 2. Buat Database

Buat database MySQL melalui phpMyAdmin.

Contoh:

```sql
CREATE DATABASE perpustakaan;
```

Kemudian import struktur database yang digunakan oleh project.

---

## 3. Konfigurasi Database

Sesuaikan file:

```text
config.php
```

dengan konfigurasi MySQL pada server.

Contoh:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "perpustakaan";
```

Sesuaikan nilai tersebut dengan konfigurasi server masing-masing.

---

## 4. Jalankan Project

Jika menggunakan XAMPP, aktifkan:

```text
Apache
MySQL
```

Kemudian buka:

```text
http://localhost/perpustakaan/
```

---

# 🌐 Versi Online

Project juga tersedia secara online:

[Sistem Informasi Perpustakaan SMK Negeri 7 Yogyakarta](https://perpustakaansmkn7yogyakarta.free.je/index.php?utm_source=chatgpt.com)

---

# 📱 Responsive Design

Sistem dirancang agar dapat digunakan melalui berbagai ukuran layar, termasuk:

* 💻 Desktop
* 💻 Laptop
* 📱 Smartphone
* 📱 Tablet

Tampilan halaman menggunakan CSS responsive sehingga beberapa bagian antarmuka dapat menyesuaikan ukuran layar perangkat.

---

# 🔒 Keamanan

Sistem menerapkan beberapa mekanisme dasar keamanan, antara lain:

* Login menggunakan session.
* Pembatasan akses berdasarkan role.
* Validasi akses pada halaman tertentu.
* Penggunaan escaping pada input database.
* Redirect pengguna ketika tidak memiliki hak akses.

Untuk penggunaan pada lingkungan produksi, keamanan aplikasi tetap perlu ditingkatkan, terutama pada:

* Password hashing.
* Validasi input.
* Prepared statement.
* Perlindungan CSRF.
* Validasi upload file.
* Pengamanan konfigurasi database.

---

# 🚀 Pengembangan Selanjutnya

Beberapa pengembangan yang dapat ditambahkan:

* 🔔 Sistem notifikasi peminjaman.
* 📊 Statistik perpustakaan yang lebih lengkap.
* 📈 Grafik peminjaman.
* 📧 Notifikasi melalui email.
* 📱 Progressive Web App (PWA).
* 🖨️ Laporan peminjaman yang lebih lengkap.
* 📚 QR Code untuk buku.
* 🔎 Pencarian buku yang lebih cepat.
* 📦 Manajemen stok buku yang lebih detail.
* 🔐 Peningkatan keamanan autentikasi.
* 📋 Export data ke Excel/PDF.

---

# 🏫 Informasi Sekolah

**SMK Negeri 7 Yogyakarta**

Alamat:

```text
Jl. Gowongan Kidul JT III/416
Kota Yogyakarta
D.I. Yogyakarta
```

NPSN:

```text
20403295
```

Status:

```text
Negeri
```

Akreditasi:

```text
A
```

Informasi tersebut tercantum pada data resmi satuan pendidikan SMKN 7 Yogyakarta.

Website resmi sekolah:

[SMK Negeri 7 Yogyakarta](https://www.smkn7jogja.sch.id/?utm_source=chatgpt.com)

---

# 👨‍💻 Pengembang

**Sistem Informasi Perpustakaan
SMK Negeri 7 Yogyakarta**

Project ini dikembangkan sebagai sistem informasi perpustakaan berbasis web untuk membantu digitalisasi pengelolaan perpustakaan sekolah.

---

# 📄 Lisensi

Project ini dibuat untuk kebutuhan sistem informasi perpustakaan sekolah.

Penggunaan, pengembangan, dan distribusi source code dapat disesuaikan dengan kebutuhan dan kebijakan pemilik project.

---

## ⭐ Sistem Informasi Perpustakaan SMK Negeri 7 Yogyakarta

> **Mendukung pengelolaan perpustakaan yang lebih mudah, terstruktur, dan digital.**
