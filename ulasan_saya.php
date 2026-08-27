<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "config.php";

// =====================================================
// CEK LOGIN
// =====================================================
if (function_exists('cek_login')) {
    cek_login();
}

if (
    !isset($_SESSION['sudah_login']) ||
    !isset($_SESSION['level']) ||
    $_SESSION['level'] !== 'siswa'
) {
    header("Location: login.php");
    exit;
}


// =====================================================
// AMBIL ID PENGGUNA DARI SESSION
// =====================================================
$id_pengguna = 0;

if (
    isset($_SESSION['id_pengguna']) &&
    (int)$_SESSION['id_pengguna'] > 0
) {
    $id_pengguna = (int)$_SESSION['id_pengguna'];

} elseif (
    isset($_SESSION['id']) &&
    (int)$_SESSION['id'] > 0
) {
    $id_pengguna = (int)$_SESSION['id'];
}


// =====================================================
// PASTIKAN ID TERSEBUT ADA DI TABEL PENGGUNA
// =====================================================
if ($id_pengguna > 0) {

    $stmt_cek_pengguna = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM pengguna
         WHERE id = ?
         LIMIT 1"
    );

    if ($stmt_cek_pengguna) {

        mysqli_stmt_bind_param(
            $stmt_cek_pengguna,
            "i",
            $id_pengguna
        );

        mysqli_stmt_execute($stmt_cek_pengguna);

        $hasil_cek_pengguna = mysqli_stmt_get_result(
            $stmt_cek_pengguna
        );

        if (
            !$hasil_cek_pengguna ||
            mysqli_num_rows($hasil_cek_pengguna) == 0
        ) {
            $id_pengguna = 0;
        }

        mysqli_stmt_close($stmt_cek_pengguna);
    }
}


// =====================================================
// JIKA BELUM DAPAT ID, CARI BERDASARKAN USERNAME
// =====================================================
if (
    $id_pengguna <= 0 &&
    isset($_SESSION['username'])
) {

    $username = $_SESSION['username'];

    $stmt_cari_pengguna = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM pengguna
         WHERE username = ?
         AND level = 'siswa'
         LIMIT 1"
    );

    if ($stmt_cari_pengguna) {

        mysqli_stmt_bind_param(
            $stmt_cari_pengguna,
            "s",
            $username
        );

        mysqli_stmt_execute($stmt_cari_pengguna);

        $hasil_cari_pengguna = mysqli_stmt_get_result(
            $stmt_cari_pengguna
        );

        if (
            $hasil_cari_pengguna &&
            mysqli_num_rows($hasil_cari_pengguna) > 0
        ) {

            $data_pengguna = mysqli_fetch_assoc(
                $hasil_cari_pengguna
            );

            $id_pengguna = (int)$data_pengguna['id'];
        }

        mysqli_stmt_close($stmt_cari_pengguna);
    }
}


// =====================================================
// JIKA MASIH BELUM ADA, COBA CARI BERDASARKAN NAMA
// =====================================================
if (
    $id_pengguna <= 0 &&
    isset($_SESSION['nama'])
) {

    $nama_session = $_SESSION['nama'];

    $stmt_cari_nama = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM pengguna
         WHERE nama = ?
         AND level = 'siswa'
         LIMIT 1"
    );

    if ($stmt_cari_nama) {

        mysqli_stmt_bind_param(
            $stmt_cari_nama,
            "s",
            $nama_session
        );

        mysqli_stmt_execute($stmt_cari_nama);

        $hasil_cari_nama = mysqli_stmt_get_result(
            $stmt_cari_nama
        );

        if (
            $hasil_cari_nama &&
            mysqli_num_rows($hasil_cari_nama) > 0
        ) {

            $data_pengguna = mysqli_fetch_assoc(
                $hasil_cari_nama
            );

            $id_pengguna = (int)$data_pengguna['id'];
        }

        mysqli_stmt_close($stmt_cari_nama);
    }
}


// =====================================================
// JIKA ID PENGGUNA TIDAK DITEMUKAN
// =====================================================
if ($id_pengguna <= 0) {

    echo "
        <script>
            alert('ID pengguna tidak ditemukan. Silakan login kembali.');
            window.location='login.php';
        </script>
    ";

    exit;
}


// =====================================================
// ID SISWA UNTUK DATA PEMINJAMAN
// =====================================================
$id_siswa = 0;


// Coba dari session
if (
    isset($_SESSION['id_siswa']) &&
    (int)$_SESSION['id_siswa'] > 0
) {
    $id_siswa = (int)$_SESSION['id_siswa'];
}


// Jika tidak ada, gunakan ID pengguna
if ($id_siswa <= 0) {
    $id_siswa = $id_pengguna;
}


// =====================================================
// PROSES KIRIM ULASAN
// =====================================================
if (isset($_POST['kirim_ulasan'])) {

    $id_buku = (int)($_POST['id_buku'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $komentar = trim($_POST['ulasan'] ?? '');


    // VALIDASI
    if (
        $id_buku <= 0 ||
        $rating < 1 ||
        $rating > 5 ||
        $komentar === ''
    ) {

        echo "
            <script>
                alert('Mohon isi semua data ulasan dengan benar!');
            </script>
        ";

    } else {


        // =================================================
        // CEK APAKAH BUKU SUDAH DIKEMBALIKAN
        // =================================================
        $stmt_cek_buku = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM peminjaman
             WHERE id_buku = ?
             AND id_siswa = ?
             AND status = 'dikembalikan'
             LIMIT 1"
        );


        if (!$stmt_cek_buku) {

            die(
                "Query peminjaman gagal: " .
                mysqli_error($koneksi)
            );
        }


        mysqli_stmt_bind_param(
            $stmt_cek_buku,
            "ii",
            $id_buku,
            $id_siswa
        );

        mysqli_stmt_execute($stmt_cek_buku);

        $hasil_cek_buku = mysqli_stmt_get_result(
            $stmt_cek_buku
        );


        // Jika buku belum dikembalikan
        if (
            !$hasil_cek_buku ||
            mysqli_num_rows($hasil_cek_buku) == 0
        ) {

            echo "
                <script>
                    alert('Kamu hanya bisa memberikan ulasan untuk buku yang sudah dikembalikan.');
                </script>
            ";

        } else {


            // =============================================
            // CEK APAKAH SUDAH MEMBERIKAN ULASAN
            // =============================================
            $stmt_cek_ulasan = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM ulasan
                 WHERE id_buku = ?
                 AND id_pengguna = ?
                 LIMIT 1"
            );


            if (!$stmt_cek_ulasan) {

                die(
                    "Query ulasan gagal: " .
                    mysqli_error($koneksi)
                );
            }


            mysqli_stmt_bind_param(
                $stmt_cek_ulasan,
                "ii",
                $id_buku,
                $id_pengguna
            );

            mysqli_stmt_execute($stmt_cek_ulasan);

            $hasil_cek_ulasan = mysqli_stmt_get_result(
                $stmt_cek_ulasan
            );


            if (
                $hasil_cek_ulasan &&
                mysqli_num_rows($hasil_cek_ulasan) > 0
            ) {

                echo "
                    <script>
                        alert('Kamu sudah memberikan ulasan untuk buku ini.');
                    </script>
                ";

            } else {


                // =========================================
                // SIMPAN ULASAN
                // =========================================
                $stmt_simpan = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO ulasan
                    (
                        id_buku,
                        id_pengguna,
                        rating,
                        komentar
                    )
                    VALUES (?, ?, ?, ?)"
                );


                if (!$stmt_simpan) {

                    die(
                        "Gagal membuat query simpan: " .
                        mysqli_error($koneksi)
                    );
                }


                mysqli_stmt_bind_param(
                    $stmt_simpan,
                    "iiis",
                    $id_buku,
                    $id_pengguna,
                    $rating,
                    $komentar
                );


                if (
                    mysqli_stmt_execute($stmt_simpan)
                ) {

                    mysqli_stmt_close($stmt_simpan);

                    echo "
                        <script>
                            alert('Ulasan berhasil dikirim!');
                            window.location='ulasan_saya.php';
                        </script>
                    ";

                    exit;

                } else {

                    echo "
                        <script>
                            alert('Gagal menyimpan ulasan: " .
                            addslashes(
                                mysqli_stmt_error($stmt_simpan)
                            ) .
                            "');
                        </script>
                    ";
                }


                mysqli_stmt_close($stmt_simpan);
            }


            mysqli_stmt_close($stmt_cek_ulasan);
        }


        mysqli_stmt_close($stmt_cek_buku);
    }
}


// =====================================================
// AMBIL BUKU YANG SUDAH DIKEMBALIKAN
// =====================================================
$stmt_buku = mysqli_prepare(
    $koneksi,
    "SELECT DISTINCT
        buku.id,
        buku.judul

     FROM peminjaman

     INNER JOIN buku
        ON buku.id = peminjaman.id_buku

     WHERE peminjaman.id_siswa = ?
     AND peminjaman.status = 'dikembalikan'

     ORDER BY buku.judul ASC"
);


$buku_dikembalikan = false;


if ($stmt_buku) {

    mysqli_stmt_bind_param(
        $stmt_buku,
        "i",
        $id_siswa
    );

    mysqli_stmt_execute($stmt_buku);

    $buku_dikembalikan = mysqli_stmt_get_result(
        $stmt_buku
    );
}


// =====================================================
// AMBIL RIWAYAT ULASAN
// =====================================================
$stmt_riwayat = mysqli_prepare(
    $koneksi,
    "SELECT
        ulasan.id,
        ulasan.rating,
        ulasan.komentar,
        ulasan.dibuat_pada,
        buku.judul

     FROM ulasan

     INNER JOIN buku
        ON buku.id = ulasan.id_buku

     WHERE ulasan.id_pengguna = ?

     ORDER BY ulasan.dibuat_pada DESC"
);


$riwayat_ulasan = false;


if ($stmt_riwayat) {

    mysqli_stmt_bind_param(
        $stmt_riwayat,
        "i",
        $id_pengguna
    );

    mysqli_stmt_execute($stmt_riwayat);

    $riwayat_ulasan = mysqli_stmt_get_result(
        $stmt_riwayat
    );
}


// =====================================================
// NAMA SISWA
// =====================================================
$nama_siswa = $_SESSION['nama'] ?? 'Siswa';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ulasan Saya - Perpustakaan</title>


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
        }


        body {
            background: var(--pink-light);
            display: flex;
            min-height: 100vh;
        }


        /* SIDEBAR */

        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 2px 0 10px rgba(232, 74, 127, 0.15);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            left: 0;
            top: 0;
        }


        .sidebar-header {
            background: linear-gradient(
                90deg,
                var(--pink-soft),
                var(--pink-dark)
            );
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }


        .sidebar-user {
            padding: 15px;
            text-align: center;
        }


        .sidebar-user strong {
            color: var(--pink-dark);
            display: block;
            font-size: 15px;
        }


        .sidebar-user small {
            color: var(--text-dark);
            font-size: 12px;
        }


        .sidebar-menu {
            padding: 10px 0;
        }


        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: var(--text-dark);
            text-decoration: none;
            transition: 0.2s;
            margin: 3px 0;
            border-left: 3px solid transparent;
        }


        .sidebar a:hover,
        .sidebar a.aktif {
            background: var(--pink-light);
            border-left-color: var(--pink-dark);
            color: var(--pink-dark);
            font-weight: bold;
        }


        .sidebar a.logout {
            margin-top: 10px;
        }


        /* KONTEN */

        .konten {
            margin-left: 260px;
            padding: 25px;
            width: calc(100% - 260px);
        }


        h2 {
            color: var(--pink-dark);
            font-size: 24px;
            margin-bottom: 20px;
        }


        .card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(232, 74, 127, 0.1);
            margin-bottom: 25px;
        }


        .card h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }


        /* FORM */

        .form-group {
            margin-bottom: 15px;
        }


        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-dark);
        }


        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }


        .form-control:focus {
            border-color: var(--pink-dark);
        }


        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }


        .btn-pink {
            background: var(--pink-dark);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }


        .btn-pink:hover {
            opacity: 0.9;
        }


        /* TABLE */

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }


        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }


        th {
            background: var(--pink-soft);
            color: var(--text-dark);
            text-align: center;
        }


        tr:hover td {
            background: #fff8fb;
        }


        .tengah {
            text-align: center;
        }


        .rating {
            font-size: 15px;
            white-space: nowrap;
        }


        .rating-number {
            color: #777;
            font-size: 12px;
            margin-top: 3px;
        }


        .tanggal {
            color: #777;
            white-space: nowrap;
        }


        .belum-ada {
            text-align: center;
            color: #999;
            padding: 20px;
        }


        /* RESPONSIVE */

        @media (max-width: 768px) {

            .sidebar {
                width: 210px;
            }


            .konten {
                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 15px;
            }

        }


        @media (max-width: 600px) {

            .sidebar {
                width: 180px;
            }


            .konten {
                margin-left: 180px;
                width: calc(100% - 180px);
                padding: 10px;
            }


            .sidebar a {
                padding: 10px 15px;
                font-size: 13px;
            }


            .card {
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

    <div class="sidebar-header">
        Perpustakaan
    </div>


    <div class="sidebar-user">

        <strong>
            <?= htmlspecialchars($nama_siswa) ?>
        </strong>

        <small>
            Siswa / Anggota
        </small>

    </div>


    <div class="sidebar-menu">

        <a href="beranda_siswa.php">
            🏠 Beranda
        </a>


        <a href="katalog_buku.php">
            📖 Katalog Buku
        </a>


        <a href="pinjaman_saya.php">
            📚 Pinjaman Saya
        </a>


        <a href="riwayat_saya.php">
            📋 Riwayat Pinjam
        </a>


        <a
            href="ulasan_saya.php"
            class="aktif"
        >
            💬 Ulasan Saya
        </a>


        <a
            href="logout.php"
            class="logout"
        >
            🚪 Keluar
        </a>

    </div>

</div>


<!-- =====================================================
     KONTEN
===================================================== -->

<div class="konten">

    <h2>
        💬 Ulasan Saya
    </h2>


    <!-- =================================================
         FORM TAMBAH ULASAN
    ================================================== -->

    <div class="card">

        <h3>
            Tambah Ulasan Buku
        </h3>


        <form method="POST">

            <div class="form-group">

                <label>
                    Pilih Buku:
                </label>


                <select
                    name="id_buku"
                    class="form-control"
                    required
                >

                    <option value="">
                        -- Pilih Buku yang Sudah Dikembalikan --
                    </option>


                    <?php if (
                        $buku_dikembalikan &&
                        mysqli_num_rows($buku_dikembalikan) > 0
                    ): ?>

                        <?php while (
                            $b = mysqli_fetch_assoc($buku_dikembalikan)
                        ): ?>

                            <option
                                value="<?= (int)$b['id'] ?>"
                            >
                                <?= htmlspecialchars($b['judul']) ?>
                            </option>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <option
                            value=""
                            disabled
                        >
                            Belum ada buku yang sudah dikembalikan
                        </option>

                    <?php endif; ?>

                </select>

            </div>


            <!-- RATING -->

            <div class="form-group">

                <label>
                    Rating:
                </label>


                <select
                    name="rating"
                    class="form-control"
                    required
                >

                    <option value="5">
                        ⭐⭐⭐⭐⭐ (5 - Sangat Bagus)
                    </option>

                    <option value="4">
                        ⭐⭐⭐⭐ (4 - Bagus)
                    </option>

                    <option value="3">
                        ⭐⭐⭐ (3 - Cukup)
                    </option>

                    <option value="2">
                        ⭐⭐ (2 - Kurang)
                    </option>

                    <option value="1">
                        ⭐ (1 - Buruk)
                    </option>

                </select>

            </div>


            <!-- KOMENTAR -->

            <div class="form-group">

                <label>
                    Ulasan / Kesan:
                </label>


                <textarea
                    name="ulasan"
                    class="form-control"
                    rows="4"
                    placeholder="Tulis komentar atau kesan kamu tentang buku ini..."
                    required
                ></textarea>

            </div>


            <button
                type="submit"
                name="kirim_ulasan"
                class="btn-pink"
            >
                📤 Kirim Ulasan
            </button>

        </form>

    </div>


    <!-- =================================================
         RIWAYAT ULASAN
    ================================================== -->

    <div class="card">

        <h3>
            Riwayat Ulasan Kamu
        </h3>


        <?php if (
            $riwayat_ulasan &&
            mysqli_num_rows($riwayat_ulasan) > 0
        ): ?>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Rating</th>
                            <th>Ulasan</th>
                            <th>Tanggal</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php
                        $no = 1;
                        ?>


                        <?php while (
                            $u = mysqli_fetch_assoc($riwayat_ulasan)
                        ): ?>


                            <tr>

                                <td class="tengah">
                                    <?= $no++ ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $u['judul']
                                    ) ?>
                                </td>


                                <td class="tengah">

                                    <div class="rating">

                                        <?= str_repeat(
                                            "⭐",
                                            (int)$u['rating']
                                        ) ?>

                                    </div>


                                    <div class="rating-number">

                                        <?= (int)$u['rating'] ?>/5

                                    </div>

                                </td>


                                <td>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $u['komentar']
                                        )
                                    ) ?>

                                </td>


                                <td class="tanggal">

                                    <?= htmlspecialchars(
                                        $u['dibuat_pada']
                                    ) ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <p class="belum-ada">
                Belum ada ulasan yang kamu berikan.
            </p>


        <?php endif; ?>

    </div>

</div>


</body>
</html>