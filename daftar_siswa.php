<?php
include "config.php";

/*
|--------------------------------------------------------------------------
| CEK MODE HALAMAN
|--------------------------------------------------------------------------
| Jika sudah login, berarti halaman dibuka oleh admin dari Kelola Pengguna.
| Jika belum login, berarti halaman dibuka dari tombol "Daftar di sini".
*/

$mode_admin = isset($_SESSION['username']) && isset($_SESSION['level']);


// ==========================================================
// PROSES SIMPAN DATA
// ==========================================================
if (isset($_POST['simpan'])) {

    // ------------------------------------------------------
    // AMBIL DATA FORM DENGAN AMAN
    // ------------------------------------------------------
    $nis     = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nis'] ?? '')
    );

    $nama = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama'] ?? '')
    );

    $kelas = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['kelas'] ?? '')
    );

    $jurusan = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['jurusan'] ?? '')
    );

    $jurusan_baru = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['jurusan_baru'] ?? '')
    );

    // Jika jurusan baru diisi, gunakan jurusan baru
    if (!empty($jurusan_baru)) {
        $jurusan = $jurusan_baru;
    }


    /*
    |--------------------------------------------------------------------------
    | LEVEL
    |--------------------------------------------------------------------------
    | Jika daftar dari halaman login:
    | level otomatis menjadi siswa.
    |
    | Jika dari admin:
    | level diambil dari pilihan form.
    */

    if ($mode_admin) {

        $level = mysqli_real_escape_string(
            $koneksi,
            $_POST['level'] ?? ''
        );

    } else {

        $level = "siswa";

    }


    $username = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['username'] ?? '')
    );

    $password_input = $_POST['password'] ?? '';
    $password = md5($password_input);

    $no_hp = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['no_hp'] ?? '')
    );

    $alamat = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['alamat'] ?? '')
    );


    // ======================================================
    // VALIDASI
    // ======================================================

    if (
        empty($nama) ||
        empty($username) ||
        empty($password_input)
    ) {

        echo "
        <script>
            alert('Nama, username, dan password wajib diisi!');
            history.back();
        </script>
        ";

        exit;
    }


    // Khusus siswa, NIS sebaiknya wajib
    if ($level == "siswa" && empty($nis)) {

        echo "
        <script>
            alert('NIS wajib diisi untuk akun siswa!');
            history.back();
        </script>
        ";

        exit;
    }


    // Jika bukan siswa, kosongkan data siswa
    if ($level != "siswa") {

        $nis = "";
        $kelas = "";
        $jurusan = "";

    }


    // ======================================================
    // CEK USERNAME
    // ======================================================

    $cek_username = mysqli_query(
        $koneksi,
        "SELECT id FROM pengguna WHERE username='$username'"
    );

    if ($cek_username && mysqli_num_rows($cek_username) > 0) {

        echo "
        <script>
            alert('Username sudah terpakai! Silakan gunakan username lain.');
            history.back();
        </script>
        ";

        exit;
    }


    // ======================================================
    // CEK NIS JIKA SISWA
    // ======================================================

    if ($level == "siswa") {

        $cek_nis = mysqli_query(
            $koneksi,
            "SELECT id FROM pengguna WHERE nis='$nis'"
        );

        if ($cek_nis && mysqli_num_rows($cek_nis) > 0) {

            echo "
            <script>
                alert('NIS tersebut sudah terdaftar!');
                history.back();
            </script>
            ";

            exit;
        }
    }


    // ======================================================
    // SIMPAN KE DATABASE
    // ======================================================

    $query_simpan = "
        INSERT INTO pengguna
        (
            nis,
            nama,
            kelas,
            jurusan,
            level,
            username,
            password,
            no_hp,
            alamat
        )
        VALUES
        (
            '$nis',
            '$nama',
            '$kelas',
            '$jurusan',
            '$level',
            '$username',
            '$password',
            '$no_hp',
            '$alamat'
        )
    ";

    $simpan = mysqli_query(
        $koneksi,
        $query_simpan
    );


    // ======================================================
    // JIKA BERHASIL
    // ======================================================

    if ($simpan) {

        // Jika dari admin
        if ($mode_admin) {

            echo "
            <script>
                alert('Pengguna berhasil ditambahkan!');
                location.href='pengguna.php';
            </script>
            ";

        }

        // Jika dari halaman login
        else {

            echo "
            <script>
                alert('Pendaftaran berhasil! Silakan login menggunakan akun yang sudah dibuat.');
                location.href='login.php';
            </script>
            ";

        }

        exit;

    } else {

        echo "
        <script>
            alert('Gagal menyimpan data!');
            history.back();
        </script>
        ";

        exit;

    }

}


// ==========================================================
// AMBIL DATA JURUSAN
// ==========================================================

$jurusan_list = mysqli_query(
    $koneksi,
    "
    SELECT DISTINCT jurusan
    FROM pengguna
    WHERE jurusan != ''
    ORDER BY jurusan ASC
    "
);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= $mode_admin
            ? "Tambah Pengguna - Perpustakaan"
            : "Daftar Akun Siswa - Perpustakaan"
        ?>
    </title>


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
            min-height: 100vh;
        }


        /* ==================================================
           SIDEBAR ADMIN
        ================================================== */

        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 2px 0 10px rgba(232, 74, 127, 0.15);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
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


        /* ==================================================
           KONTEN ADMIN
        ================================================== */

        .konten-admin {
            margin-left: 260px;
            padding: 25px;
            width: calc(100% - 260px);
        }


        /* ==================================================
           KONTEN PUBLIC / DAFTAR DARI LOGIN
        ================================================== */

        .konten-public {
            min-height: 100vh;
            width: 100%;
            padding: 35px 20px;

            display: flex;
            justify-content: center;
            align-items: flex-start;
        }


        /* ==================================================
           CARD
        ================================================== */

        .card {
            background: white;
            padding: 30px;
            border-radius: 14px;

            box-shadow:
                0 8px 25px rgba(232, 74, 127, 0.15);

            width: 100%;
            max-width: 950px;
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
        }


        a.kembali:hover {
            text-decoration: underline;
        }


        /* ==================================================
           FORM
        ================================================== */

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }


        .form-grid > div {
            min-width: 0;
        }


        label {
            color: var(--text-dark);
            font-weight: 600;
            display: block;
        }


        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;

            border: 2px solid var(--pink-soft);

            border-radius: 8px;
            outline: none;
            margin-top: 6px;

            font-size: 14px;

            transition: 0.2s;
        }


        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--pink-dark);
        }


        input:disabled,
        select:disabled {
            background: #f1f1f1;
            cursor: not-allowed;
        }


        textarea {
            resize: vertical;
        }


        .penuh {
            grid-column: 1 / -1;
        }


        label span {
            color: red;
        }


        /* ==================================================
           BUTTON
        ================================================== */

        .btn-area {
            margin-top: 10px;
        }


        .btn-simpan,
        .btn-batal {
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }


        .btn-simpan {
            background: var(--pink-dark);
            color: white;
        }


        .btn-simpan:hover {
            background: #D6386C;
        }


        .btn-batal {
            background: var(--pink-soft);
            color: var(--text-dark);
            margin-right: 10px;
        }


        /* ==================================================
           INFO KHUSUS PENDAFTARAN SISWA
        ================================================== */

        .info-siswa {
            grid-column: 1 / -1;

            background: #FFF0F5;
            color: var(--text-dark);

            padding: 13px 15px;
            border-radius: 8px;

            border-left: 4px solid var(--pink-dark);
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media(max-width: 768px) {

            .sidebar {
                display: none;
            }


            .konten-admin {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }


            .konten-public {
                padding: 20px 12px;
            }


            .card {
                padding: 20px;
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


<?php if ($mode_admin): ?>

    <!-- =========================================
         SIDEBAR HANYA UNTUK YANG SUDAH LOGIN
    ========================================== -->

    <div class="sidebar">

        <h2>📚 Perpustakaan</h2>


        <div class="user">

            <strong>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>
            </strong>

            <br>

            <small>
                <?= ucfirst(htmlspecialchars($_SESSION['level'] ?? 'admin')) ?>
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


        <a href="cari.php">
            🔍 Pencarian
        </a>


        <a href="logout.php" class="logout">
            🚪 Keluar
        </a>

    </div>

<?php endif; ?>


<!-- =========================================
     KONTEN UTAMA
========================================== -->

<div class="<?= $mode_admin ? 'konten-admin' : 'konten-public' ?>">


    <div class="card">


        <?php if ($mode_admin): ?>

            <a
                href="pengguna.php"
                class="kembali"
            >
                ← Kembali ke Daftar Pengguna
            </a>

            <h2>
                ➕ Tambah Pengguna Baru
            </h2>

        <?php else: ?>

            <a
                href="login.php"
                class="kembali"
            >
                ← Kembali ke Login
            </a>

            <h2>
                📝 Daftar Akun Siswa
            </h2>

        <?php endif; ?>


        <form
            method="post"
            class="form-grid"
        >


            <?php if (!$mode_admin): ?>

                <div class="info-siswa">

                    <strong>📚 Pendaftaran Anggota Perpustakaan</strong>

                    <br>

                    Silakan isi data di bawah untuk membuat akun siswa.

                </div>

            <?php endif; ?>


            <!-- NIS -->

            <div>

                <label>
                    NIS (Nomor Induk Siswa)

                    <?php if (!$mode_admin): ?>
                        <span>*</span>
                    <?php endif; ?>

                </label>

                <input
                    type="text"
                    name="nis"
                    placeholder="Masukkan NIS"
                    id="nis-field"
                    <?= !$mode_admin ? 'required' : '' ?>
                >

            </div>


            <!-- NAMA -->

            <div>

                <label>
                    Nama Lengkap
                    <span>*</span>
                </label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Masukkan nama lengkap"
                    required
                >

            </div>


            <!-- KELAS -->

            <div>

                <label>
                    Kelas
                </label>

                <select
                    name="kelas"
                    id="kelas-field"
                >

                    <option value="">
                        -- Pilih Kelas --
                    </option>

                    <option value="X">
                        Kelas 10
                    </option>

                    <option value="XI">
                        Kelas 11
                    </option>

                    <option value="XII">
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
                    id="jurusan-select"
                >

                    <option value="">
                        -- Pilih Jurusan --
                    </option>


                    <?php if ($jurusan_list): ?>

                        <?php while (
                            $jur = mysqli_fetch_assoc($jurusan_list)
                        ): ?>

                            <option
                                value="<?= htmlspecialchars($jur['jurusan']) ?>"
                            >
                                <?= htmlspecialchars($jur['jurusan']) ?>
                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>


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
                    placeholder="Tulis nama jurusan jika belum ada di daftar"
                    id="jurusan-baru"
                >

            </div>


            <!-- =====================================
                 LEVEL HANYA UNTUK ADMIN
            ====================================== -->

            <?php if ($mode_admin): ?>


                <div>

                    <label>
                        Hak Akses / Level
                        <span>*</span>
                    </label>

                    <select
                        name="level"
                        id="level-pilih"
                        required
                    >

                        <option value="">
                            -- Pilih Jenis Akun --
                        </option>

                        <option value="siswa">
                            👨‍🎓 Siswa
                        </option>

                        <option value="petugas">
                            📋 Petugas
                        </option>

                        <option value="admin">
                            👑 Administrator
                        </option>

                    </select>

                </div>


                <div></div>


            <?php else: ?>


                <!-- PUBLIC OTOMATIS SISWA -->

                <input
                    type="hidden"
                    name="level"
                    value="siswa"
                >


            <?php endif; ?>


            <!-- USERNAME -->

            <div>

                <label>
                    Username
                    <span>*</span>
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="Buat username login"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div>

                <label>
                    Password
                    <span>*</span>
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Buat password login"
                    required
                >

            </div>


            <!-- NOMOR HP -->

            <div>

                <label>
                    Nomor HP
                </label>

                <input
                    type="tel"
                    name="no_hp"
                    placeholder="Masukkan nomor HP"
                >

            </div>


            <div></div>


            <!-- ALAMAT -->

            <div class="penuh">

                <label>
                    Alamat Lengkap
                </label>

                <textarea
                    name="alamat"
                    rows="3"
                    placeholder="Masukkan alamat lengkap"
                ></textarea>

            </div>


            <!-- BUTTON -->

            <div class="penuh btn-area">


                <?php if ($mode_admin): ?>


                    <button
                        type="button"
                        class="btn-batal"
                        onclick="location.href='pengguna.php'"
                    >
                        ✕ Batal
                    </button>


                    <button
                        type="submit"
                        name="simpan"
                        class="btn-simpan"
                    >
                        💾 Simpan Pengguna
                    </button>


                <?php else: ?>


                    <button
                        type="button"
                        class="btn-batal"
                        onclick="location.href='login.php'"
                    >
                        ← Kembali
                    </button>


                    <button
                        type="submit"
                        name="simpan"
                        class="btn-simpan"
                    >
                        📝 Daftar Sekarang
                    </button>


                <?php endif; ?>


            </div>


        </form>


    </div>


</div>


<script>


// ===============================================
// JAVASCRIPT HANYA JIKA ADA FORM LEVEL ADMIN
// ===============================================

const level = document.getElementById('level-pilih');

const nis = document.getElementById('nis-field');

const kelas = document.getElementById('kelas-field');

const jurusanS = document.getElementById('jurusan-select');

const jurusanB = document.getElementById('jurusan-baru');


// -----------------------------------------------
// KHUSUS ADMIN
// -----------------------------------------------

if (level) {

    function cekLevel() {

        if (level.value === "siswa") {

            nis.disabled = false;

            kelas.disabled = false;

            jurusanS.disabled = false;

            jurusanB.disabled = false;

        }

        else {

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


    level.addEventListener(
        'change',
        cekLevel
    );

}


// -----------------------------------------------
// JURUSAN
// -----------------------------------------------

jurusanS.addEventListener(
    'change',
    function () {

        if (jurusanS.value) {

            jurusanB.value = "";

        }

    }
);


jurusanB.addEventListener(
    'input',
    function () {

        if (jurusanB.value) {

            jurusanS.value = "";

        }

    }
);


</script>


</body>

</html>