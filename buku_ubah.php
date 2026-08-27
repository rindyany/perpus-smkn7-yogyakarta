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
// AMBIL DATA BUKU
// ======================================================
$id = $_GET['id'] ?? '';

$b = mysqli_fetch_assoc(
    mysqli_query(
        $koneksi,
        "SELECT * FROM buku WHERE id='$id'"
    )
);


// ======================================================
// PROSES UBAH BUKU
// ======================================================
if(isset($_POST['simpan'])){

    $kode_buku = $_POST['kode_buku'];
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $gambar_lama = $_POST['gambar_lama'];


    // ==================================================
    // UPLOAD GAMBAR BARU
    // ==================================================
    $gambar_baru = $gambar_lama;


    if(
        isset($_FILES['gambar']) &&
        $_FILES['gambar']['name'] != ""
    ){

        $namagambar = time() . "_" . $_FILES['gambar']['name'];

        $tujuan = "gambar_buku/" . $namagambar;


        if(
            move_uploaded_file(
                $_FILES['gambar']['tmp_name'],
                $tujuan
            )
        ){

            $gambar_baru = $namagambar;


            // HAPUS GAMBAR LAMA
            if(
                $gambar_lama != "" &&
                file_exists("gambar_buku/" . $gambar_lama)
            ){

                unlink(
                    "gambar_buku/" . $gambar_lama
                );

            }

        }

    }


    // ==================================================
    // UPDATE DATA BUKU
    // ==================================================
    mysqli_query(
        $koneksi,
        "UPDATE buku SET

            kode_buku='$kode_buku',
            judul='$judul',
            penulis='$penulis',
            penerbit='$penerbit',
            tahun='$tahun',
            kategori='$kategori',
            jumlah='$jumlah',
            gambar='$gambar_baru'

        WHERE id='$id'"
    );


    echo "<script>
        alert('Buku berhasil diubah!');
        location.href='buku.php';
    </script>";

}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Ubah Buku</title>

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
            padding:30px;
            width:100%;
        }


        .card{
            background:white;
            padding:30px;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(232,74,127,0.1);
            max-width:600px;
        }


        h2{
            color:var(--pink-dark);
            margin-bottom:20px;
        }


        label{
            display:block;
            margin:12px 0 5px 0;
            font-weight:bold;
            color:var(--text-dark);
        }


        input,
        select{
            width:100%;
            padding:10px;
            border:2px solid var(--pink-soft);
            border-radius:8px;
            outline:none;
        }


        input:focus{
            border-color:var(--pink-dark);
        }


        button{
            padding:12px 25px;
            border:none;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
            margin-top:20px;
        }


        .btn-simpan{
            background:var(--pink-dark);
            color:white;
        }


        .btn-simpan:hover{
            background:#D6386C;
        }


        .btn-kembali{
            background:var(--pink-soft);
            color:var(--text-dark);
            margin-left:10px;
        }


        .gambar-saat-ini{
            max-width:150px;
            margin:10px 0;
            border:1px solid #ddd;
            padding:5px;
        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <h2>📚 Perpustakaan</h2>


    <!-- USER LOGIN -->

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

    <?php if($level_sidebar == 'admin'): ?>

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

    <?php if($level_sidebar == 'petugas'): ?>

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
     KONTEN
========================= -->

<div class="konten">

    <div class="card">

        <h2>✏️ Ubah Data Buku</h2>


        <form method="post" enctype="multipart/form-data">


            <label>Kode Buku</label>

            <input
                type="text"
                name="kode_buku"
                value="<?= htmlspecialchars($b['kode_buku'] ?? '') ?>"
                required
            >


            <label>Judul Buku</label>

            <input
                type="text"
                name="judul"
                value="<?= htmlspecialchars($b['judul'] ?? '') ?>"
                required
            >


            <label>Penulis</label>

            <input
                type="text"
                name="penulis"
                value="<?= htmlspecialchars($b['penulis'] ?? '') ?>"
                required
            >


            <label>Penerbit</label>

            <input
                type="text"
                name="penerbit"
                value="<?= htmlspecialchars($b['penerbit'] ?? '') ?>"
                required
            >


            <label>Tahun Terbit</label>

            <input
                type="text"
                name="tahun"
                value="<?= htmlspecialchars($b['tahun'] ?? '') ?>"
                required
            >


            <label>Kategori</label>

            <input
                type="text"
                name="kategori"
                value="<?= htmlspecialchars($b['kategori'] ?? '') ?>"
                required
            >


            <label>Jumlah Buku</label>

            <input
                type="number"
                name="jumlah"
                value="<?= htmlspecialchars($b['jumlah'] ?? '') ?>"
                required
            >


            <!-- GAMBAR SAAT INI -->

            <label>Gambar Buku Saat Ini</label>


            <?php if(!empty($b['gambar'])): ?>

                <img
                    src="gambar_buku/<?= htmlspecialchars($b['gambar']) ?>"
                    class="gambar-saat-ini"
                    alt="Gambar Buku"
                >

                <br>

            <?php else: ?>

                <em style="color:#999">
                    Belum ada gambar
                </em>

                <br>

            <?php endif; ?>


            <input
                type="hidden"
                name="gambar_lama"
                value="<?= htmlspecialchars($b['gambar'] ?? '') ?>"
            >


            <!-- UPLOAD GAMBAR BARU -->

            <label>
                Upload Gambar Baru
                (Kosongkan jika tidak diganti)
            </label>


            <input
                type="file"
                name="gambar"
                accept="image/*"
            >


            <!-- TOMBOL -->

            <button
                type="submit"
                name="simpan"
                class="btn-simpan"
            >
                💾 Simpan Perubahan
            </button>


            <button
                type="button"
                class="btn-kembali"
                onclick="location.href='buku.php'"
            >
                ← Kembali
            </button>


        </form>

    </div>

</div>


</body>
</html>