<?php
include "config.php";
cek_login();
if($_SESSION['level'] != "siswa"){
  header("Location: beranda.php");
  exit;
}
$id_siswa = $_SESSION['id'];
$pinjam = mysqli_query($koneksi, "SELECT peminjaman.*, buku.judul, buku.kode_buku FROM peminjaman JOIN buku ON peminjaman.id_buku = buku.id WHERE peminjaman.id_siswa='$id_siswa' AND peminjaman.status='dipinjam' ORDER BY peminjaman.tgl_pinjam DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pinjaman Saya</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    :root{--pink-soft:#F8C8DC;--pink-dark:#E84A7F;--pink-light:#FFF0F5;--text-dark:#5C2337;--merah:#C62828;--hijau:#2E7D32;--abu:#888;}
    body{background:var(--pink-light);display:flex;min-height:100vh;}
    .sidebar{width:260px;background:white;box-shadow:2px 0 10px rgba(232,74,127,0.15);position:fixed;height:100vh;overflow:auto;}
    .sidebar-header{background:linear-gradient(90deg,var(--pink-soft),var(--pink-dark));color:white;padding:15px;text-align:center;font-size:16px;font-weight:bold;}
    .sidebar-user{padding:15px;text-align:center;}
    .sidebar-user strong{color:var(--pink-dark);display:block;font-size:15px;}
    .sidebar-user small{color:var(--text-dark);font-size:12px;}
    .sidebar-menu{padding:10px 0;}
    .sidebar a{display:block;padding:12px 25px;color:var(--text-dark);text-decoration:none;transition:0.2s;margin:3px 0;border-left:3px solid transparent;}
    .sidebar a:hover,.sidebar a.aktif{background:var(--pink-light);border-left-color:var(--pink-dark);color:var(--pink-dark);font-weight:bold;}
    .sidebar a.logout{margin-top:10px;}
    .konten{margin-left:260px;padding:25px;width:calc(100% - 260px);}
    h2{color:var(--pink-dark);font-size:24px;margin-bottom:25px;}
    .card-daftar{background:white;padding:25px;border-radius:14px;box-shadow:0 4px 12px rgba(232,74,127,0.1);}
    table{width:100%;border-collapse:collapse;margin-top:10px;}
    th,td{border:1px solid #ddd;padding:10px;text-align:left;font-size:13px;}
    th{background:var(--pink-soft);color:var(--text-dark);text-align:center;}
    .tengah{text-align:center;}
    .telat{color:var(--merah);font-weight:bold;}
    .ok{color:var(--hijau);font-weight:bold;}
    .kosong{text-align:center;padding:40px;color:#999;}
  </style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-header">Perpustakaan</div>
  <div class="sidebar-user"><strong><?= $_SESSION['nama'] ?></strong><small>Siswa / Anggota</small></div>
  <div class="sidebar-menu">
    <a href="beranda_siswa.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'beranda_siswa.php' ? 'aktif' : ''; ?>">🏠 Beranda</a>
    <a href="katalog_buku.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'katalog_buku.php' ? 'aktif' : ''; ?>">📖 Katalog Buku</a>
    <a href="pinjaman_saya.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pinjaman_saya.php' ? 'aktif' : ''; ?>">📚 Pinjaman Saya</a>
    <a href="riwayat_saya.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'riwayat_saya.php' ? 'aktif' : ''; ?>">📋 Riwayat Peminjaman</a>
    <a href="ulasan_saya.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ulasan_saya.php' ? 'aktif' : ''; ?>">📊 Ulasan</a>
    <a href="logout.php" class="logout">🚪 Keluar</a>
  </div>
</div>

<div class="konten">
  <h2>📖 Pinjaman Saya</h2>
  <div class="card-daftar">
    <?php if(mysqli_num_rows($pinjam) > 0): ?>
    <table>
      <tr><th>No</th><th>Kode Buku</th><th>Judul Buku</th><th>Tgl Pinjam</th><th>Batas Kembali</th><th>Status</th></tr>
      <?php $no=1; $sekarang = date('Y-m-d'); while($p=mysqli_fetch_assoc($pinjam)):
        $status = strtotime($sekarang) > strtotime($p['tgl_kembali']) ? 'TERLAMBAT' : 'Sedang Dipinjam';
        $kelas = strtotime($sekarang) > strtotime($p['tgl_kembali']) ? 'telat' : 'ok';
      ?>
      <tr>
        <td class="tengah"><?= $no++ ?></td>
        <td><?= $p['kode_buku'] ?></td>
        <td><?= $p['judul'] ?></td>
        <td class="tengah"><?= date('d/m/Y', strtotime($p['tgl_pinjam'])) ?></td>
        <td class="tengah"><?= date('d/m/Y', strtotime($p['tgl_kembali'])) ?></td>
        <td class="tengah <?= $kelas ?>"><?= $status ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p class="kosong">Anda belum meminjam buku apa pun.<br>Silakan hubungi petugas untuk meminjam buku dari <a href="katalog_buku.php" style="color:var(--pink-dark);font-weight:bold;">Katalog Buku</a>.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>