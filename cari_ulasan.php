<?php

include "config.php";
cek_login();


// ======================================================
// CEK HAK AKSES
// Hanya admin dan petugas
// ======================================================

if (
    !isset($_SESSION['level']) ||
    !in_array($_SESSION['level'], ['admin', 'petugas'])
) {

    header("Location: login.php");
    exit;

}


// ======================================================
// DATA UNTUK SIDEBAR
// ======================================================

$nama_sidebar  = $_SESSION['nama'] ?? 'Pengguna';
$level_sidebar = $_SESSION['level'] ?? '';

if ($level_sidebar == 'admin') {

    $jabatan_sidebar = 'Administrator';
    $label_sidebar   = 'Admin';

} elseif ($level_sidebar == 'petugas') {

    $jabatan_sidebar = 'Petugas Perpus';
    $label_sidebar   = 'Petugas';

} else {

    $jabatan_sidebar = $nama_sidebar;
    $label_sidebar   = ucfirst($level_sidebar);

}


// ======================================================
// AMBIL KATA KUNCI
// ======================================================

$cari = trim($_GET['cari'] ?? '');


// ======================================================
// QUERY ULASAN
// ======================================================

if ($cari !== '') {

    $kata_cari = "%" . $cari . "%";

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT
            ulasan.id,
            ulasan.rating,
            ulasan.komentar,
            ulasan.dibuat_pada,
            buku.judul,
            pengguna.nama
         FROM ulasan
         INNER JOIN buku
            ON ulasan.id_buku = buku.id
         INNER JOIN pengguna
            ON ulasan.id_pengguna = pengguna.id
         WHERE
            buku.judul LIKE ?
            OR pengguna.nama LIKE ?
            OR ulasan.komentar LIKE ?
            OR ulasan.rating LIKE ?
         ORDER BY ulasan.dibuat_pada DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $kata_cari,
        $kata_cari,
        $kata_cari,
        $kata_cari
    );

    mysqli_stmt_execute($stmt);

    $hasil_ulasan = mysqli_stmt_get_result($stmt);

} else {

    $hasil_ulasan = mysqli_query(
        $koneksi,
        "SELECT
            ulasan.id,
            ulasan.rating,
            ulasan.komentar,
            ulasan.dibuat_pada,
            buku.judul,
            pengguna.nama
         FROM ulasan
         INNER JOIN buku
            ON ulasan.id_buku = buku.id
         INNER JOIN pengguna
            ON ulasan.id_pengguna = pengguna.id
         ORDER BY ulasan.dibuat_pada DESC"
    );

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pencarian Ulasan - Perpustakaan</title>


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


        /* ================================
           SIDEBAR
        ================================= */

        .sidebar {

            width: 260px;

            background: white;

            box-shadow:
                2px 0 10px
                rgba(232, 74, 127, 0.15);

            position: fixed;

            height: 100vh;

            overflow: auto;

            left: 0;
            top: 0;

        }


        .sidebar-header {

            background:
                linear-gradient(
                    90deg,
                    var(--pink-soft),
                    var(--pink-dark)
                );

            color: white;

            padding: 20px 15px;

            font-size: 20px;

            font-weight: bold;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 10px;

        }


        .sidebar-header .emoji-logo {

            font-size: 28px;

        }


        .sidebar-user {

            padding: 20px 15px;

            text-align: center;

            border-bottom:
                1px solid
                var(--pink-soft);

        }


        .sidebar-user strong {

            color: var(--pink-dark);

            display: block;

            font-size: 20px;

            font-weight: bold;

            margin-bottom: 5px;

        }


        .sidebar-user small {

            color: var(--text-dark);

            font-size: 16px;

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

            border-left:
                3px solid
                transparent;

        }


        .sidebar a:hover,
        .sidebar a.aktif {

            background:
                var(--pink-light);

            border-left-color:
                var(--pink-dark);

            color:
                var(--pink-dark);

            font-weight: bold;

        }


        .sidebar a.logout {

            margin-top: 10px;

            color: var(--merah);

        }


        /* ================================
           KONTEN
        ================================= */

        .konten {

            margin-left: 260px;

            padding: 30px;

            width:
                calc(100% - 260px);

        }


        h2 {

            color:
                var(--pink-dark);

            margin-bottom: 20px;

        }


        .card {

            background: white;

            padding: 25px;

            border-radius: 16px;

            box-shadow:
                0 4px 15px
                rgba(232, 74, 127, 0.12);

            margin-bottom: 20px;

        }


        /* ================================
           FORM CARI
        ================================= */

        .form-cari {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        .input-cari {

            flex: 1;

            min-width: 200px;

            padding: 13px 15px;

            border:
                2px solid
                var(--pink-soft);

            border-radius: 10px;

            outline: none;

            font-size: 14px;

        }


        .input-cari:focus {

            border-color:
                var(--pink-dark);

        }


        .btn-cari {

            border: none;

            background:
                var(--pink-dark);

            color: white;

            padding:
                13px 25px;

            border-radius: 10px;

            cursor: pointer;

            font-weight: bold;

        }


        .btn-cari:hover {

            background: #D6386C;

        }


        .btn-reset {

            display: inline-flex;

            align-items: center;

            text-decoration: none;

            background:
                var(--pink-soft);

            color:
                var(--text-dark);

            padding:
                13px 20px;

            border-radius: 10px;

            font-weight: bold;

        }


        /* ================================
           INFO HASIL
        ================================= */

        .info-hasil {

            margin-bottom: 15px;

            color: #777;

            font-size: 14px;

        }


        .kata-cari {

            color:
                var(--pink-dark);

            font-weight: bold;

        }


        /* ================================
           TABEL
        ================================= */

        .table-wrapper {

            width: 100%;

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th,
        td {

            padding: 12px;

            border-bottom:
                1px solid #eee;

            text-align: left;

            vertical-align: top;

        }


        th {

            background:
                var(--pink-soft);

            color:
                var(--text-dark);

            text-align: center;

        }


        tr:hover td {

            background: #fff8fb;

        }


        .tengah {

            text-align: center;

        }


        .rating {

            color: #F5A623;

            font-size: 16px;

            white-space: nowrap;

        }


        .tanggal {

            color: #777;

            white-space: nowrap;

            font-size: 13px;

        }


        .komentar {

            line-height: 1.6;

            min-width: 250px;

        }


        .kosong {

            padding: 30px;

            text-align: center;

            color: #999;

        }


        /* ================================
           RESPONSIVE
        ================================= */

        @media(max-width: 768px) {

            .sidebar {

                width: 220px;

            }


            .konten {

                margin-left: 220px;

                width:
                    calc(100% - 220px);

                padding: 15px;

            }


            .sidebar a {

                padding:
                    12px 15px;

            }


            h2 {

                font-size: 21px;

            }

        }

    </style>

</head>


<body>


<!-- ================================
     SIDEBAR
================================= -->

<div class="sidebar">


    <!-- HEADER -->

    <div class="sidebar-header">

        <div class="emoji-logo">
            📚
        </div>

        <span>
            PERPUSTAKAAN
        </span>

    </div>


    <!-- USER LOGIN -->

    <div class="sidebar-user">

        <strong>

            <?= htmlspecialchars($jabatan_sidebar) ?>

        </strong>

        <small>

            <?= htmlspecialchars($label_sidebar) ?>

        </small>

    </div>


    <!-- MENU -->

    <div class="sidebar-menu">


        <!-- BERANDA -->

        <a
            href="beranda.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'beranda.php' ? 'aktif' : ''; ?>"
        >

            🏠 Beranda

        </a>


        <!-- KHUSUS ADMIN -->

        <?php if ($level_sidebar == 'admin') : ?>

            <a
                href="pengguna.php"
                class="<?= basename($_SERVER['PHP_SELF']) == 'pengguna.php' ? 'aktif' : ''; ?>"
            >

                👤 Kelola Pengguna

            </a>

        <?php endif; ?>


        <!-- ADMIN DAN PETUGAS -->

        <a
            href="buku.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'aktif' : ''; ?>"
        >

            📚 Kelola Buku

        </a>


        <a
            href="peminjaman.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'aktif' : ''; ?>"
        >

            📖 Transaksi Pinjam

        </a>


        <a
            href="riwayat_peminjaman.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat_peminjaman.php' ? 'aktif' : ''; ?>"
        >

            📋 Riwayat Peminjaman

        </a>


        <a
            href="cari_ulasan.php"
            class="aktif"
        >

            🔍 Pencarian Ulasan

        </a>


        <!-- KHUSUS PETUGAS -->

        <?php if ($level_sidebar == 'petugas') : ?>

            <a
                href="kondisi_buku.php"
                class="<?= basename($_SERVER['PHP_SELF']) == 'kondisi_buku.php' ? 'aktif' : ''; ?>"
            >

                📚 Cek Kondisi Buku

            </a>

        <?php endif; ?>


        <!-- LOGOUT -->

        <a
            href="logout.php"
            class="logout"
        >

            🚪 Keluar

        </a>


    </div>

</div>



<!-- ================================
     KONTEN
================================= -->

<div class="konten">


    <h2>

        🔍 Pencarian Ulasan Buku

    </h2>



    <!-- CARD PENCARIAN -->

    <div class="card">


        <form
            method="GET"
            class="form-cari"
        >


            <input
                type="text"
                name="cari"
                class="input-cari"
                value="<?= htmlspecialchars($cari) ?>"
                placeholder="Cari judul buku, nama pengguna, isi ulasan, atau rating..."
            >


            <button
                type="submit"
                class="btn-cari"
            >

                🔍 Cari

            </button>


            <?php if ($cari !== ''): ?>

                <a
                    href="cari_ulasan.php"
                    class="btn-reset"
                >

                    ✕ Reset

                </a>

            <?php endif; ?>


        </form>


    </div>



    <!-- CARD HASIL -->

    <div class="card">


        <?php

        $jumlah_hasil = 0;

        if ($hasil_ulasan) {

            $jumlah_hasil =
                mysqli_num_rows($hasil_ulasan);

        }

        ?>


        <div class="info-hasil">


            <?php if ($cari !== ''): ?>


                Menampilkan

                <span class="kata-cari">

                    <?= $jumlah_hasil ?>

                </span>

                hasil untuk:

                <span class="kata-cari">

                    "<?= htmlspecialchars($cari) ?>"

                </span>


            <?php else: ?>


                Menampilkan semua ulasan:

                <span class="kata-cari">

                    <?= $jumlah_hasil ?>

                </span>

                ulasan


            <?php endif; ?>


        </div>



        <?php if (
            $hasil_ulasan &&
            mysqli_num_rows($hasil_ulasan) > 0
        ): ?>


            <div class="table-wrapper">


                <table>


                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Judul Buku</th>

                            <th>Nama Pemberi Ulasan</th>

                            <th>Rating</th>

                            <th>Ulasan</th>

                            <th>Tanggal</th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php $no = 1; ?>


                        <?php while (
                            $ulasan = mysqli_fetch_assoc(
                                $hasil_ulasan
                            )
                        ): ?>


                            <tr>


                                <td class="tengah">

                                    <?= $no++ ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $ulasan['judul']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $ulasan['nama']
                                    ) ?>

                                </td>


                                <td class="tengah">


                                    <div class="rating">

                                        <?= str_repeat(
                                            "⭐",
                                            (int)$ulasan['rating']
                                        ) ?>

                                    </div>


                                    <?= (int)$ulasan['rating'] ?>/5


                                </td>


                                <td class="komentar">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $ulasan['komentar']
                                        )
                                    ) ?>

                                </td>


                                <td class="tanggal">

                                    <?= htmlspecialchars(
                                        $ulasan['dibuat_pada']
                                    ) ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="kosong">

                😕 Tidak ada ulasan yang ditemukan.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>