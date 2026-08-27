<?php
include "config.php";
cek_login();
if($_SESSION['level']!="admin"){
  die("<script>alert('Akses Ditolak!');location.href='beranda.php';</script>");
}

if(isset($_POST['simpan'])){
  $nis      = mysqli_real_escape_string($koneksi, trim($_POST['nis']));
  $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
  $kelas    = mysqli_real_escape_string($koneksi, $_POST['kelas']);
  $jurusan  = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
  $jurusan_baru = trim($_POST['jurusan_baru']);
  if(!empty($jurusan_baru)) $jurusan = $jurusan_baru;
  $level    = $_POST['level'];
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = md5($_POST['password']);
  $no_hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
  $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);

  if($level!="siswa"){ $nis=""; $kelas=""; $jurusan=""; }

  $cek = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE username='$username'"));
  if($cek>0){
    echo "<script>alert('Username sudah terpakai!');history.back();</script>";
    exit;
  }

  mysqli_query($koneksi, "INSERT INTO pengguna 
    (nis,nama,kelas,jurusan,level,username,password,no_hp,alamat) 
    VALUES ('$nis','$nama','$kelas','$jurusan','$level','$username','$password','$no_hp','$alamat')");
  
  echo "<script>alert('✅ Pengguna berhasil ditambahkan!');location.href='pengguna.php';</script>";
}

$jurusan_list = mysqli_query($koneksi, "SELECT DISTINCT jurusan FROM pengguna WHERE jurusan!='' ORDER BY jurusan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Pengguna — Perpustakaan</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    :root{--pink-soft:#F8C8DC;--pink-dark:#E84A7F;--pink-light:#FFF0F5;--text-dark:#5C2337;--merah:#C62828;}
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
    .card{background:white;padding:30px;border-radius:14px;box-shadow:0 4px 12px rgba(232,74,127,0.1);max-width:900px;}
    h2{color:var(--pink-dark);margin-bottom:20px;}
    a.kembali{display:inline-block;margin-bottom:15px;color:var(--pink-dark);text-decoration:none;}
    .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px;}
    label{color:var(--text-dark);font-weight:500;}
    input,select,textarea{width:100%;padding:10px 12px;border:2px solid var(--pink-soft);border-radius:8px;outline:none;margin-top:5px;}
    input:disabled,select:disabled{background:#f0f0f0;cursor:not-allowed;}
    input:focus,select:focus,textarea:focus{border-color:var(--pink-dark);}
    .penuh{grid-column:1/-1;}
    .btn-simpan{background:var(--pink-dark);color:white;border:none;padding:12px 25px;border-radius:8px;font-weight:bold;cursor:pointer;}
    .btn-simpan:hover{background:#D6386C;}
    .btn-batal{background:var(--pink-soft);color:var(--text-dark);border:none;padding:12px 25px;border-radius:8px;font-weight:bold;cursor:pointer;margin-right:10px;}
    label span{color:red;}
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
  <div class="card">
    <a href="pengguna.php" class="kembali">← Kembali ke Daftar Pengguna</a>
    <h2>➕ Tambah Pengguna Baru</h2>

    <form method="post" class="form-grid">
      <div>
        <label>NIS (Nomor Induk Siswa)</label>
        <input type="text" name="nis" placeholder="Khusus untuk Siswa" id="nis-field">
      </div>
      <div>
        <label>Nama Lengkap <span>*</span></label>
        <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
      </div>

      <div>
        <label>Kelas</label>
        <select name="kelas" id="kelas-field">
          <option value="">-- Pilih Kelas --</option>
          <option value="X">Kelas 10</option>
          <option value="XI">Kelas 11</option>
          <option value="XII">Kelas 12</option>
        </select>
      </div>
      <div>
        <label>Jurusan</label>
        <select name="jurusan" id="jurusan-select">
          <option value="">-- Pilih Jurusan --</option>
          <?php while($jur=mysqli_fetch_assoc($jurusan_list)): ?>
          <option value="<?=$jur['jurusan']?>"><?=$jur['jurusan']?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="penuh">
        <label>Atau Tulis Jurusan Baru</label>
        <input type="text" name="jurusan_baru" placeholder="Tulis nama jurusan jika belum ada di daftar" id="jurusan-baru">
      </div>

      <div>
        <label>Hak Akses / Level <span>*</span></label>
        <select name="level" id="level-pilih" required>
          <option value="">-- Pilih Jenis Akun --</option>
          <option value="siswa">👨‍🎓 Siswa</option>
          <option value="petugas">📋 Petugas</option>
          <option value="admin">👑 Administrator</option>
        </select>
      </div>
      <div></div>

      <div>
        <label>Username <span>*</span></label>
        <input type="text" name="username" placeholder="Buat username login" required>
      </div>
      <div>
        <label>Password <span>*</span></label>
        <input type="password" name="password" placeholder="Buat password login" required>
      </div>

      <div>
        <label>Nomor HP</label>
        <input type="tel" name="no_hp" placeholder="Masukkan nomor HP">
      </div>
      <div></div>

      <div class="penuh">
        <label>Alamat Lengkap</label>
        <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
      </div>

      <div class="penuh" style="margin-top:10px;">
        <button type="button" class="btn-batal" onclick="location.href='pengguna.php'">✕ Batal</button>
        <button type="submit" name="simpan" class="btn-simpan">💾 Simpan Pengguna</button>
      </div>
    </form>
  </div>
</div>

<script>
const level = document.getElementById('level-pilih');
const nis = document.getElementById('nis-field');
const kelas = document.getElementById('kelas-field');
const jurusanS = document.getElementById('jurusan-select');
const jurusanB = document.getElementById('jurusan-baru');

function cekLevel(){
  if(level.value=="siswa"){
    nis.disabled = false;
    kelas.disabled = false;
    jurusanS.disabled = false;
    jurusanB.disabled = false;
  }else{
    nis.value=""; nis.disabled = true;
    kelas.value=""; kelas.disabled = true;
    jurusanS.value=""; jurusanS.disabled = true;
    jurusanB.value=""; jurusanB.disabled = true;
  }
}
level.addEventListener('change',cekLevel);
jurusanS.addEventListener('change',()=>{if(jurusanS.value)jurusanB.value=""});
jurusanB.addEventListener('input',()=>{if(jurusanB.value)jurusanS.value=""});
</script>

</body>
</html>