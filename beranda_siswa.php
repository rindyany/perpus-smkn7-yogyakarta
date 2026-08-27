<?php
session_start();
include "config.php";
cek_login();
if($_SESSION['level'] != "siswa"){
  die("<script>alert('Akses Ditolak!');location.href='beranda.php';</script>");
}

$id_saya = $_SESSION['id'];

// Hitung data OTOMATIS untuk siswa yang login
$jumlah_buku = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku"));
$sedang_pinjam = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_siswa='$id_saya' AND status='dipinjam'"));
$riwayat_selesai = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_siswa='$id_saya' AND status='dikembalikan'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Beranda Siswa - Perpustakaan</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    
    /* === WARNA ROSE PINK === */
    :root{
      --pink-soft: #F8C8DC;
      --pink-dark: #E84A7F;
      --pink-light: #FFF0F5;
      --text-dark: #5C2337;
      --hijau: #81C784;
      --kuning: #FFD166;
      --ungu: #B794F4;
      --putih: #FFFFFF;
      --bayangan: rgba(232,74,127,0.15);
    }

    body{
      background: var(--pink-light);
      display: flex;
      min-height: 100vh;
    }

    /* === SIDEBAR KIRI === */
    .sidebar{
      width: 260px;
      background: var(--putih);
      box-shadow: 2px 0 10px var(--bayangan);
      position: fixed;
      height: 100vh;
      overflow: auto;
    }

    .sidebar-header{
      background: linear-gradient(90deg, var(--pink-soft), var(--pink-dark));
      color: white;
      padding: 15px;
      text-align: center;
      font-size: 16px;
      font-weight: bold;
    }

    .sidebar-user{
      padding: 20px 15px;
      text-align: center;
      border-bottom: 1px solid #f0c8d8;
    }

    .sidebar-user strong{
      color: var(--pink-dark);
      display: block;
      font-size: 15px;
    }

    .sidebar-user small{
      color: var(--text-dark);
      font-size: 12px;
    }

    .sidebar-menu{
      padding: 15px 0;
    }

    .sidebar a{
      display: block;
      padding: 12px 25px;
      color: var(--text-dark);
      text-decoration: none;
      transition: 0.2s;
      margin: 3px 0;
      border-left: 3px solid transparent;
    }

    .sidebar a:hover, .sidebar a.aktif{
      background: var(--pink-light);
      border-left-color: var(--pink-dark);
      color: var(--pink-dark);
      font-weight: bold;
    }

    .sidebar a.logout{
      margin-top: 30px;
    }

    /* === KONTEN UTAMA === */
    .konten{
      margin-left: 260px;
      padding: 30px;
      width: calc(100% - 260px);
    }

    .selamat-datang{
      background: var(--putih);
      padding: 20px 25px;
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 2px 8px var(--bayangan);
    }

    .selamat-datang h2{
      color: var(--pink-dark);
      font-size: 20px;
      margin-bottom: 5px;
    }

    .selamat-datang p{
      color: var(--text-dark);
      font-size: 14px;
    }

    h3{
      color: var(--pink-dark);
      font-size: 18px;
      margin-bottom: 20px;
    }

    .grid-data{
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }

    .kotak{
      background: var(--putih);
      padding: 25px 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--bayangan);
      text-align: center;
    }

    .kotak .icon{
      font-size: 28px;
      margin-bottom: 10px;
    }

    .kotak.jumlah-buku .icon{ color: var(--pink-dark); }
    .kotak.pinjam .icon{ color: var(--hijau); }
    .kotak.selesai .icon{ color: var(--kuning); }
    .kotak.status .icon{ color: var(--ungu); }

    .kotak .label{
      color: var(--text-dark);
      font-size: 13px;
      margin-bottom: 8px;
    }

    .kotak .angka{
      color: var(--pink-dark);
      font-size: 30px;
      font-weight: bold;
    }

    .kotak.status .angka{
      font-size: 22px;
    }

    /* Responsive */
    @media(max-width: 900px){
      .grid-data{ grid-template-columns: repeat(2, 1fr); }
    }
    @media(max-width: 500px){
      .grid-data{ grid-template-columns: 1fr; }
      .sidebar{ position: relative; width: 100%; height: auto; }
      .konten{ margin-left: 0; width: 100%; }
    }
  </style>
</head>
<body>

<!-- SIDEBAR KIRI -->
<div class="sidebar">
  <div class="sidebar-header">📚 PERPUSTAKAAN</div>
  <div class="sidebar-user">
    <strong><?php echo $_SESSION['nama_lengkap'] ?? $_SESSION['username']; ?></strong>
    <small>Siswa / Anggota</small>
  </div>
  <div class="sidebar-menu">
    <a href="beranda_siswa.php" class="aktif">🏠 Beranda</a>
    <a href="katalog_buku.php">📖 Katalog Buku</a>
    <a href="pinjaman_saya.php">📚 Pinjaman Saya</a>
    <a href="riwayat_saya.php">📋 Riwayat Peminjaman</a>
    <a href="ulasan_saya.php">📊 Ulasan</a>
    <a href="logout.php" class="logout">🚪 Keluar</a>
  </div>
</div>

<!-- KONTEN UTAMA -->
<div class="konten">

  <div class="selamat-datang">
    <h2>👋 Selamat Datang, <?php echo $_SESSION['nama_lengkap'] ?? $_SESSION['username']; ?>!</h2>
    <p>Berikut informasi perpustakaan kamu hari ini.</p>
  </div>

  <h3>📊 Ringkasan Data</h3>

  <div class="grid-data">
    <div class="kotak jumlah-buku">
      <div class="icon">📚</div>
      <div class="label">Jumlah Buku</div>
      <div class="angka"><?php echo $jumlah_buku; ?></div>
    </div>

    <div class="kotak pinjam">
      <div class="icon">📖</div>
      <div class="label">Sedang Dipinjam</div>
      <div class="angka"><?php echo $sedang_pinjam; ?></div>
    </div>

    <div class="kotak selesai">
      <div class="icon">✅</div>
      <div class="label">Riwayat Selesai</div>
      <div class="angka"><?php echo $riwayat_selesai; ?></div>
    </div>

    <div class="kotak status">
      <div class="icon">👤</div>
      <div class="label">Status</div>
      <div class="angka">Siswa</div>
    </div>
  </div>

</div>

</body>
</html>