<?php
include "config.php";
cek_login();
if($_SESSION['level']!="admin"){
  die("<script>alert('Akses Ditolak!');location.href='beranda.php';</script>");
}

// Hapus Pengguna
if(isset($_GET['hapus'])){
  $id = $_GET['hapus'];
  if($id!=$_SESSION['id']){
    mysqli_query($koneksi, "DELETE FROM pengguna WHERE id='$id'");
    echo "<script>alert('Pengguna telah dihapus!');location.href='pengguna.php';</script>";
  }else{
    echo "<script>alert('Tidak dapat menghapus akun sendiri!');location.href='pengguna.php';</script>";
  }
}

// Pencarian & Filter
$cari = ""; $filter_kelas = ""; $filter_jurusan = ""; $where = "";
if(isset($_GET['cari'])){
  $cari = $_GET['cari'];
  $where .= " AND (nis LIKE '%$cari%' OR nama LIKE '%$cari%' OR username LIKE '%$cari%')";
}
if(isset($_GET['kelas']) && $_GET['kelas']!="semua"){
  $filter_kelas = $_GET['kelas'];
  $where .= " AND kelas='$filter_kelas'";
}
if(isset($_GET['jurusan']) && $_GET['jurusan']!="semua"){
  $filter_jurusan = $_GET['jurusan'];
  $where .= " AND jurusan='$filter_jurusan'";
}
if(!empty($where)) $where = "WHERE ".ltrim($where," AND ");

$kelas_list = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM pengguna WHERE kelas!='' ORDER BY 
  CASE kelas 
    WHEN 'X' THEN 1 
    WHEN 'XI' THEN 2 
    WHEN 'XII' THEN 3 
  END");
$jurusan_list = mysqli_query($koneksi, "SELECT DISTINCT jurusan FROM pengguna WHERE jurusan!='' ORDER BY 
  CASE jurusan 
    WHEN 'RPL' THEN 1 
    WHEN 'NK' THEN 2 
    ELSE 3 
  END, jurusan");

// 👇 URUTAN: LEVEL → JURUSAN → KELAS → NAMA
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Pengguna — Perpustakaan</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    :root{--pink-soft:#F8C8DC;--pink-dark:#E84A7F;--pink-light:#FFF0F5;--text-dark:#5C2337;--hijau:#2E7D32;--oranye:#EF6C00;--merah:#C62828;}
    body{background:var(--pink-light);display:flex;min-height:100vh;}
    .sidebar{width:260px;background:white;box-shadow:2px 0 10px rgba(232,74,127,0.15);position:fixed;height:100vh;overflow:auto;}
    .sidebar h2{padding:20px;text-align:center;background:linear-gradient(135deg,var(--pink-soft),var(--pink-dark));color:white;font-size:18px;}
    .sidebar .user{padding:15px;text-align:center;border-bottom:1px solid var(--pink-soft);}
    .sidebar .user strong{color:var(--pink-dark);}
    .sidebar .user small{color:var(--text-dark);}
    .sidebar a{display:block;padding:13px 20px;color:var(--text-dark);text-decoration:none;transition:0.2s;border-left:4px solid transparent;}
    .sidebar a:hover,.sidebar a.aktif{background:var(--pink-light);border-left-color:var(--pink-dark);color:var(--pink-dark);font-weight:bold;}
    .sidebar a.logout{margin-top:20px;color:var(--merah);}
    .konten{margin-left:260px;padding:25px;width:100%;}
    .atasan{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;}
    h2{color:var(--pink-dark);}
    .btn-tambah{background:var(--pink-dark);color:white;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold;}
    .btn-tambah:hover{background:#D6386C;}
    .btn-cetak{background:var(--hijau);color:white;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold;}
    .card{background:white;padding:25px;border-radius:14px;box-shadow:0 4px 12px rgba(232,74,127,0.1);}
    .filter-baris,.cari-baris{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;}
    select,input{padding:10px 12px;border:2px solid var(--pink-soft);border-radius:8px;outline:none;}
    button{padding:10px 20px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;}
    .btn-cari{background:var(--pink-dark);color:white;}
    .btn-reset{background:var(--pink-soft);color:var(--text-dark);}
    table{width:100%;border-collapse:collapse;margin-top:10px;}
    th,td{padding:10px 8px;text-align:center;border-bottom:1px solid #f0d6e0;font-size:13px;}
    th{background:var(--pink-light);color:var(--pink-dark);}
    td:nth-child(3),td:nth-child(4){text-align:left;}
    .btn-ubah{background:var(--oranye);color:white;padding:4px 8px;border-radius:4px;text-decoration:none;font-size:12px;display:inline-block;margin:2px;}
    .btn-hapus{background:var(--merah);color:white;padding:4px 8px;border-radius:4px;text-decoration:none;font-size:12px;display:inline-block;margin:2px;}
    .kosong{text-align:center;padding:30px;color:#999;}
    .level{padding:2px 8px;border-radius:12px;font-size:12px;color:white;}
    .l-admin{background:#C62828;}
    .l-petugas{background:#EF6C00;}
    .l-siswa{background:#2E7D32;}
  </style>
</head>
<body>

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
  <a href="riwayat_peminjaman.php">📋 Riwayat Peminjaman</a>
  <a href="cari_ulasan.php">🔍 Pencarian Ulasan</a>
  <a href="logout.php" class="logout">🚪 Keluar</a>
</div>

<div class="konten">
  <div class="atasan">
    <h2>👤 Kelola Data Pengguna</h2>
    <div>
      <a href="cetak_pengguna.php" class="btn-cetak">📄 Cetak Laporan</a>
      <a href="pengguna_tambah.php" class="btn-tambah">➕ Tambah Pengguna</a>
    </div>
  </div>

  <div class="card">
    <form class="filter-baris" method="get">
      <select name="kelas" onchange="this.form.submit()">
        <option value="semua" <?= ($filter_kelas=="")?"selected":"" ?>>Semua Kelas</option>
        <option value="X" <?= ($filter_kelas=="X")?"selected":"" ?>>Kelas 10</option>
        <option value="XI" <?= ($filter_kelas=="XI")?"selected":"" ?>>Kelas 11</option>
        <option value="XII" <?= ($filter_kelas=="XII")?"selected":"" ?>>Kelas 12</option>
      </select>
      <select name="jurusan" onchange="this.form.submit()">
        <option value="semua" <?= ($filter_jurusan=="")?"selected":"" ?>>Semua Jurusan</option>
        <?php while($jur=mysqli_fetch_assoc($jurusan_list)): ?>
        <option value="<?=$jur['jurusan']?>" <?= ($filter_jurusan==$jur['jurusan'])?"selected":"" ?>><?=$jur['jurusan']?></option>
        <?php endwhile; ?>
      </select>
      <button type="button" class="btn-reset" onclick="location.href='pengguna.php'">Tampilkan Semua</button>
    </form>

    <form class="cari-baris" method="get">
      <input type="text" name="cari" placeholder="Cari NIS / Nama / Username..." value="<?=$cari?>">
      <button class="btn-cari">Cari</button>
    </form>

    <table>
      <tr>
        <th>No</th>
        <th>NIS</th>
        <th>Nama Lengkap</th>
        <th>Alamat / HP</th>
        <th>Kelas</th>
        <th>Jurusan</th>
        <th>Level</th>
        <th>Username</th>
        <th>Aksi</th>
      </tr>
      <?php 
      $no=1;
      if(mysqli_num_rows($pengguna)>0):
      while($p=mysqli_fetch_assoc($pengguna)){ 
        $lvl = $p['level'];
        $lbl = ($lvl=='admin')?'l-admin':(($lvl=='petugas')?'l-petugas':'l-siswa');
      ?>
      <tr>
        <td><?=$no?></td>
        <td><?=$p['nis']?></td>
        <td style="text-align:left;"><?=$p['nama']?></td>
        <td style="text-align:left;font-size:12px;"><?=$p['alamat']?><br><?=$p['no_hp']?></td>
        <td><?=$p['kelas']?></td>
        <td><?=$p['jurusan']?></td>
        <td><span class="level <?=$lbl?>"><?=ucfirst($lvl)?></span></td>
        <td><?=$p['username']?></td>
        <td>
          <a href="pengguna_ubah.php?id=<?=$p['id']?>" class="btn-ubah">Ubah</a>
          <?php if($p['id']!=$_SESSION['id']){ ?>
          <a href="?hapus=<?=$p['id']?>" class="btn-hapus" onclick="return confirm('Yakin hapus?')">Hapus</a>
          <?php } ?>
        </td>
      </tr>
      <?php $no++; } else: ?>
      <tr><td colspan="9" class="kosong">👤 Belum ada data pengguna.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
</body>
</html>