<?php
session_start();
include 'config.php';

if(!isset($_SESSION['sudah_login'])){
  header("Location: login.php");
  exit;
}

// Ambil kata kunci pencarian
$cari = "";
if(isset($_GET['cari'])){
  $cari = trim($_GET['cari']);
}

// Query data buku dengan pencarian
$where = "";
if(!empty($cari)){
  $where = "WHERE kode_buku LIKE '%$cari%' 
            OR judul LIKE '%$cari%' 
            OR penulis LIKE '%$cari%' 
            OR penerbit LIKE '%$cari%'
            OR kategori LIKE '%$cari%'";
}
$buku = mysqli_query($koneksi, "SELECT * FROM buku $where ORDER BY kode_buku ASC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Katalog Buku - Perpustakaan SMK N 7 YOGYAKARTA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    :root{--pink-soft:#F8E8EE;--pink-main:#F4A7B9;--pink-dark:#D885A3;--text-dark:#5C2337;--abu:#888;--putih:#ffffff;--shadow:rgba(216,133,163,0.15);}
    body{background:var(--pink-soft);display:flex;min-height:100vh;}
    .sidebar{width:260px;background:var(--putih);box-shadow:2px 0 12px var(--shadow);position:fixed;height:100vh;z-index:99;}
    .sidebar-header{background:linear-gradient(90deg,var(--pink-soft),var(--pink-main));color:white;padding:18px;text-align:center;font-size:17px;font-weight:bold;}
    .sidebar-user{padding:20px;text-align:center;border-bottom:1px solid #f0f0f0;}
    .sidebar-user strong{color:var(--pink-dark);display:block;font-size:16px;}
    .sidebar-user small{color:var(--abu);font-size:13px;}
    .sidebar-menu{padding:15px 0;}
    .sidebar a{display:block;padding:13px 25px;color:var(--text-dark);text-decoration:none;transition:0.25s;margin:4px 0;border-left:3px solid transparent;}
    .sidebar a:hover,.sidebar a.active{background:var(--pink-soft);border-left-color:var(--pink-dark);color:var(--pink-dark);font-weight:600;}
    .sidebar a.logout{margin-top:30px;}
    .konten{margin-left:260px;padding:30px;width:calc(100% - 260px);}
    h2{color:var(--pink-dark);font-size:28px;margin-bottom:10px;font-weight:700;}
    .card{border:none;border-radius:14px;box-shadow:0 4px 12px var(--shadow);background:var(--putih);}
    .table th{background:var(--pink-soft);color:var(--pink-dark);border:none;vertical-align:middle;}
    .table td{vertical-align:middle;}
    .badge-ada{background:#81C784;color:white;}
    .badge-habis{background:#EF5350;color:white;}
    .btn-pink{background:var(--pink-main);color:white;border:none;}
    .btn-pink:hover{background:var(--pink-dark);color:white;}
    .form-control:focus{border-color:var(--pink-main);box-shadow:0 0 0 0.25rem rgba(244,167,185,0.25);}
    .sampul-buku{width:60px;height:80px;object-fit:cover;border-radius:6px;border:2px solid var(--pink-soft);}
    .tidak-ada-gambar{width:60px;height:80px;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;border-radius:6px;color:var(--pink-dark);font-size:20px;}
  </style>
</head>
<body>

<!-- SIDEBAR KIRI -->
<div class="sidebar">
  <div class="sidebar-header">PERPUSTAKAAN</div>
  <div class="sidebar-user">
    <strong><!-- SESUDAH (Aman) -->
<?php echo $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? 'Siswa'; ?></strong>
    <small>Siswa / Anggota</small>
  </div>
  <div class="sidebar-menu">
    <a href="beranda_siswa.php" class="aktif">🏠 Beranda</a>
    <a href="katalog_buku.php">📖 Katalog Buku</a>
    <a href="pinjaman_saya.php">📚 Pinjaman Saya</a>
    <a href="riwayat_peminjaman.php">📋 Riwayat Peminjaman</a>
    <a href="ulasan_saya.php">📊 Ulasan</a>
    <a href="logout.php" class="logout">🚪 Keluar</a>
  </div>
</div>

<!-- KONTEN UTAMA -->
<div class="konten">
  <h2>📚 Katalog Buku</h2>
  <p class="text-muted mb-4">Daftar buku yang tersedia di perpustakaan</p>

  <!-- FORM PENCARIAN -->
  <div class="card p-3 mb-4">
    <form method="GET" action="katalog_buku.php" class="row g-2 align-items-center">
      <div class="col-auto flex-grow-1">
        <input type="text" name="cari" class="form-control" placeholder="Cari buku berdasarkan kode, judul, penulis, penerbit..." value="<?php echo htmlspecialchars($cari); ?>">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-pink">🔍 Cari</button>
        <?php if(!empty($cari)){ ?>
          <a href="katalog_buku.php" class="btn btn-secondary">↩️ Reset</a>
        <?php } ?>
      </div>
    </form>
  </div>

  <div class="card p-4">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th width="5%">No</th>
            <th>Sampul</th>
            <th>Kode Buku</th>
            <th>Judul Buku</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>Jumlah</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $no = 1;
          while($data = mysqli_fetch_assoc($buku)){ 
            // Pastikan jumlah tidak kurang dari 0
            $jumlah = max(0, $data['jumlah']);
          ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td>
              <?php if(!empty($data['gambar']) && file_exists("gambar_buku/".$data['gambar'])){ ?>
                <img src="gambar_buku/<?php echo $data['gambar']; ?>" alt="Sampul Buku" class="sampul-buku">
              <?php } else { ?>
                <div class="tidak-ada-gambar">📖</div>
              <?php } ?>
            </td>
            <td><?php echo $data['kode_buku']; ?></td>
            <td><?php echo $data['judul']; ?></td>
            <td><?php echo $data['penulis']; ?></td>
            <td><?php echo $data['penerbit']; ?></td>
            <td><?php echo $data['tahun']; ?></td>
            <td><strong><?php echo $jumlah; ?></strong></td>
            <td>
              <?php if($jumlah > 0){ ?>
                <span class="badge badge-ada">✅ Tersedia</span>
              <?php } else { ?>
                <span class="badge badge-habis">❌ Tidak Tersedia</span>
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
          <?php if(mysqli_num_rows($buku) == 0){ ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-4">
              <?php if(!empty($cari)){ ?>
                😞 Buku dengan kata kunci "<?php echo htmlspecialchars($cari); ?>" tidak ditemukan
              <?php } else { ?>
                📚 Belum ada data buku
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>