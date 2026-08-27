<?php
session_start();
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
// PROSES KEMBALIKAN BUKU
// ======================================================
if (isset($_GET['kembalikan'])) {

    $id_pinjam = intval($_GET['kembalikan']);

    // Ambil data peminjaman
    $query_data = mysqli_query(
        $koneksi,
        "SELECT * FROM peminjaman WHERE id='$id_pinjam'"
    );

    $data = mysqli_fetch_assoc($query_data);

    if (!$data) {

        echo "<script>
            alert('Data peminjaman tidak ditemukan!');
            location.href='daftar_dipinjam.php';
        </script>";

        exit;
    }


    // Pastikan hanya buku yang masih dipinjam
    if ($data['status'] == 'dikembalikan') {

        echo "<script>
            alert('Buku ini sudah dikembalikan!');
            location.href='daftar_dipinjam.php';
        </script>";

        exit;
    }


    $id_buku = $data['id_buku'];
    $tgl_batas_kembali = $data['tgl_kembali'];
    $tgl_sekarang = date('Y-m-d');


    // ==================================================
    // HITUNG DENDA
    // Rp 1.000 per hari keterlambatan
    // ==================================================
    $denda = 0;

    if (
        !empty($tgl_batas_kembali) &&
        strtotime($tgl_sekarang) > strtotime($tgl_batas_kembali)
    ) {

        $hari_telat = floor(
            (
                strtotime($tgl_sekarang)
                -
                strtotime($tgl_batas_kembali)
            )
            /
            (60 * 60 * 24)
        );

        $denda = $hari_telat * 1000;
    }


    // ==================================================
    // MULAI TRANSAKSI DATABASE
    // ==================================================
    mysqli_begin_transaction($koneksi);

    try {

        // Update status peminjaman
        $update_peminjaman = mysqli_query(
            $koneksi,
            "UPDATE peminjaman SET
                status = 'dikembalikan',
                denda = '$denda'
            WHERE id = '$id_pinjam'"
        );

        if (!$update_peminjaman) {
            throw new Exception(
                mysqli_error($koneksi)
            );
        }


        // Tambahkan kembali stok buku
        $update_buku = mysqli_query(
            $koneksi,
            "UPDATE buku
            SET jumlah = jumlah + 1
            WHERE id = '$id_buku'"
        );

        if (!$update_buku) {
            throw new Exception(
                mysqli_error($koneksi)
            );
        }


        // Simpan perubahan
        mysqli_commit($koneksi);


        // Pesan berhasil
        $pesan = "Buku berhasil dikembalikan!";

        if ($denda > 0) {

            $pesan .= "\\nDenda: Rp "
                . number_format(
                    $denda,
                    0,
                    ',',
                    '.'
                );
        }


        echo "<script>
            alert('$pesan');
            location.href='daftar_dipinjam.php';
        </script>";

    } catch (Exception $e) {

        mysqli_rollback($koneksi);

        echo "<script>
            alert('Gagal mengembalikan buku!');
            location.href='daftar_dipinjam.php';
        </script>";
    }

    exit;
}


// ======================================================
// AMBIL DATA BUKU YANG SEDANG DIPINJAM
// ======================================================
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

    WHERE p.status IN ('dipinjam', 'terlambat')

    ORDER BY p.id DESC
";

$dipinjam = mysqli_query(
    $koneksi,
    $query
);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Daftar Buku Sedang Dipinjam</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
            --merah:#C62828;
            --kuning:#FF9800;
        }


        body{
            background:var(--pink-light);
            display:flex;
            min-height:100vh;
        }


        /* =============================================
           SIDEBAR
           SAMA SEPERTI BERANDA
        ============================================= */

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


        /* =============================================
           KONTEN
        ============================================= */

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


        .card{
            background:white;
            padding:25px;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(232,74,127,0.1);
        }


        /* =============================================
           TABEL
        ============================================= */

        .table th{
            background:var(--pink-soft);
            color:var(--pink-dark);
            border:none;
            white-space:nowrap;
        }


        .table td{
            vertical-align:middle;
        }


        /* =============================================
           BUTTON
        ============================================= */

        .btn-abu{
            background:#E5E7EB;
            color:#374151;
            border:none;
            border-radius:8px;
            font-weight:bold;
        }


        .btn-abu:hover{
            background:#D1D5DB;
            color:#111827;
        }


        .btn-hijau{
            background:#2E7D32;
            color:white;
            border:none;
            border-radius:7px;
            font-weight:bold;
        }


        .btn-hijau:hover{
            background:#256C28;
            color:white;
        }


        .btn-kuning{
            background:var(--kuning);
            color:white;
            border:none;
            border-radius:7px;
            font-weight:bold;
        }


        .btn-kuning:hover{
            background:#FB8C00;
            color:white;
        }


        /* =============================================
           BADGE
        ============================================= */

        .badge-terlambat{
            background:var(--merah);
            color:white;
            padding:7px 10px;
            border-radius:6px;
        }


        .badge-dipinjam{
            background:var(--kuning);
            color:white;
            padding:7px 10px;
            border-radius:6px;
        }


        /* =============================================
           RESPONSIVE
        ============================================= */

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


<!-- =====================================================
     SIDEBAR
     SAMA SEPERTI BERANDA
===================================================== -->

<div class="sidebar">


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
            class="<?=
                in_array(
                    basename($_SERVER['PHP_SELF']),
                    [
                        'peminjaman.php',
                        'daftar_dipinjam.php'
                    ]
                )
                ? 'aktif'
                : '';
            ?>"
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


<!-- =====================================================
     KONTEN
===================================================== -->

<div class="konten">


    <!-- KEMBALI -->

    <a
        href="peminjaman.php"
        class="btn btn-abu mb-4"
    >
        ← Kembali ke Transaksi Pinjam
    </a>


    <h2>
        📋 Daftar Buku Sedang Dipinjam
    </h2>


    <!-- TABEL -->

    <div class="card">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">


                <thead>

                    <tr>

                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Peminjam</th>
                        <th>Kode Buku</th>
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Status</th>
                        <th>Perpanjang</th>
                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (
                    $dipinjam &&
                    mysqli_num_rows($dipinjam) > 0
                ):

                    while (
                        $data = mysqli_fetch_assoc($dipinjam)
                    ):

                        $tgl_sekarang = date('Y-m-d');


                        // Cek keterlambatan

                        if (
                            strtotime($tgl_sekarang)
                            >
                            strtotime($data['tgl_kembali'])
                        ) {

                            $status = '
                                <span class="badge badge-terlambat">
                                    TERLAMBAT
                                </span>
                            ';

                        } else {

                            $status = '
                                <span class="badge badge-dipinjam">
                                    DIPINJAM
                                </span>
                            ';

                        }

                ?>


                    <tr>


                        <!-- NOMOR -->

                        <td>
                            <?= $no++ ?>
                        </td>


                        <!-- NIS -->

                        <td>
                            <?= htmlspecialchars($data['nis']) ?>
                        </td>


                        <!-- NAMA -->

                        <td>
                            <?= htmlspecialchars($data['nama']) ?>
                        </td>


                        <!-- KODE BUKU -->

                        <td>
                            <?= htmlspecialchars($data['kode_buku']) ?>
                        </td>


                        <!-- JUDUL BUKU -->

                        <td>
                            <?= htmlspecialchars($data['judul']) ?>
                        </td>


                        <!-- TANGGAL PINJAM -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime($data['tgl_pinjam'])
                            ) ?>

                        </td>


                        <!-- BATAS KEMBALI -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime($data['tgl_kembali'])
                            ) ?>

                        </td>


                        <!-- STATUS -->

                        <td>
                            <?= $status ?>
                        </td>


                        <!-- PERPANJANG -->

                        <td>

                            <button
                                class="btn btn-kuning btn-sm"
                                type="button"
                            >
                                Perpanjang
                            </button>

                        </td>


                        <!-- AKSI -->

                        <td>

                            <a
                                href="daftar_dipinjam.php?kembalikan=<?= $data['id'] ?>"
                                class="btn btn-hijau btn-sm"
                                onclick="return confirm('Yakin buku ini sudah dikembalikan?')"
                            >
                                Kembalikan
                            </a>

                        </td>


                    </tr>


                <?php

                    endwhile;

                else:

                ?>


                    <tr>

                        <td
                            colspan="10"
                            class="text-center text-muted py-4"
                        >
                            😊 Tidak ada buku yang sedang dipinjam
                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>
</html>