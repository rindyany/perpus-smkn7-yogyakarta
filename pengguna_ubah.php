<?php
include "config.php";
cek_login();

if ($_SESSION['level'] != "admin") {
  die("<script>alert('Akses Ditolak!');location.href='beranda.php';</script>");
}

// AMBIL ID DARI URL
if (!isset($_GET['id']) || $_GET['id'] == '') {
  die("<script>alert('ID pengguna tidak ditemukan!');location.href='pengguna.php';</script>");
}

$id = (int)$_GET['id'];

// AMBIL DATA DARI DATABASE
$query_edit = mysqli_query(
  $koneksi,
  "SELECT * FROM pengguna WHERE id='$id'"
);

$edit = mysqli_fetch_assoc($query_edit);

// JIKA DATA TIDAK DITEMUKAN
if (!$edit) {
  die("<script>alert('Data tidak ditemukan!');location.href='pengguna.php';</script>");
}


// ==========================
// PROSES SIMPAN PERUBAHAN
// ==========================
if (isset($_POST['simpan'])) {

  $nis = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['nis'] ?? '')
  );

  $nama = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['nama'] ?? '')
  );

  $kelas = mysqli_real_escape_string(
    $koneksi,
    $_POST['kelas'] ?? ''
  );

  $jurusan = mysqli_real_escape_string(
    $koneksi,
    $_POST['jurusan'] ?? ''
  );

  $jurusan_baru = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['jurusan_baru'] ?? '')
  );

  $level = mysqli_real_escape_string(
    $koneksi,
    $_POST['level'] ?? ''
  );

  $username = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['username'] ?? '')
  );

  $no_hp = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['no_hp'] ?? '')
  );

  $alamat = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['alamat'] ?? '')
  );

  $password_input = $_POST['password'] ?? '';


  // JIKA JURUSAN BARU DIISI
  if (!empty($jurusan_baru)) {
    $jurusan = $jurusan_baru;
  }


  // ADMIN DAN PETUGAS
  // NIS, KELAS, DAN JURUSAN DIKOSONGKAN
  if ($level != "siswa") {
    $nis = "";
    $kelas = "";
    $jurusan = "";
  }


  // VALIDASI
  if (empty($nama) || empty($username) || empty($level)) {

    echo "<script>
      alert('Nama, username, dan level wajib diisi!');
    </script>";

  } else {

    // CEK USERNAME SUDAH DIGUNAKAN ATAU BELUM
    $cek_username = mysqli_query(
      $koneksi,
      "SELECT id FROM pengguna
       WHERE username='$username'
       AND id != '$id'"
    );

    if (mysqli_num_rows($cek_username) > 0) {

      echo "<script>
        alert('Username sudah digunakan oleh pengguna lain!');
      </script>";

    } else {

      // JIKA PASSWORD DIISI
      if (!empty($password_input)) {

        // PASSWORD BARU DIHASH
        $password = md5($password_input);

        $query_update = "
          UPDATE pengguna SET
            nis='$nis',
            nama='$nama',
            kelas='$kelas',
            jurusan='$jurusan',
            level='$level',
            username='$username',
            no_hp='$no_hp',
            alamat='$alamat',
            password='$password'
          WHERE id='$id'
        ";

      } else {

        // JIKA PASSWORD KOSONG
        // PASSWORD LAMA TETAP DIGUNAKAN
        $query_update = "
          UPDATE pengguna SET
            nis='$nis',
            nama='$nama',
            kelas='$kelas',
            jurusan='$jurusan',
            level='$level',
            username='$username',
            no_hp='$no_hp',
            alamat='$alamat'
          WHERE id='$id'
        ";
      }


      // JALANKAN UPDATE
      $update = mysqli_query(
        $koneksi,
        $query_update
      );


      if ($update) {

        echo "<script>
          alert('✅ Data berhasil diubah!');
          location.href='pengguna.php';
        </script>";

        exit;

      } else {

        echo "<script>
          alert('❌ Gagal mengubah data!');
        </script>";
      }
    }
  }
}


// ==========================
// AMBIL DAFTAR JURUSAN
// ==========================
$jurusan_list = mysqli_query(
  $koneksi,
  "SELECT DISTINCT jurusan
   FROM pengguna
   WHERE jurusan != ''
   ORDER BY jurusan ASC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>Ubah Pengguna — Perpustakaan</title>

  <style>

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    :root {
      --pink-soft: #F8C8DC;
      --pink-dark: #E84A7F;
      --pink-light: #FFF0F5;
      --text-dark: #5C2337;
      --merah: #C62828;
    }

    body {
      background: var(--pink-light);
      display: flex;
      min-height: 100vh;
    }


    /* ==========================
       SIDEBAR
    ========================== */

    .sidebar {
      width: 260px;
      background: white;
      box-shadow: 2px 0 10px rgba(232, 74, 127, 0.15);
      position: fixed;
      height: 100vh;
      overflow: auto;
    }

    .sidebar h2 {
      padding: 20px;
      text-align: center;
      background: linear-gradient(
        135deg,
        var(--pink-soft),
        var(--pink-dark)
      );
      color: white;
      font-size: 18px;
    }

    .sidebar .user {
      padding: 15px;
      text-align: center;
      border-bottom: 1px solid var(--pink-soft);
    }

    .sidebar .user strong {
      color: var(--pink-dark);
    }

    .sidebar .user small {
      color: var(--text-dark);
    }

    .sidebar a {
      display: block;
      padding: 13px 20px;
      color: var(--text-dark);
      text-decoration: none;
      transition: 0.2s;
      border-left: 4px solid transparent;
    }

    .sidebar a:hover,
    .sidebar a.aktif {
      background: var(--pink-light);
      border-left-color: var(--pink-dark);
      color: var(--pink-dark);
      font-weight: bold;
    }

    .sidebar a.logout {
      margin-top: 20px;
      color: var(--merah);
    }


    /* ==========================
       KONTEN
    ========================== */

    .konten {
      margin-left: 260px;
      padding: 25px;
      width: 100%;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 4px 12px rgba(232, 74, 127, 0.1);
      max-width: 900px;
    }

    h2 {
      color: var(--pink-dark);
      margin-bottom: 20px;
    }

    a.kembali {
      display: inline-block;
      margin-bottom: 15px;
      color: var(--pink-dark);
      text-decoration: none;
      font-weight: 500;
    }

    a.kembali:hover {
      text-decoration: underline;
    }


    /* ==========================
       FORM
    ========================== */

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }

    label {
      color: var(--text-dark);
      font-weight: 500;
    }

    input,
    select,
    textarea {
      width: 100%;
      padding: 10px 12px;
      border: 2px solid var(--pink-soft);
      border-radius: 8px;
      outline: none;
      margin-top: 5px;
      font-size: 14px;
    }

    input:disabled,
    select:disabled,
    textarea:disabled {
      background: #f0f0f0;
      cursor: not-allowed;
    }

    input:focus,
    select:focus,
    textarea:focus {
      border-color: var(--pink-dark);
    }

    .penuh {
      grid-column: 1 / -1;
    }


    /* ==========================
       PASSWORD
    ========================== */

    .password-box {
      position: relative;
      width: 100%;
    }

    .password-box input {
      padding-right: 50px;
    }

    .btn-lihat {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 18px;
      padding: 5px;
    }

    .btn-lihat:hover {
      background: #FFF0F5;
      border-radius: 5px;
    }


    /* ==========================
       BUTTON
    ========================== */

    .btn-simpan {
      background: var(--pink-dark);
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-simpan:hover {
      background: #D6386C;
    }

    .btn-batal {
      background: var(--pink-soft);
      color: var(--text-dark);
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      margin-right: 10px;
    }

    .keterangan {
      color: #888;
      font-size: 12px;
      margin-top: 5px;
      display: block;
    }


    /* ==========================
       RESPONSIVE
    ========================== */

    @media(max-width: 768px) {

      .sidebar {
        width: 220px;
      }

      .konten {
        margin-left: 220px;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }

      .penuh {
        grid-column: auto;
      }
    }

  </style>
</head>


<body>


<!-- ==========================
     SIDEBAR
========================== -->

<div class="sidebar">

  <h2>📚 Perpustakaan</h2>

  <div class="user">

    <strong>
      <?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username']) ?>
    </strong>

    <br>

    <small>
      <?= ucfirst($_SESSION['level']) ?>
    </small>

  </div>


  <a href="beranda.php">
    🏠 Beranda
  </a>

  <a href="pengguna.php" class="aktif">
    👤 Kelola Pengguna
  </a>

  <a href="buku.php">
   📚 Kelola Buku
  </a>

  <a href="peminjaman.php">
    📖 Transaksi Pinjam
  </a>

  <a href="riwayat_peminjaman.php">
    📋 Riwayat Peminjaman
  </a>

  <a href="cari_ulasan.php">
    🔍 Pencarian Ulasan
  </a>

  <a href="logout.php" class="logout">
    🚪 Keluar
  </a>

</div>


<!-- ==========================
     KONTEN
========================== -->

<div class="konten">

  <div class="card">

    <a href="pengguna.php" class="kembali">
      ← Kembali ke Daftar Pengguna
    </a>


    <h2>✏️ Ubah Data Pengguna</h2>


    <form method="post" class="form-grid">


      <!-- NIS -->

      <div>

        <label>
          NIS (Nomor Induk Siswa)
        </label>

        <input
          type="text"
          name="nis"
          placeholder="Khusus Siswa"
          id="nis-field"
          value="<?= htmlspecialchars($edit['nis'] ?? '') ?>">

      </div>


      <!-- NAMA -->

      <div>

        <label>
          Nama Lengkap
        </label>

        <input
          type="text"
          name="nama"
          value="<?= htmlspecialchars($edit['nama'] ?? '') ?>"
          required>

      </div>


      <!-- KELAS -->

      <div>

        <label>
          Kelas
        </label>

        <select
          name="kelas"
          id="kelas-field">

          <option value="">
            -- Pilih Kelas --
          </option>

          <option
            value="X"
            <?= ($edit['kelas'] ?? '') == 'X' ? 'selected' : '' ?>>

            Kelas 10

          </option>

          <option
            value="XI"
            <?= ($edit['kelas'] ?? '') == 'XI' ? 'selected' : '' ?>>

            Kelas 11

          </option>

          <option
            value="XII"
            <?= ($edit['kelas'] ?? '') == 'XII' ? 'selected' : '' ?>>

            Kelas 12

          </option>

        </select>

      </div>


      <!-- JURUSAN -->

      <div>

        <label>
          Jurusan
        </label>

        <select
          name="jurusan"
          id="jurusan-select">

          <option value="">
            -- Pilih Jurusan --
          </option>


          <?php while ($jur = mysqli_fetch_assoc($jurusan_list)): ?>

            <option
              value="<?= htmlspecialchars($jur['jurusan']) ?>"
              <?= ($edit['jurusan'] ?? '') == $jur['jurusan'] ? 'selected' : '' ?>>

              <?= htmlspecialchars($jur['jurusan']) ?>

            </option>

          <?php endwhile; ?>


        </select>

      </div>


      <!-- JURUSAN BARU -->

      <div class="penuh">

        <label>
          Atau Tulis Jurusan Baru
        </label>

        <input
          type="text"
          name="jurusan_baru"
          placeholder="Tulis jurusan jika belum ada..."
          id="jurusan-baru">

      </div>


      <!-- LEVEL -->

      <div>

        <label>
          Hak Akses / Level
        </label>

        <select
          name="level"
          id="level-pilih"
          required>

          <option value="">
            -- Pilih Jenis Akun --
          </option>

          <option
            value="siswa"
            <?= ($edit['level'] ?? '') == 'siswa' ? 'selected' : '' ?>>

            Siswa

          </option>

          <option
            value="petugas"
            <?= ($edit['level'] ?? '') == 'petugas' ? 'selected' : '' ?>>

            Petugas

          </option>

          <option
            value="admin"
            <?= ($edit['level'] ?? '') == 'admin' ? 'selected' : '' ?>>

            Admin

          </option>

        </select>

      </div>


      <div></div>


      <!-- USERNAME -->

      <div>

        <label>
          Username
        </label>

        <input
          type="text"
          name="username"
          value="<?= htmlspecialchars($edit['username'] ?? '') ?>"
          required>

      </div>


      <!-- PASSWORD -->

      <div>

        <label>
          Password
          <span style="color:#888;font-size:12px;">
            (Kosongkan jika tidak ingin diganti)
          </span>
        </label>


        <div class="password-box">

          <input
            type="password"
            name="password"
            id="password"
            placeholder="Masukkan password baru">


          <button
            type="button"
            class="btn-lihat"
            onclick="lihatPassword()"
            id="tombol-password">

            👁️

          </button>

        </div>


        <small class="keterangan">

          Password lama tidak dapat ditampilkan karena sudah tersimpan
          dalam bentuk hash di database. Isi password baru hanya jika
          ingin menggantinya.

        </small>

      </div>


      <!-- NO HP -->

      <div>

        <label>
          Nomor HP
        </label>

        <input
          type="tel"
          name="no_hp"
          value="<?= htmlspecialchars($edit['no_hp'] ?? '') ?>">

      </div>


      <div></div>


      <!-- ALAMAT -->

      <div class="penuh">

        <label>
          Alamat Lengkap
        </label>

        <textarea
          name="alamat"
          rows="3"><?= htmlspecialchars($edit['alamat'] ?? '') ?></textarea>

      </div>


      <!-- TOMBOL -->

      <div
        class="penuh"
        style="margin-top:10px;">

        <button
          type="button"
          class="btn-batal"
          onclick="location.href='pengguna.php'">

          ✕ Batal

        </button>


        <button
          type="submit"
          name="simpan"
          class="btn-simpan">

          💾 Simpan Perubahan

        </button>

      </div>


    </form>

  </div>

</div>



<script>


// ==========================
// AMBIL ELEMENT
// ==========================

const level = document.getElementById('level-pilih');

const nis = document.getElementById('nis-field');

const kelas = document.getElementById('kelas-field');

const jurusanS = document.getElementById('jurusan-select');

const jurusanB = document.getElementById('jurusan-baru');

const password = document.getElementById('password');

const tombolPassword = document.getElementById('tombol-password');


// ==========================
// CEK LEVEL PENGGUNA
// ==========================

function cekLevel() {

  if (level.value === "siswa") {

    nis.disabled = false;
    kelas.disabled = false;
    jurusanS.disabled = false;
    jurusanB.disabled = false;

  } else {

    nis.value = "";
    nis.disabled = true;

    kelas.value = "";
    kelas.disabled = true;

    jurusanS.value = "";
    jurusanS.disabled = true;

    jurusanB.value = "";
    jurusanB.disabled = true;
  }
}


// ==========================
// SAAT LEVEL DIUBAH
// ==========================

level.addEventListener(
  'change',
  cekLevel
);


// ==========================
// JALANKAN SAAT HALAMAN DIBUKA
// ==========================

window.addEventListener(
  'DOMContentLoaded',
  cekLevel
);


// ==========================
// JURUSAN SELECT
// ==========================

jurusanS.addEventListener(
  'change',
  function () {

    if (jurusanS.value !== "") {
      jurusanB.value = "";
    }

  }
);


// ==========================
// JURUSAN BARU
// ==========================

jurusanB.addEventListener(
  'input',
  function () {

    if (jurusanB.value !== "") {
      jurusanS.value = "";
    }

  }
);


// ==========================
// LIHAT / SEMBUNYIKAN PASSWORD
// ==========================

function lihatPassword() {

  if (password.type === "password") {

    password.type = "text";

    tombolPassword.innerHTML = "🙈";

  } else {

    password.type = "password";

    tombolPassword.innerHTML = "👁️";

  }

}


</script>


</body>
</