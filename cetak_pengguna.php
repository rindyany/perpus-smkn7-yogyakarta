<?php
include "config.php";
cek_login();
if($_SESSION['level']!="admin"){
  die("<script>alert('Akses Ditolak!');location.href='beranda.php';</script>");
}

// Ambil pilihan filter
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : 'semua';
$filter_jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : 'semua';

// Susun query filter
$where = "";
if($filter_kelas!="semua") $where .= " AND kelas='$filter_kelas'";
if($filter_jurusan!="semua") $where .= " AND jurusan='$filter_jurusan'";
if(!empty($where)) $where = "WHERE ".ltrim($where," AND ");

// Ambil data sesuai filter + urutan yang benar
$pengguna = mysqli_query($koneksi, "SELECT * FROM pengguna $where 
  ORDER BY 
    CASE level 
      WHEN 'admin' THEN 1 
      WHEN 'petugas' THEN 2 
      WHEN 'siswa' THEN 3 
    END,
    CASE jurusan 
      WHEN 'RPL' THEN 1 
      WHEN 'NK' THEN 2 
      ELSE 3 
    END,
    CASE kelas 
      WHEN 'X' THEN 1 
      WHEN 'XI' THEN 2 
      WHEN 'XII' THEN 3 
    END, 
    nama ASC");

// Ambil daftar jurusan untuk pilihan
$jurusan_list = mysqli_query($koneksi, "SELECT DISTINCT jurusan FROM pengguna WHERE jurusan!='' ORDER BY 
  CASE jurusan WHEN 'RPL' THEN 1 WHEN 'NK' THEN 2 ELSE 3 END, jurusan");

// Tanggal cetak otomatis
$tgl_lengkap = date('d F Y');
$tgl_singkat = date('d F Y H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Data Pengguna</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    :root{--pink-soft:#F8C8DC;--pink-dark:#E84A7F;--pink-light:#FFF0F5;--text-dark:#5C2337;--merah:#C62828;}
    body{background:var(--pink-light);display:flex;min-height:100vh;}

    /* ✅ SIDEBAR KIRI */
    .sidebar{width:260px;background:white;box-shadow:2px 0 10px rgba(232,74,127,0.15);position:fixed;height:100vh;overflow:auto;}
    .sidebar h2{padding:20px;text-align:center;background:linear-gradient(135deg,var(--pink-soft),var(--pink-dark));color:white;font-size:18px;}
    .sidebar .user{padding:15px;text-align:center;border-bottom:1px solid var(--pink-soft);}
    .sidebar .user strong{color:var(--pink-dark);}
    .sidebar .user small{color:var(--text-dark);}
    .sidebar a{display:block;padding:13px 20px;color:var(--text-dark);text-decoration:none;transition:0.2s;border-left:4px solid transparent;}
    .sidebar a:hover,.sidebar a.aktif{background:var(--pink-light);border-left-color:var(--pink-dark);color:var(--pink-dark);font-weight:bold;}
    .sidebar a.logout{margin-top:20px;color:var(--merah);}

    .konten{margin-left:260px;padding:25px;width:100%;}

    /* ---- KEPALA LAPORAN ---- */
    .kepala{text-align:center;margin-bottom:25px;}
    .kepala h2{margin-bottom:5px;letter-spacing:1px;}
    .kepala p{color:#444;}

    /* ---- PILIHAN FILTER ---- */
    .form-pilih{background:#f9f0f5;padding:18px;border-radius:10px;margin-bottom:25px;}
    .form-pilih label{font-weight:bold;margin-right:8px;}
    select{padding:8px 10px;border-radius:6px;border:1px solid #E84A7F;}
    .btn-lihat{background:#E84A7F;color:white;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-weight:bold;margin-left:10px;}
    .btn-lihat:hover{background:#D6386C;}

    /* ---- TABEL DATA ---- */
    table{width:100%;border-collapse:collapse;margin-top:10px;}
    th,td{border:1px solid #333;padding:10px;text-align:left;font-size:13px;}
    th{background:#f0d9e3;text-align:center;}
    td:nth-child(1),td:nth-child(6),td:nth-child(7),td:nth-child(8){text-align:center;}

    /* ---- TANDA TANGAN ---- */
    .tanda-tangan{margin-top:60px;width:100%;}
    .kiri{float:left;width:48%;}
    .kanan{float:right;width:48%;text-align:right;}
    .garis-ttd{margin-top:70px;border-bottom:1px solid #000;width:240px;}
    .kiri .garis-ttd{margin-left:0;}
    .kanan .garis-ttd{margin-left:auto;}
    .nama-jabatan{margin-top:5px;}

    /* ---- TOMBOL ---- */
    .tombol{clear:both;margin-top:40px;text-align:center;}
    .btn-cetak{padding:10px 25px;font-size:15px;cursor:pointer;background:#E84A7F;color:white;border:none;border-radius:8px;font-weight:bold;}
    .btn-cetak:hover{background:#D6386C;}
    .kembali{display:inline-block;margin-top:15px;color:#E84A7F;text-decoration:none;}

    /* ✅ SAAT DICETAK → SIDEBAR & TOMBOL HILANG DARI KERTAS */
    @media print{
      .sidebar,.form-pilih,.tombol,.kembali{display:none !important;}
      .konten{margin-left:0 !important;padding:10px !important;}
    }
  </style>
</head>
<body>

<!-- ✅ SIDEBAR KIRI SUDAH ADA LENGKAP -->
<div class="sidebar">
  <h2>📚 Perpustakaan</h2>
  <div class="user">
    <strong><?=$_SESSION['nama']?></strong><br>
    <small><?=ucfirst($_SESSION['level'])?></small>
  </div>
  <a href="beranda.php">🏠 Beranda</a>
  <a href="pengguna.php" class="aktif">👤 Kelola Pengguna</a>
  <a href="buku.php">📚 Kelola Buku</a>
  <a href="peminjaman.php">📖 Transaksi Pinjam</a>
  <a href="laporan.php">📊 Laporan</a>
  <a href="cari_ulasan.php">🔍 Pencarian Ulasan</a>
  <a href="logout.php" class="logout">🚪 Keluar</a>
</div>

<div class="konten">

  <!-- KEPALA LAPORAN -->
  <div class="kepala">
    <h2>PERPUSTAKAAN SEKOLAH SMK N 7 YOGYAKARTA</h2>
    <h3>LAPORAN DATA PENGGUNA</h3>
    <p>Dicetak Tanggal: <?= $tgl_singkat ?></p>
  </div>

  <!-- PILIHAN FILTER -->
  <div class="form-pilih">
    <form method="get">
      <label>Pilih Kelas:</label>
      <select name="kelas" onchange="this.form.submit()">
        <option value="semua" <?= ($filter_kelas=="semua")?"selected":"" ?>>Semua Kelas</option>
        <option value="X" <?= ($filter_kelas=="X")?"selected":"" ?>>Kelas 10</option>
        <option value="XI" <?= ($filter_kelas=="XI")?"selected":"" ?>>Kelas 11</option>
        <option value="XII" <?= ($filter_kelas=="XII")?"selected":"" ?>>Kelas 12</option>
      </select>

      <label style="margin-left:20px;">Pilih Jurusan:</label>
      <select name="jurusan" onchange="this.form.submit()">
        <option value="semua" <?= ($filter_jurusan=="semua")?"selected":"" ?>>Semua Jurusan</option>
        <?php while($jur=mysqli_fetch_assoc($jurusan_list)): ?>
        <option value="<?=$jur['jurusan']?>" <?= ($filter_jurusan==$jur['jurusan'])?"selected":"" ?>>
          <?=$jur['jurusan']?>
        </option>
        <?php endwhile; ?>
      </select>

      <button type="button" class="btn-lihat" onclick="location.href='cetak_pengguna.php'">Tampilkan Semua</button>
    </form>
  </div>

  <!-- TABEL DATA -->
  <table>
    <tr>
      <th>No</th>
      <th>NIS</th>
      <th>Nama Lengkap</th>
      <th>Alamat</th>
      <th>No. HP</th>
      <th>Kelas</th>
      <th>Jurusan</th>
      <th>Level</th>
    </tr>
    <?php
    $no=1;
    if(mysqli_num_rows($pengguna)>0):
    while($p=mysqli_fetch_assoc($pengguna)){
    ?>
    <tr>
      <td><?=$no?></td>
      <td><?=$p['nis']?></td>
      <td><?=$p['nama']?></td>
      <td><?=$p['alamat']?></td>
      <td><?=$p['no_hp']?></td>
      <td><?=$p['kelas']?></td>
      <td><?=$p['jurusan']?></td>
      <td><?=ucfirst($p['level'])?></td>
    </tr>
    <?php $no++; } else: ?>
    <tr><td colspan="8" style="text-align:center;padding:30px;">⚠️ Tidak ada data sesuai pilihan.</td></tr>
    <?php endif; ?>
  </table>

  <!-- TANDA TANGAN -->
  <div class="tanda-tangan">
    <div class="kiri">
      <div class="garis-ttd"></div>
      <p class="nama-jabatan">Administrator Perpustakaan</p>
    </div>
    <div class="kanan">
      <p>Yogyakarta, <?= $tgl_lengkap ?></p>
      <div class="garis-ttd"></div>
      <p class="nama-jabatan">Petugas Perpustakaan</p>
    </div>
  </div>

  <!-- TOMBOL CETAK -->
  <div class="tombol">
    <br><br>
    <button class="btn-cetak" onclick="window.print()">🖨️ KLIK DISINI UNTUK MENCETAK</button>
    <br>
    <a href="pengguna.php" class="kembali">← Kembali ke Daftar Pengguna</a>
  </div>

</div>

</body>
</html>