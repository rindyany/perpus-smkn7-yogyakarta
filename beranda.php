<?php
include "config.php";
cek_login();

// ======================================================
// DATA USER UNTUK SIDEBAR
// ======================================================
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


// ======================================================
// AMBIL DATA STATISTIK
// ======================================================

// Jumlah buku
$jml_buku = 0;

$res = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM buku"
);

if ($res) {
    $d = mysqli_fetch_assoc($res);
    $jml_buku = $d['total'];
}


// Jumlah anggota
$jml_anggota = 0;

$res = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pengguna WHERE level='siswa'"
);

if ($res) {
    $d = mysqli_fetch_assoc($res);
    $jml_anggota = $d['total'];
}


// Buku sedang dipinjam
$sedang_dipinjam = 0;

$res = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM peminjaman WHERE status='dipinjam'"
);

if ($res) {
    $d = mysqli_fetch_assoc($res);
    $sedang_dipinjam = $d['total'];
}


// Riwayat selesai
$riwayat_selesai = 0;

$res = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM peminjaman WHERE status='dikembalikan'"
);

if ($res) {
    $d = mysqli_fetch_assoc($res);
    $riwayat_selesai = $d['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Beranda Perpustakaan</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI', sans-serif;
        }

        :root{
            --pink-soft:#F8C8DC;
            --pink-dark:#E84A7F;
            --pink-light:#FFF0F5;
            --text-dark:#5C2337;
            --biru:#4FC3F7;
            --hijau:#81C784;
            --ungu:#BA68C8;
            --abu:#888;
        }

        body{
            background:var(--pink-light);
            display:flex;
            min-height:100vh;
        }


        /* SIDEBAR */

        .sidebar{
            width:260px;
            background:white;
            box-shadow:2px 0 10px rgba(232,74,127,0.15);
            position:fixed;
            height:100vh;
            overflow:auto;
        }


        .sidebar-header{
            background:linear-gradient(
                90deg,
                var(--pink-soft),
                var(--pink-dark)
            );

            color:white;
            padding:20px 15px;
            font-size:20px;
            font-weight:bold;

            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
        }

        .sidebar-header .emoji-logo{
            font-size:28px;
        }


        /* USER SIDEBAR */

        .sidebar-user{
            padding:20px 15px;
            text-align:center;
            border-bottom:1px solid var(--pink-soft);
        }

        .sidebar-user strong{
            color:var(--pink-dark);
            display:block;
            font-size:20px;
            font-weight:bold;
            margin-bottom:5px;
        }

        .sidebar-user small{
            color:var(--text-dark);
            font-size:16px;
        }


        /* MENU */

        .sidebar-menu{
            padding:10px 0;
        }

        .sidebar a{
            display:block;
            padding:12px 25px;
            color:var(--text-dark);
            text-decoration:none;
            transition:0.2s;
            margin:3px 0;
            border-left:3px solid transparent;
        }

        .sidebar a:hover,
        .sidebar a.aktif{
            background:var(--pink-light);
            border-left-color:var(--pink-dark);
            color:var(--pink-dark);
            font-weight:bold;
        }

        .sidebar a.logout{
            margin-top:10px;
            color:#C62828;
        }


        /* KONTEN */

        .konten{
            margin-left:260px;
            padding:25px;
            width:calc(100% - 260px);
        }

        h2{
            color:var(--pink-dark);
            font-size:24px;
            margin-bottom:25px;
        }


        /* STATISTIK */

        .grid-kotak{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
            margin-bottom:30px;
        }

        .kotak{
            background:white;
            padding:25px;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(232,74,127,0.1);
            display:flex;
            align-items:center;
            gap:15px;
        }

        .kotak-icon{
            width:60px;
            height:60px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
        }

        .kotak:nth-child(1) .kotak-icon{
            background:var(--pink-soft);
            color:var(--pink-dark);
        }

        .kotak:nth-child(2) .kotak-icon{
            background:var(--biru);
            color:white;
        }

        .kotak:nth-child(3) .kotak-icon{
            background:var(--hijau);
            color:white;
        }

        .kotak:nth-child(4) .kotak-icon{
            background:var(--ungu);
            color:white;
        }

        .kotak-judul{
            font-size:14px;
            color:var(--abu);
            margin-bottom:5px;
        }

        .kotak-angka{
            font-size:28px;
            font-weight:bold;
            color:var(--text-dark);
        }


        /* GRAFIK */

        .card-grafik{
            background:white;
            padding:25px;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(232,74,127,0.1);
        }

        .grafik-judul{
            font-size:16px;
            font-weight:bold;
            color:var(--text-dark);
            margin-bottom:20px;
        }

        .grafik-wadah{
            position:relative;
            height:350px;
            width:100%;
        }


        /* RESPONSIVE */

        @media(max-width:1000px){

            .grid-kotak{
                grid-template-columns:repeat(2,1fr);
            }

        }

        @media(max-width:600px){

            .sidebar{
                width:220px;
            }

            .konten{
                margin-left:220px;
                width:calc(100% - 220px);
                padding:15px;
            }

            .grid-kotak{
                grid-template-columns:1fr;
            }

        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-header">
        <div class="emoji-logo">📚</div>
        <span>PERPUSTAKAAN</span>
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
        <a href="beranda.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'beranda.php' ? 'aktif' : ''; ?>">
            🏠 Beranda
        </a>


        <!-- KHUSUS ADMIN -->
        <?php if ($level_sidebar == 'admin') : ?>

            <a href="pengguna.php"
               class="<?= basename($_SERVER['PHP_SELF']) == 'pengguna.php' ? 'aktif' : ''; ?>">
                👤 Kelola Pengguna
            </a>

        <?php endif; ?>


        <!-- ADMIN DAN PETUGAS -->
        <a href="buku.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'aktif' : ''; ?>">
            📚 Kelola Buku
        </a>


        <a href="peminjaman.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'aktif' : ''; ?>">
            📖 Transaksi Pinjam
        </a>


        <a href="riwayat_peminjaman.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat_peminjaman.php' ? 'aktif' : ''; ?>">
            📋 Riwayat Peminjaman
        </a>


        <a href="cari_ulasan.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'cari_ulasan.php' ? 'aktif' : ''; ?>">
            🔍 Pencarian Ulasan
        </a>


        <!-- KHUSUS PETUGAS -->
        <?php if ($level_sidebar == 'petugas') : ?>

            <a href="kondisi_buku.php"
               class="<?= basename($_SERVER['PHP_SELF']) == 'kondisi_buku.php' ? 'aktif' : ''; ?>">
                📚 Cek Kondisi Buku
            </a>

        <?php endif; ?>


        <!-- LOGOUT -->
        <a href="logout.php" class="logout">
            🚪 Keluar
        </a>

    </div>

</div>


<!-- KONTEN -->

<div class="konten">

    <h2>🏠 Beranda Perpustakaan</h2>


    <div class="grid-kotak">


        <div class="kotak">

            <div class="kotak-icon">📚</div>

            <div>

                <div class="kotak-judul">
                    Jumlah Buku
                </div>

                <div class="kotak-angka">
                    <?= $jml_buku ?>
                </div>

            </div>

        </div>


        <div class="kotak">

            <div class="kotak-icon">👥</div>

            <div>

                <div class="kotak-judul">
                    Jumlah Anggota
                </div>

                <div class="kotak-angka">
                    <?= $jml_anggota ?>
                </div>

            </div>

        </div>


        <div class="kotak">

            <div class="kotak-icon">📖</div>

            <div>

                <div class="kotak-judul">
                    Sedang Dipinjam
                </div>

                <div class="kotak-angka">
                    <?= $sedang_dipinjam ?>
                </div>

            </div>

        </div>


        <div class="kotak">

            <div class="kotak-icon">✅</div>

            <div>

                <div class="kotak-judul">
                    Riwayat Selesai
                </div>

                <div class="kotak-angka">
                    <?= $riwayat_selesai ?>
                </div>

            </div>

        </div>

    </div>


    <!-- GRAFIK -->

    <div class="card-grafik">

        <div class="grafik-judul">
            📊 Statistik Data Perpustakaan
        </div>

        <div class="grafik-wadah">
            <canvas id="grafikStatistik"></canvas>
        </div>

    </div>

</div>


<script>

const ctx = document
    .getElementById('grafikStatistik')
    .getContext('2d');


new Chart(ctx, {

    type: 'bar',

    data: {

        labels: [
            'Jumlah Buku',
            'Jumlah Anggota',
            'Sedang Dipinjam',
            'Riwayat Selesai'
        ],

        datasets: [{

            label: 'Jumlah',

            data: [
                <?= $jml_buku ?>,
                <?= $jml_anggota ?>,
                <?= $sedang_dipinjam ?>,
                <?= $riwayat_selesai ?>
            ],

            backgroundColor: [
                'rgba(248, 200, 220, 0.85)',
                'rgba(79, 195, 247, 0.85)',
                'rgba(129, 199, 132, 0.85)',
                'rgba(186, 104, 200, 0.85)'
            ],

            borderColor: [
                '#E84A7F',
                '#4FC3F7',
                '#81C784',
                '#BA68C8'
            ],

            borderWidth: 2,
            borderRadius: 8

        }]

    },

    options: {

        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                display: false
            }
        },

        scales: {

            y: {

                beginAtZero: true,

                ticks: {
                    stepSize: 1
                }

            }

        }

    }

});

</script>

</body>
</html>