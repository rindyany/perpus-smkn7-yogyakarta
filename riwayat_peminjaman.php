<?php
session_start();
include 'config.php';
cek_login();

// =====================================================
// HAK AKSES: ADMIN DAN PETUGAS
// =====================================================
if (
    $_SESSION['level'] != 'admin' &&
    $_SESSION['level'] != 'petugas'
) {
    die("<script>
        alert('Akses Ditolak!');
        location.href='beranda.php';
    </script>");
}


// =====================================================
// DATA USER UNTUK SIDEBAR
// SAMA SEPERTI BERANDA
// =====================================================
$nama_sidebar  = $_SESSION['nama'] ?? 'Pengguna';
$level_sidebar = $_SESSION['level'] ?? '';

if ($level_sidebar == 'admin') {
    $jabatan_sidebar = 'Administrator';
    $label_sidebar   = 'Admin';
} elseif ($level_sidebar == 'petugas') {
    $jabatan_sidebar = 'Petugas Perpus';
    $label_sidebar   = 'Petugas';
} elseif ($level_sidebar == 'siswa') {
    $jabatan_sidebar = $nama_sidebar;
    $label_sidebar   = 'Siswa';
} else {
    $jabatan_sidebar = $nama_sidebar;
    $label_sidebar   = ucfirst($level_sidebar);
}


// =====================================================
// AMBIL DATA FILTER
// =====================================================
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
$id_buku = isset($_GET['id_buku']) ? $_GET['id_buku'] : '';


// =====================================================
// QUERY FILTER
// =====================================================
$where = "WHERE p.status = 'dikembalikan' ";

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $where .= "AND DATE(p.tgl_pinjam)
               BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
}

if (!empty($id_buku)) {
    $where .= "AND p.id_buku = '$id_buku' ";
}


// =====================================================
// AMBIL DATA RIWAYAT PEMINJAMAN
// =====================================================
$query = "
    SELECT
        p.*,
        pg.nis,
        pg.nama,
        b.kode_buku,
        b.judul

    FROM peminjaman p

    JOIN pengguna pg
        ON p.id_siswa = pg.id

    JOIN buku b
        ON p.id_buku = b.id

    $where

    ORDER BY p.tgl_pinjam DESC
";

$riwayat = mysqli_query($koneksi, $query);


// =====================================================
// DATA BUKU UNTUK FILTER
// =====================================================
$daftar_buku = mysqli_query(
    $koneksi,
    "SELECT * FROM buku ORDER BY judul ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Riwayat Peminjaman - Perpustakaan</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
            --hijau: #2E7D32;
            --abu: #888;
            --kuning: #FF9800;
        }

        body {
            background: var(--pink-light);
            display: flex;
            min-height: 100vh;
        }


        /* =====================================
           SIDEBAR
           SAMA SEPERTI BERANDA
        ===================================== */

        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 2px 0 10px rgba(232,74,127,0.15);
            position: fixed;
            height: 100vh;
            overflow: auto;
        }

        .sidebar-header {
            background: linear-gradient(
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


        /* USER SIDEBAR */

        .sidebar-user {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid var(--pink-soft);
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


        /* MENU */

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
            color: var(--merah);
        }


        /* =====================================
           KONTEN
        ===================================== */

        .konten {
            margin-left: 260px;
            padding: 25px;
            width: calc(100% - 260px);
        }

        h2 {
            color: var(--pink-dark);
            font-size: 22px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }


        /* =====================================
           FORM
        ===================================== */

        .form-label {
            font-size: 13px;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border: 1px solid #f0c8d8;
            border-radius: 6px;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pink-dark);
            box-shadow: none;
        }


        /* =====================================
           BUTTON
        ===================================== */

        .btn-pink {
            background: linear-gradient(
                90deg,
                var(--pink-soft),
                var(--pink-dark)
            );

            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn-pink:hover {
            background: var(--pink-dark);
            color: white;
        }

        .btn-kuning {
            background: var(--kuning);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn-kuning:hover {
            background: #FB8C00;
            color: white;
        }

        .btn-abu {
            background: #E5E7EB;
            color: #374151;
            border: none;
            border-radius: 6px;
        }

        .btn-abu:hover {
            background: #D1D5DB;
            color: #111827;
        }

        .btn-secondary {
            background: #6B7280;
            color: white;
            border: none;
            border-radius: 6px;
        }


        /* =====================================
           TABEL
        ===================================== */

        .table th {
            background: var(--pink-soft);
            color: var(--pink-dark);
            border: none;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }


        /* =====================================
           BADGE DAN WARNA
        ===================================== */

        .badge-selesai {
            background: var(--hijau);
            color: white;
        }

        .text-merah {
            color: var(--merah);
            font-weight: bold;
        }

        .text-hijau {
            color: var(--hijau);
            font-weight: bold;
        }


        /* =====================================
           HEADER LAPORAN
        ===================================== */

        .laporan-header {
            display: none;
        }


        /* =====================================
           PRINT
        ===================================== */

        @media print {

            .sidebar,
            .tombol-kembali,
            .filter-box,
            .btn-kuning,
            .btn-secondary,
            .no-print {
                display: none !important;
            }

            .konten {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                background: white !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
                padding: 10px 0 !important;
            }

            body {
                background: white !important;
            }

            .laporan-header {
                display: block !important;
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 2px solid #333;
            }

            .laporan-header h3 {
                font-size: 18px;
                font-weight: bold;
                margin: 0;
            }

            .laporan-header p {
                font-size: 13px;
                color: #333;
                margin: 3px 0;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            table th,
            table td {
                border: 1px solid #333 !important;
                padding: 8px !important;
                font-size: 12px;
            }

            .tanda-tangan {
                margin-top: 50px;
            }
        }


        /* RESPONSIVE SAMA SEPERTI BERANDA */

        @media(max-width:600px) {

            .sidebar {
                width: 220px;
            }

            .konten {
                margin-left: 220px;
                width: calc(100% - 220px);
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================
     SIDEBAR SAMA SEPERTI BERANDA
===================================== -->

<div class="sidebar no-print">

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
            class="<?= basename($_SERVER['PHP_SELF']) == 'cari_ulasan.php' ? 'aktif' : ''; ?>"
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


<!-- =====================================
     KONTEN UTAMA
===================================== -->

<div class="konten">


    <div class="tombol-kembali mb-4 no-print">

        <a
            href="peminjaman.php"
            class="btn btn-abu"
        >
            ← Kembali ke Transaksi Pinjam
        </a>

    </div>


    <!-- HEADER CETAK -->

    <div class="laporan-header">

        <h3>PERPUSTAKAAN SEKOLAH</h3>

        <h3>SMK NEGERI 7 YOGYAKARTA</h3>

        <p>Jl. Contoh No. 123, Yogyakarta</p>

        <hr style="border:1px solid #333;margin:10px 0;">

        <h4 style="margin:15px 0 5px;">
            LAPORAN RIWAYAT PEMINJAMAN BUKU
        </h4>

        <p>

            Periode:

            <?php
            echo !empty($tanggal_awal)
                ? date(
                    'd/m/Y',
                    strtotime($tanggal_awal)
                )
                : 'Semua Tanggal';
            ?>

            s/d

            <?php
            echo !empty($tanggal_akhir)
                ? date(
                    'd/m/Y',
                    strtotime($tanggal_akhir)
                )
                : 'Sekarang';
            ?>

        </p>

    </div>


    <h2 class="no-print">
        ✅ Riwayat Peminjaman
    </h2>


    <!-- FILTER -->

    <div class="card filter-box no-print">

        <form
            method="GET"
            action="riwayat_peminjaman.php"
            class="row g-3 align-items-end"
        >


            <div class="col-md-3">

                <label class="form-label fw-semibold">
                    Tanggal Pinjam Dari
                </label>

                <input
                    type="date"
                    name="tanggal_awal"
                    class="form-control"
                    value="<?= $tanggal_awal ?>"
                >

            </div>


            <div class="col-md-3">

                <label class="form-label fw-semibold">
                    Sampai Tanggal
                </label>

                <input
                    type="date"
                    name="tanggal_akhir"
                    class="form-control"
                    value="<?= $tanggal_akhir ?>"
                >

            </div>


            <div class="col-md-3">

                <label class="form-label fw-semibold">
                    Judul Buku
                </label>

                <select
                    name="id_buku"
                    class="form-select"
                >

                    <option value="">
                        -- Semua Buku --
                    </option>


                    <?php while ($b = mysqli_fetch_assoc($daftar_buku)) { ?>

                        <option
                            value="<?= $b['id'] ?>"
                            <?= $id_buku == $b['id'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($b['kode_buku']) ?>
                            -
                            <?= htmlspecialchars($b['judul']) ?>

                        </option>

                    <?php } ?>


                </select>

            </div>


            <div class="col-md-3 d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-pink w-100"
                >
                    🔍 Tampilkan
                </button>


                <?php if (
                    !empty($tanggal_awal) ||
                    !empty($tanggal_akhir) ||
                    !empty($id_buku)
                ) { ?>

                    <a
                        href="riwayat_peminjaman.php"
                        class="btn btn-secondary"
                    >
                        ↩️ Reset
                    </a>

                <?php } ?>


            </div>


        </form>

    </div>


    <!-- TOTAL DAN CETAK -->

    <div
        class="d-flex justify-content-between align-items-center mb-4 no-print"
    >

        <h5 class="text-muted mb-0">

            <?php

            $jumlah = mysqli_num_rows($riwayat);

            echo "Total: <strong>$jumlah</strong> riwayat peminjaman";

            ?>

        </h5>


        <button
            onclick="window.print()"
            class="btn btn-kuning"
        >
            🖨️ Cetak Laporan
        </button>

    </div>


    <!-- TABEL RIWAYAT -->

    <div class="card">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">


                <thead>

                    <tr>

                        <th width="4%">No</th>
                        <th>NIS</th>
                        <th>Nama Peminjam</th>
                        <th>Kode Buku</th>
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $no = 1;

                if (mysqli_num_rows($riwayat) > 0) {

                    while ($data = mysqli_fetch_assoc($riwayat)) {


                        $denda =
                            !empty($data['denda'])
                            && $data['denda'] > 0

                            ? '<span class="text-merah">
                                Rp ' .
                                number_format(
                                    $data['denda'],
                                    0,
                                    ',',
                                    '.'
                                ) .
                              '</span>'

                            : '<span class="text-hijau">—</span>';


                        $tgl_kembali =
                            !empty($data['tgl_kembali'])

                            ? date(
                                'd/m/Y',
                                strtotime(
                                    $data['tgl_kembali']
                                )
                            )

                            : '—';

                ?>


                    <tr>

                        <td>
                            <?= $no++ ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($data['nis']) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($data['nama']) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($data['kode_buku']) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($data['judul']) ?>
                        </td>


                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime($data['tgl_pinjam'])
                            ) ?>

                        </td>


                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime($data['tgl_kembali'])
                            ) ?>

                        </td>


                        <td>

                            <?= $tgl_kembali ?>

                        </td>


                        <td>

                            <strong>
                                <?= $denda ?>
                            </strong>

                        </td>


                        <td>

                            <span class="badge badge-selesai">
                                ✅ Selesai
                            </span>

                        </td>

                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="10"
                            class="text-center text-muted py-4"
                        >
                            😞 Belum ada riwayat peminjaman yang selesai
                        </td>

                    </tr>


                <?php } ?>


                </tbody>

            </table>

        </div>

    </div>


    <!-- TANDA TANGAN -->

    <div class="card tanda-tangan">

        <div class="row">

            <div class="col-8"></div>


            <div class="col-4 text-center">

                <p>
                    Yogyakarta,
                    <?= date('d F Y') ?>
                </p>

                <p>
                    Kepala Perpustakaan
                </p>

                <br>
                <br>
                <br>

                <p>
                    <u>
                        <strong>
                            Administrator Perpustakaan
                        </strong>
                    </u>
                </p>

                <p>
                    NIP. -
                </p>

            </div>

        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>
</html>