<?php
include "config.php";
cek_login();

// ======================================================
// CEK HAK AKSES ADMIN DAN PETUGAS
// ======================================================
if (
    !isset($_SESSION['level']) ||
    (
        $_SESSION['level'] != "admin" &&
        $_SESSION['level'] != "petugas"
    )
) {
    die("<script>
        alert('Akses Ditolak!');
        location.href='beranda.php';
    </script>");
}


// ======================================================
// DATA SIDEBAR
// ======================================================
$nama_sidebar  = $_SESSION['nama'] ?? 'Pengguna';
$level_sidebar = $_SESSION['level'] ?? '';


// ======================================================
// FILTER KATEGORI
// ======================================================
$filter_kategori = isset($_GET['kategori'])
    ? $_GET['kategori']
    : 'semua';

$where = "";

if ($filter_kategori != "semua") {

    $kategori_aman = mysqli_real_escape_string(
        $koneksi,
        $filter_kategori
    );

    $where = "WHERE kategori = '$kategori_aman'";
}


// ======================================================
// AMBIL DATA BUKU
// ======================================================
$buku = mysqli_query(
    $koneksi,
    "SELECT * FROM buku
     $where
     ORDER BY kategori ASC, judul ASC"
);


// ======================================================
// AMBIL DAFTAR KATEGORI
// ======================================================
$kategori_list = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kategori
     FROM buku
     WHERE kategori != ''
     ORDER BY kategori ASC"
);


// ======================================================
// TANGGAL
// ======================================================
$tgl_lengkap = date('d F Y');
$tgl_singkat = date('d F Y H:i');
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Laporan Data Buku</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI',sans-serif;
        }


        :root{
            --pink-soft:#F8C8DC;
            --pink-dark:#E84A7F;
            --pink-light:#FFF0F5;
            --text-dark:#5C2337;
            --merah:#C62828;
        }


        body{
            background:var(--pink-light);
            display:flex;
            min-height:100vh;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar{
            width:260px;
            background:white;
            box-shadow:2px 0 10px rgba(232,74,127,0.15);
            position:fixed;
            height:100vh;
            overflow:auto;
        }


        .sidebar h2{
            padding:20px;
            text-align:center;
            background:linear-gradient(
                135deg,
                var(--pink-soft),
                var(--pink-dark)
            );
            color:white;
            font-size:18px;
        }


        .sidebar .user{
            padding:15px;
            text-align:center;
            border-bottom:1px solid var(--pink-soft);
        }


        .sidebar .user strong{
            color:var(--pink-dark);
        }


        .sidebar .user small{
            color:var(--text-dark);
        }


        .sidebar a{
            display:block;
            padding:13px 20px;
            color:var(--text-dark);
            text-decoration:none;
            transition:0.2s;
            border-left:4px solid transparent;
        }


        .sidebar a:hover,
        .sidebar a.aktif{
            background:var(--pink-light);
            border-left-color:var(--pink-dark);
            color:var(--pink-dark);
            font-weight:bold;
        }


        .sidebar a.logout{
            margin-top:20px;
            color:var(--merah);
        }


        /* =========================
           KONTEN
        ========================= */

        .konten{
            margin-left:260px;
            padding:25px;
            width:100%;
        }


        .kepala{
            text-align:center;
            margin-bottom:25px;
        }


        .kepala h2{
            margin-bottom:5px;
            letter-spacing:1px;
            color:var(--pink-dark);
        }


        .kepala h3{
            margin-bottom:8px;
        }


        .form-pilih{
            background:#f9f0f5;
            padding:18px;
            border-radius:10px;
            margin-bottom:25px;
        }


        .form-pilih label{
            font-weight:bold;
            margin-right:8px;
        }


        select{
            padding:8px 12px;
            border-radius:6px;
            border:1px solid var(--pink-dark);
        }


        .btn-lihat{
            background:var(--pink-dark);
            color:white;
            border:none;
            padding:8px 20px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            margin-left:10px;
        }


        .btn-lihat:hover{
            background:#D6386C;
        }


        table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
            background:white;
        }


        th,
        td{
            border:1px solid #333;
            padding:10px;
            text-align:left;
            font-size:14px;
        }


        th{
            background:var(--pink-soft);
            color:var(--text-dark);
            text-align:center;
        }


        td:nth-child(1),
        td:nth-child(2),
        td:nth-child(7),
        td:nth-child(8){
            text-align:center;
        }


        /* =========================
           TANDA TANGAN
        ========================= */

        .tanda-tangan{
            margin-top:60px;
            width:100%;
            overflow:hidden;
        }


        .kiri{
            float:left;
            width:48%;
        }


        .kanan{
            float:right;
            width:48%;
            text-align:right;
        }


        .garis-ttd{
            margin-top:70px;
            border-bottom:1px solid #000;
            width:240px;
        }


        .kiri .garis-ttd{
            margin-left:0;
        }


        .kanan .garis-ttd{
            margin-left:auto;
        }


        .nama-jabatan{
            margin-top:5px;
        }


        /* =========================
           TOMBOL
        ========================= */

        .tombol{
            clear:both;
            margin-top:40px;
            text-align:center;
        }


        .btn-cetak{
            padding:12px 30px;
            font-size:15px;
            cursor:pointer;
            background:var(--pink-dark);
            color:white;
            border:none;
            border-radius:8px;
            font-weight:bold;
        }


        .btn-cetak:hover{
            background:#D6386C;
        }


        .kembali{
            display:inline-block;
            margin-top:15px;
            color:var(--pink-dark);
            text-decoration:none;
        }


        /* =========================
           PRINT
        ========================= */

        @media print{

            .sidebar,
            .form-pilih,
            .tombol,
            .kembali{
                display:none !important;
            }


            .konten{
                margin-left:0 !important;
                padding:10px !important;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <h2>📚 Perpustakaan</h2>


    <!-- USER -->

    <div class="user">

        <strong>
            <?= htmlspecialchars($nama_sidebar) ?>
        </strong>

        <br>

        <small>
            <?= htmlspecialchars(ucfirst($level_sidebar)) ?>
        </small>

    </div>


    <!-- BERANDA -->

    <a href="beranda.php">
        🏠 Beranda
    </a>


    <!-- KHUSUS ADMIN -->

    <?php if ($level_sidebar == 'admin'): ?>

        <a href="pengguna.php">
            👤 Kelola Pengguna
        </a>

    <?php endif; ?>


    <!-- ADMIN DAN PETUGAS -->

    <a href="buku.php" class="aktif">
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


    <!-- KHUSUS PETUGAS -->

    <?php if ($level_sidebar == 'petugas'): ?>

        <a href="kondisi_buku.php">
            📚 Cek Kondisi Buku
        </a>

    <?php endif; ?>


    <!-- LOGOUT -->

    <a href="logout.php" class="logout">
        🚪 Keluar
    </a>

</div>


<!-- =========================
     KONTEN LAPORAN
========================= -->

<div class="konten">


    <div class="kepala">

        <h2>
            PERPUSTAKAAN SEKOLAH SMK N 7 YOGYAKARTA
        </h2>

        <h3>
            LAPORAN DATA BUKU
        </h3>

        <p>
            Dicetak Tanggal:
            <?= $tgl_singkat ?>
        </p>

    </div>


    <!-- FILTER KATEGORI -->

    <div class="form-pilih">

        <form method="get" action="cetak_buku.php">

            <label>
                Pilih Kategori Buku:
            </label>


            <select
                name="kategori"
                onchange="this.form.submit()"
            >

                <option
                    value="semua"
                    <?= ($filter_kategori == "semua") ? "selected" : "" ?>
                >
                    Semua Kategori
                </option>


                <?php while($kat = mysqli_fetch_assoc($kategori_list)): ?>

                    <option
                        value="<?= htmlspecialchars($kat['kategori']) ?>"
                        <?= ($filter_kategori == $kat['kategori']) ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($kat['kategori']) ?>
                    </option>

                <?php endwhile; ?>


            </select>


            <button
                type="button"
                class="btn-lihat"
                onclick="location.href='cetak_buku.php'"
            >
                Tampilkan Semua
            </button>


        </form>

    </div>


    <!-- TABEL LAPORAN -->

    <table>

        <tr>

            <th>No</th>
            <th>Kode Buku</th>
            <th>Judul Buku</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>Kategori</th>
            <th>Jumlah</th>

        </tr>


        <?php

        $no = 1;

        if(mysqli_num_rows($buku) > 0):

            while($b = mysqli_fetch_assoc($buku)):

        ?>

                <tr>

                    <td>
                        <?= $no ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['kode_buku']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['judul']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['penulis']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['penerbit']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['tahun']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['kategori']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['jumlah']) ?>
                    </td>

                </tr>


                <?php $no++; ?>


            <?php endwhile; ?>


        <?php else: ?>


            <tr>

                <td
                    colspan="8"
                    style="text-align:center;padding:30px;"
                >
                    ⚠️ Tidak ada buku sesuai kategori.
                </td>

            </tr>


        <?php endif; ?>


    </table>


    <!-- TANDA TANGAN -->

    <div class="tanda-tangan">


        <div class="kiri">

            <div class="garis-ttd"></div>

            <p class="nama-jabatan">
                Administrator Perpustakaan
            </p>

        </div>


        <div class="kanan">

            <p>
                Yogyakarta,
                <?= $tgl_lengkap ?>
            </p>

            <div class="garis-ttd"></div>

            <p class="nama-jabatan">
                Petugas Perpustakaan
            </p>

        </div>


    </div>


    <!-- TOMBOL CETAK -->

    <div class="tombol">

        <br><br>


        <button
            class="btn-cetak"
            onclick="window.print()"
        >
            🖨️ KLIK DISINI UNTUK MENCETAK
        </button>


        <br>


        <a
            href="buku.php"
            class="kembali"
        >
            ← Kembali ke Daftar Buku
        </a>


    </div>


</div>


</body>
</html>