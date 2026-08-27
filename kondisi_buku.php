<?php

include "config.php";
cek_login();


// ======================================================
// CEK HAK AKSES
// KHUSUS PETUGAS
// ======================================================

if (
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'petugas'
) {

    header("Location: beranda.php");
    exit;

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
// BUAT TABEL KONDISI BUKU JIKA BELUM ADA
// ======================================================

mysqli_query(
    $koneksi,
    "CREATE TABLE IF NOT EXISTS kondisi_buku (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_buku INT NOT NULL,
        kondisi VARCHAR(100) NOT NULL,
        keterangan TEXT NULL,
        tanggal DATE NOT NULL,
        dibuat_oleh INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);


// ======================================================
// SIMPAN KONDISI BUKU
// ======================================================

if (isset($_POST['simpan'])) {

    $id_buku = intval($_POST['id_buku'] ?? 0);

    $kondisi = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['kondisi'] ?? '')
    );

    $keterangan = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['keterangan'] ?? '')
    );

    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

    $id_petugas = intval($_SESSION['id'] ?? 0);


    if ($id_buku <= 0 || $kondisi == '') {

        echo "
        <script>
            alert('Buku dan kondisi wajib dipilih!');
            history.back();
        </script>
        ";

        exit;

    }


    // CEK BUKU

    $cek_buku = mysqli_query(
        $koneksi,
        "SELECT id FROM buku WHERE id='$id_buku'"
    );


    if (mysqli_num_rows($cek_buku) == 0) {

        echo "
        <script>
            alert('Data buku tidak ditemukan!');
            history.back();
        </script>
        ";

        exit;

    }


    // SIMPAN DATA KONDISI

    $query_simpan = mysqli_query(
        $koneksi,
        "INSERT INTO kondisi_buku
        (
            id_buku,
            kondisi,
            keterangan,
            tanggal,
            dibuat_oleh
        )
        VALUES
        (
            '$id_buku',
            '$kondisi',
            '$keterangan',
            '$tanggal',
            '$id_petugas'
        )"
    );


    if ($query_simpan) {

        echo "
        <script>
            alert('Kondisi buku berhasil dicatat!');
            location.href='kondisi_buku.php';
        </script>
        ";

        exit;

    } else {

        echo "
        <script>
            alert('Gagal menyimpan kondisi buku!');
            history.back();
        </script>
        ";

        exit;

    }

}


// ======================================================
// HAPUS DATA KONDISI
// ======================================================

if (isset($_GET['hapus'])) {

    $id_hapus = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM kondisi_buku
         WHERE id='$id_hapus'"
    );

    echo "
    <script>
        alert('Data kondisi buku berhasil dihapus!');
        location.href='kondisi_buku.php';
    </script>
    ";

    exit;

}


// ======================================================
// AMBIL DATA BUKU
// ======================================================

$buku = mysqli_query(
    $koneksi,
    "SELECT *
     FROM buku
     ORDER BY judul ASC"
);


// ======================================================
// PENCARIAN
// ======================================================

$cari = trim($_GET['cari'] ?? '');

if ($cari != '') {

    $kata_cari = mysqli_real_escape_string(
        $koneksi,
        $cari
    );

    $query_kondisi = "
        SELECT
            kondisi_buku.*,
            buku.kode_buku,
            buku.judul,
            buku.penulis
        FROM kondisi_buku

        INNER JOIN buku
            ON kondisi_buku.id_buku = buku.id

        WHERE
            buku.judul LIKE '%$kata_cari%'
            OR buku.kode_buku LIKE '%$kata_cari%'
            OR kondisi_buku.kondisi LIKE '%$kata_cari%'
            OR kondisi_buku.keterangan LIKE '%$kata_cari%'

        ORDER BY
            kondisi_buku.tanggal DESC,
            kondisi_buku.id DESC
    ";

} else {

    $query_kondisi = "
        SELECT
            kondisi_buku.*,
            buku.kode_buku,
            buku.judul,
            buku.penulis
        FROM kondisi_buku

        INNER JOIN buku
            ON kondisi_buku.id_buku = buku.id

        ORDER BY
            kondisi_buku.tanggal DESC,
            kondisi_buku.id DESC
    ";

}


$data_kondisi = mysqli_query(
    $koneksi,
    $query_kondisi
);


// ======================================================
// HITUNG STATISTIK
// ======================================================

$total_data = mysqli_num_rows($data_kondisi);

$jumlah_baik = 0;
$jumlah_rusak = 0;
$jumlah_sobek = 0;
$jumlah_hilang = 0;
$jumlah_lainnya = 0;


$statistik = mysqli_query(
    $koneksi,
    "SELECT kondisi, COUNT(*) AS total
     FROM kondisi_buku
     GROUP BY kondisi"
);


if ($statistik) {

    while ($s = mysqli_fetch_assoc($statistik)) {

        if ($s['kondisi'] == 'Baik') {

            $jumlah_baik = $s['total'];

        } elseif ($s['kondisi'] == 'Rusak') {

            $jumlah_rusak = $s['total'];

        } elseif ($s['kondisi'] == 'Sobek') {

            $jumlah_sobek = $s['total'];

        } elseif ($s['kondisi'] == 'Hilang') {

            $jumlah_hilang = $s['total'];

        } else {

            $jumlah_lainnya += $s['total'];

        }

    }

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

    <title>Cek Kondisi Buku</title>


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
            --kuning:#FF9800;
            --biru:#4FC3F7;
            --abu:#888;
        }


        body{
            background:var(--pink-light);
            display:flex;
            min-height:100vh;
        }


        /* =================================================
           SIDEBAR
           SAMA SEPERTI BERANDA
        ================================================= */

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


        /* =================================================
           KONTEN
        ================================================= */

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


        /* =================================================
           STATISTIK
        ================================================= */

        .statistik{
            display:grid;
            grid-template-columns:repeat(5, 1fr);
            gap:15px;
            margin-bottom:25px;
        }


        .stat-card{
            background:white;
            padding:20px;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(232,74,127,0.10);
        }


        .stat-icon{
            font-size:28px;
            margin-bottom:8px;
        }


        .stat-judul{
            font-size:13px;
            color:var(--abu);
        }


        .stat-angka{
            font-size:25px;
            font-weight:bold;
            color:var(--text-dark);
            margin-top:5px;
        }


        /* =================================================
           CARD
        ================================================= */

        .card{
            background:white;
            padding:25px;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(232,74,127,0.10);
            margin-bottom:25px;
        }


        .card-title{
            color:var(--text-dark);
            font-size:18px;
            font-weight:bold;
            margin-bottom:20px;
        }


        /* =================================================
           FORM
        ================================================= */

        .form-grid{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:20px;
        }


        .form-group{
            margin-bottom:5px;
        }


        .form-group.full{
            grid-column:1 / -1;
        }


        label{
            display:block;
            font-size:14px;
            font-weight:600;
            color:var(--text-dark);
            margin-bottom:8px;
        }


        select,
        input,
        textarea{
            width:100%;
            padding:12px 14px;
            border:2px solid var(--pink-soft);
            border-radius:8px;
            font-size:14px;
            background:white;
            outline:none;
        }


        select:focus,
        input:focus,
        textarea:focus{
            border-color:var(--pink-dark);
        }


        textarea{
            resize:vertical;
            min-height:100px;
        }


        .btn-simpan{
            margin-top:20px;
            width:100%;

            background:linear-gradient(
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
        }


        .btn-simpan:hover{
            opacity:0.9;
        }


        /* =================================================
           PENCARIAN
        ================================================= */

        .form-cari{
            display:flex;
            gap:10px;
        }


        .input-cari{
            flex:1;
        }


        .btn-cari{
            background:var(--pink-dark);
            color:white;
            border:none;
            padding:12px 22px;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
        }


        .btn-reset{
            background:#eee;
            color:#555;
            text-decoration:none;
            padding:12px 18px;
            border-radius:8px;
            font-weight:bold;
        }


        /* =================================================
           TABEL
        ================================================= */

        .table-wrapper{
            overflow-x:auto;
        }


        table{
            width:100%;
            border-collapse:collapse;
        }


        th,
        td{
            padding:12px;
            border-bottom:1px solid #eee;
            text-align:left;
            vertical-align:middle;
        }


        th{
            background:var(--pink-soft);
            color:var(--text-dark);
            white-space:nowrap;
        }


        tr:hover td{
            background:#fff8fb;
        }


        .tengah{
            text-align:center;
        }


        /* =================================================
           BADGE KONDISI
        ================================================= */

        .badge{
            padding:7px 12px;
            border-radius:20px;
            color:white;
            font-size:12px;
            font-weight:bold;
            display:inline-block;
        }


        .baik{
            background:var(--hijau);
        }


        .rusak{
            background:var(--merah);
        }


        .sobek{
            background:var(--kuning);
        }


        .hilang{
            background:#6B21A8;
        }


        .lainnya{
            background:#64748B;
        }


        /* =================================================
           HAPUS
        ================================================= */

        .btn-hapus{
            background:var(--merah);
            color:white;
            text-decoration:none;
            padding:8px 12px;
            border-radius:7px;
            font-size:12px;
            font-weight:bold;
        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media(max-width:1200px){

            .statistik{
                grid-template-columns:repeat(3, 1fr);
            }

        }


        @media(max-width:800px){

            .sidebar{
                width:220px;
            }


            .konten{
                margin-left:220px;
                width:calc(100% - 220px);
                padding:15px;
            }


            .statistik{
                grid-template-columns:repeat(2, 1fr);
            }


            .form-grid{
                grid-template-columns:1fr;
            }


            .form-group.full{
                grid-column:auto;
            }

        }


        @media(max-width:500px){

            .statistik{
                grid-template-columns:1fr;
            }


            .form-cari{
                flex-direction:column;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
     SAMA PERSIS DENGAN BERANDA
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


        <!-- KELOLA BUKU -->

        <a
            href="buku.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'aktif' : ''; ?>"
        >
            📚 Kelola Buku
        </a>


        <!-- TRANSAKSI -->

        <a
            href="peminjaman.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'aktif' : ''; ?>"
        >
            📖 Transaksi Pinjam
        </a>


        <!-- RIWAYAT -->

        <a
            href="riwayat_peminjaman.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat_peminjaman.php' ? 'aktif' : ''; ?>"
        >
            📋 Riwayat Peminjaman
        </a>


        <!-- ULASAN -->

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


    <h2>
        📚 Cek Kondisi Buku
    </h2>


    <!-- STATISTIK -->

    <div class="statistik">


        <div class="stat-card">

            <div class="stat-icon">
                📋
            </div>

            <div class="stat-judul">
                Total Laporan
            </div>

            <div class="stat-angka">
                <?= $total_data ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <div class="stat-judul">
                Kondisi Baik
            </div>

            <div class="stat-angka">
                <?= $jumlah_baik ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⚠️
            </div>

            <div class="stat-judul">
                Buku Rusak
            </div>

            <div class="stat-angka">
                <?= $jumlah_rusak ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                📄
            </div>

            <div class="stat-judul">
                Buku Sobek
            </div>

            <div class="stat-angka">
                <?= $jumlah_sobek ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ❌
            </div>

            <div class="stat-judul">
                Buku Hilang
            </div>

            <div class="stat-angka">
                <?= $jumlah_hilang ?>
            </div>

        </div>


    </div>


    <!-- FORM INPUT KONDISI -->

    <div class="card">


        <div class="card-title">

            📝 Catat Kondisi Buku

        </div>


        <form method="POST">


            <div class="form-grid">


                <!-- PILIH BUKU -->

                <div class="form-group">

                    <label>
                        📚 Pilih Buku
                    </label>


                    <select
                        name="id_buku"
                        required
                    >

                        <option value="">
                            — Pilih Buku —
                        </option>


                        <?php while ($b = mysqli_fetch_assoc($buku)): ?>

                            <option value="<?= $b['id'] ?>">

                                <?= htmlspecialchars($b['kode_buku']) ?>

                                -

                                <?= htmlspecialchars($b['judul']) ?>

                            </option>

                        <?php endwhile; ?>


                    </select>

                </div>


                <!-- KONDISI -->

                <div class="form-group">

                    <label>
                        🔎 Kondisi Buku
                    </label>


                    <select
                        name="kondisi"
                        required
                    >

                        <option value="">
                            — Pilih Kondisi —
                        </option>

                        <option value="Baik">
                            ✅ Baik
                        </option>

                        <option value="Rusak">
                            ⚠️ Rusak
                        </option>

                        <option value="Sobek">
                            📄 Sobek
                        </option>

                        <option value="Hilang">
                            ❌ Hilang
                        </option>

                        <option value="Lainnya">
                            📝 Lainnya
                        </option>

                    </select>

                </div>


                <!-- TANGGAL -->

                <div class="form-group">

                    <label>
                        📅 Tanggal Pemeriksaan
                    </label>


                    <input
                        type="date"
                        name="tanggal"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >

                </div>


                <!-- KETERANGAN -->

                <div class="form-group full">

                    <label>
                        📝 Keterangan
                    </label>


                    <textarea
                        name="keterangan"
                        placeholder="Contoh: Halaman 15 sampai 20 sobek, cover buku rusak, buku tidak ditemukan, dan sebagainya..."
                    ></textarea>

                </div>


            </div>


            <button
                type="submit"
                name="simpan"
                class="btn-simpan"
            >

                💾 Simpan Kondisi Buku

            </button>


        </form>


    </div>


    <!-- PENCARIAN -->

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
                placeholder="Cari kode buku, judul buku, kondisi, atau keterangan..."
            >


            <button
                type="submit"
                class="btn-cari"
            >

                🔍 Cari

            </button>


            <?php if ($cari != ''): ?>

                <a
                    href="kondisi_buku.php"
                    class="btn-reset"
                >

                    ↩ Reset

                </a>

            <?php endif; ?>


        </form>


    </div>


    <!-- TABEL KONDISI BUKU -->

    <div class="card">


        <div class="card-title">

            📋 Daftar Kondisi Buku

        </div>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th>No</th>
                        <th>Kode Buku</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Kondisi</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (
                        $data_kondisi &&
                        mysqli_num_rows($data_kondisi) > 0
                    ): ?>


                        <?php $no = 1; ?>


                        <?php while (
                            $data = mysqli_fetch_assoc($data_kondisi)
                        ): ?>


                            <?php

                            $kondisi_class = 'lainnya';

                            if ($data['kondisi'] == 'Baik') {

                                $kondisi_class = 'baik';

                            } elseif ($data['kondisi'] == 'Rusak') {

                                $kondisi_class = 'rusak';

                            } elseif ($data['kondisi'] == 'Sobek') {

                                $kondisi_class = 'sobek';

                            } elseif ($data['kondisi'] == 'Hilang') {

                                $kondisi_class = 'hilang';

                            }

                            ?>


                            <tr>


                                <td class="tengah">

                                    <?= $no++ ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $data['kode_buku']
                                    ) ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $data['judul']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $data['penulis'] ?? '-'
                                    ) ?>

                                </td>


                                <td class="tengah">

                                    <span
                                        class="badge <?= $kondisi_class ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $data['kondisi']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= !empty($data['keterangan'])

                                        ? nl2br(
                                            htmlspecialchars(
                                                $data['keterangan']
                                            )
                                        )

                                        : '-'
                                    ?>

                                </td>


                                <td>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($data['tanggal'])
                                    ) ?>

                                </td>


                                <td class="tengah">

                                    <a
                                        href="kondisi_buku.php?hapus=<?= $data['id'] ?>"
                                        class="btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus data kondisi buku ini?')"
                                    >

                                        🗑 Hapus

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="tengah"
                                style="padding:30px;color:#888;"
                            >

                                📭 Belum ada data kondisi buku.

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>


</div>


</body>

</html>