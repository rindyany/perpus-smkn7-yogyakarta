<?php
include "config.php";
cek_login();


// ======================================================
// HAK AKSES
// ======================================================
if (
    $_SESSION['level'] != "admin" &&
    $_SESSION['level'] != "petugas"
) {
    die("<script>
        alert('Akses Ditolak!');
        location.href='beranda.php';
    </script>");
}


// ======================================================
// DATA USER UNTUK SIDEBAR
// SAMA SEPERTI BERANDA
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
// SIMPAN PEMINJAMAN
// ======================================================
if (isset($_POST['simpan'])) {

    $id_siswa        = $_POST['id_siswa'];
    $id_buku         = $_POST['id_buku'];
    $tgl_pinjam_sql  = $_POST['tgl_pinjam_sql'];
    $tgl_kembali_sql = $_POST['tgl_kembali_sql'];


    // CEK JUMLAH BUKU
    $cek_buku = mysqli_fetch_assoc(
        mysqli_query(
            $koneksi,
            "SELECT jumlah FROM buku WHERE id='$id_buku'"
        )
    );


    if (!$cek_buku) {

        echo "<script>
            alert('Data buku tidak ditemukan!');
            history.back();
        </script>";

        exit;
    }


    if ($cek_buku['jumlah'] <= 0) {

        echo "<script>
            alert('Maaf, stok buku habis!');
            history.back();
        </script>";

        exit;
    }


    mysqli_begin_transaction($koneksi);


    try {

        mysqli_query(
            $koneksi,
            "INSERT INTO peminjaman
            (
                id_siswa,
                id_buku,
                tgl_pinjam,
                tgl_kembali,
                status,
                jumlah_perpanjang,
                denda
            )
            VALUES
            (
                '$id_siswa',
                '$id_buku',
                '$tgl_pinjam_sql',
                '$tgl_kembali_sql',
                'dipinjam',
                0,
                0
            )"
        );


        mysqli_query(
            $koneksi,
            "UPDATE buku
             SET jumlah = jumlah - 1
             WHERE id='$id_buku'"
        );


        mysqli_commit($koneksi);


        echo "<script>
            alert('Peminjaman Berhasil Dicatat! Stok buku berkurang.');
            location.href='peminjaman.php';
        </script>";

        exit;


    } catch (Exception $e) {

        mysqli_rollback($koneksi);

        echo "<script>
            alert('Gagal menyimpan peminjaman!');
            history.back();
        </script>";

        exit;
    }
}


// ======================================================
// AMBIL DATA SISWA
// ======================================================
$pengguna = mysqli_query(
    $koneksi,
    "SELECT * FROM pengguna
     WHERE level='siswa'
     ORDER BY nama ASC"
);


// ======================================================
// AMBIL DATA BUKU
// ======================================================
$buku = mysqli_query(
    $koneksi,
    "SELECT * FROM buku
     ORDER BY judul ASC"
);


// ======================================================
// TANGGAL
// ======================================================
$tgl_pinjam = date('d/m/Y');

$tgl_pinjam_sql = date('Y-m-d');


$tgl_kembali = date(
    'd/m/Y',
    strtotime('+7 days')
);


$tgl_kembali_sql = date(
    'Y-m-d',
    strtotime('+7 days')
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Transaksi Pinjam</title>


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
            --merah:#C62828;
            --hijau:#2E7D32;
            --abu:#888;
            --kuning:#FF9800;
            --biru-muda:#00BFA5;
        }


        body{
            background:var(--pink-light);
            display:flex;
            min-height:100vh;
        }


        /* =========================================
           SIDEBAR
           SAMA SEPERTI BERANDA
        ========================================= */

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


        /* =========================================
           KONTEN
        ========================================= */

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


        /* =========================================
           FORM
        ========================================= */

        .form-box{
            background:white;
            padding:30px;
            border-radius:14px;
            max-width:600px;
            box-shadow:
                0 4px 15px
                rgba(232,74,127,0.12);
        }


        .form-group{
            margin-bottom:20px;
        }


        label{
            display:block;
            font-size:14px;
            font-weight:600;
            color:var(--text-dark);
            margin-bottom:8px;
        }


        select,
        input{
            width:100%;
            padding:12px 14px;
            border:2px solid var(--pink-soft);
            border-radius:8px;
            font-size:14px;
            background:white;
            outline:none;
        }


        select:focus,
        input:focus{
            border-color:var(--pink-dark);
        }


        input[readonly]{
            background:#FFF5F8;
            color:#555;
            cursor:default;
        }


        .keterangan{
            font-size:12px;
            color:#888;
            margin-top:7px;
        }


        /* =========================================
           TOMBOL
        ========================================= */

        .btn-simpan{
            width:100%;

            background:
                linear-gradient(
                    90deg,
                    var(--pink-soft),
                    var(--pink-dark)
                );

            color:white;
            border:none;
            padding:13px;
            font-size:15px;
            font-weight:bold;
            border-radius:8px;
            cursor:pointer;
            transition:0.2s;
        }


        .btn-simpan:hover{
            opacity:0.9;
            transform:translateY(-1px);
        }


        .tombol-bawah{
            margin-top:20px;
            display:flex;
            flex-direction:column;
            gap:10px;
            max-width:600px;
        }


        .btn-lihat{
            background:var(--biru-muda);
            color:white;
            border:none;
            padding:12px 20px;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
            text-decoration:none;
            font-size:14px;
            text-align:center;
            transition:0.2s;
        }


        .btn-lihat:hover{
            background:#00AB94;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media(max-width:800px){

            .sidebar{
                width:220px;
            }


            .konten{
                margin-left:220px;
                width:calc(100% - 220px);
                padding:15px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================
     SIDEBAR
     SAMA SEPERTI BERANDA
========================================= -->

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



<!-- =========================================
     KONTEN
========================================= -->

<div class="konten">


    <h2>
        📖 Transaksi Pinjam
    </h2>


    <div class="form-box">


        <form method="post">


            <!-- SISWA -->

            <div class="form-group">

                <label>
                    NIS - Nama Anggota / Siswa
                </label>


                <select name="id_siswa" required>

                    <option value="">
                        — Pilih Anggota —
                    </option>


                    <?php while($a = mysqli_fetch_assoc($pengguna)): ?>

                        <option value="<?= $a['id'] ?>">

                            <?= htmlspecialchars($a['nis']) ?>
                            -
                            <?= htmlspecialchars($a['nama']) ?>

                        </option>

                    <?php endwhile; ?>


                </select>

            </div>



            <!-- BUKU -->

            <div class="form-group">

                <label>
                    Kode - Judul Buku
                </label>


                <select name="id_buku" required>

                    <option value="">
                        — Pilih Buku —
                    </option>


                    <?php while($b = mysqli_fetch_assoc($buku)): ?>

                        <option value="<?= $b['id'] ?>">

                            <?= htmlspecialchars($b['kode_buku']) ?>
                            -
                            <?= htmlspecialchars($b['judul']) ?>

                        </option>

                    <?php endwhile; ?>


                </select>

            </div>



            <!-- TANGGAL PINJAM -->

            <div class="form-group">

                <label>
                    📅 Tanggal Pinjam
                </label>


                <input
                    type="text"
                    value="<?= $tgl_pinjam ?>"
                    readonly
                >


                <input
                    type="hidden"
                    name="tgl_pinjam_sql"
                    value="<?= $tgl_pinjam_sql ?>"
                >

            </div>



            <!-- TANGGAL KEMBALI -->

            <div class="form-group">

                <label>
                    📆 Batas Pengembalian
                </label>


                <input
                    type="text"
                    value="<?= $tgl_kembali ?>"
                    readonly
                >


                <input
                    type="hidden"
                    name="tgl_kembali_sql"
                    value="<?= $tgl_kembali_sql ?>"
                >


                <p class="keterangan">

                    ℹ️ Batas pengembalian otomatis
                    7 hari setelah pinjam

                </p>

            </div>



            <!-- SIMPAN -->

            <button
                type="submit"
                name="simpan"
                class="btn-simpan"
            >
                ✔ Simpan Peminjaman
            </button>


        </form>

    </div>



    <!-- TOMBOL BAWAH -->

    <div class="tombol-bawah">

        <a
            href="daftar_dipinjam.php"
            class="btn-lihat"
        >
            📋 Lihat Sedang Dipinjam
        </a>

    </div>


</div>


</body>
</html>