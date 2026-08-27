<?php 
include "config.php"; 
cek_login(); 

// ======================================================
// CEK LEVEL USER
// ======================================================
$level_sidebar = $_SESSION['level'] ?? '';
$nama_sidebar  = $_SESSION['nama'] ?? 'Pengguna';

// Hanya admin dan petugas yang boleh masuk
if (
    $level_sidebar != 'admin' &&
    $level_sidebar != 'petugas'
) {
    die("
        <script>
            alert('Akses ditolak!');
            window.location.href='beranda.php';
        </script>
    ");
}
?> 

<!DOCTYPE html> 
<html lang="id"> 

<head> 

  <meta charset="UTF-8"> 
  <title>Kelola Data Buku</title> 

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
        --hijau:#2E7D32;
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


    /* KONTEN */ 

    .konten{
        margin-left:260px;
        padding:25px;
        width:100%;
    } 

    .card{
        background:white;
        padding:25px;
        border-radius:14px;
        box-shadow:0 4px 12px rgba(232,74,127,0.1);
    } 

    .atasan{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:15px;
        flex-wrap:wrap;
        gap:10px;
    } 

    h2{
        color:var(--pink-dark);
    } 

    .btn-tambah{
        background:var(--pink-dark);
        color:white;
        padding:10px 20px;
        border-radius:8px;
        text-decoration:none;
        font-weight:bold;
    } 

    .btn-tambah:hover{
        background:#D6386C;
    } 

    .btn-cetak{
        background:var(--hijau);
        color:white;
        padding:10px 20px;
        border-radius:8px;
        text-decoration:none;
        font-weight:bold;
        margin-left:8px;
    } 

    .btn-cetak:hover{
        background:#276829;
    } 


    /* KOTAK PENCARIAN */ 

    .pencarian{
        margin-bottom:20px;
    } 

    .pencarian input{
        width:100%;
        max-width:400px;
        padding:12px 15px;
        border:2px solid var(--pink-soft);
        border-radius:8px;
        font-size:15px;
        outline:none;
    } 

    .pencarian input:focus{
        border-color:var(--pink-dark);
    } 


    /* TABEL */ 

    table{
        width:100%;
        border-collapse:collapse;
        margin-top:15px;
    } 

    th,
    td{
        border:1px solid #ddd;
        padding:10px;
        text-align:left;
        font-size:14px;
    } 

    th{
        background:var(--pink-soft);
        color:var(--text-dark);
        text-align:center;
    } 

    td{
        vertical-align:middle;
    } 

    .tengah{
        text-align:center;
    } 


    /* GAMBAR BUKU */ 

    .gambar-buku{
        width:80px;
        height:100px;
        object-fit:cover;
        border-radius:4px;
        border:1px solid #eee;
        display:block;
        margin:0 auto;
    } 

    .tidak-ada-gambar{
        color:#999;
        font-size:12px;
        text-align:center;
        padding:20px 0;
    } 


    /* TOMBOL AKSI */ 

    .btn-ubah{
        background:#FF9800;
        color:white;
        padding:5px 10px;
        border-radius:4px;
        text-decoration:none;
        font-size:13px;
        display:inline-block;
        margin:2px 0;
    } 

    .btn-hapus{
        background:var(--merah);
        color:white;
        padding:5px 10px;
        border-radius:4px;
        text-decoration:none;
        font-size:13px;
        display:inline-block;
        margin:2px 0;
    } 

    .btn-ubah:hover{
        background:#FB8C00;
    } 

    .btn-hapus:hover{
        background:#B71C1C;
    } 

  </style> 

</head> 


<body> 


<!-- SIDEBAR --> 

<div class="sidebar"> 

    <h2>📚 Perpustakaan</h2> 


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

    <?php if ($level_sidebar == 'admin') : ?>

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

    <?php if ($level_sidebar == 'petugas') : ?>

        <a href="kondisi_buku.php">
            📚 Cek Kondisi Buku
        </a>

    <?php endif; ?>


    <!-- LOGOUT -->

    <a href="logout.php" class="logout">
        🚪 Keluar
    </a> 

</div> 


<!-- ISI KONTEN --> 

<div class="konten"> 

    <div class="card"> 

        <div class="atasan"> 

            <h2>📖 Daftar Data Buku</h2> 

            <div> 

                <a href="buku_tambah.php" class="btn-tambah">
                    ➕ Tambah Buku
                </a> 

                <a href="cetak_buku.php" class="btn-cetak">
                    📄 Cetak Laporan
                </a> 

            </div> 

        </div> 


        <!-- PENCARIAN -->

        <div class="pencarian"> 

            <input 
                type="text" 
                id="cari" 
                placeholder="🔍 Cari buku berdasarkan kode, judul, penulis..." 
                onkeyup="cariBuku()"
            > 

        </div> 


        <!-- TABEL -->

        <table id="tabel-buku"> 

            <tr> 

                <th width="50">No</th> 
                <th width="100">Gambar</th> 
                <th width="100">Kode</th> 
                <th>Judul Buku</th> 
                <th>Penulis</th> 
                <th>Penerbit</th> 
                <th width="70">Tahun</th> 
                <th width="90">Kategori</th> 
                <th width="60">Jumlah</th> 
                <th width="140">Aksi</th> 

            </tr> 


            <?php 

            $buku = mysqli_query(
                $koneksi,
                "SELECT * FROM buku ORDER BY id DESC"
            ); 

            $no = 1; 

            while($b = mysqli_fetch_assoc($buku)): 

            ?> 


            <tr> 

                <td class="tengah">
                    <?= $no ?>
                </td> 


                <td class="tengah"> 

                    <?php  

                    if(!empty($b['gambar'])){ 

                        $lokasi_gambar = "gambar_buku/".$b['gambar']; 

                        echo "<img 
                            src='$lokasi_gambar' 
                            class='gambar-buku' 
                            alt='Cover Buku' 
                            onerror='this.style.display=\"none\";this.nextElementSibling.style.display=\"block\";'
                        >"; 

                        echo "<div 
                            class='tidak-ada-gambar' 
                            style='display:none;'
                        >
                            Gambar<br>tidak ada
                        </div>"; 

                    }else{ 

                        echo "<div class='tidak-ada-gambar'>
                            Belum ada<br>gambar
                        </div>"; 

                    } 

                    ?> 

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


                <td class="tengah">
                    <?= htmlspecialchars($b['tahun']) ?>
                </td> 


                <td>
                    <?= htmlspecialchars($b['kategori']) ?>
                </td> 


                <td class="tengah">
                    <?= htmlspecialchars($b['jumlah']) ?>
                </td> 


                <td class="tengah"> 

                    <a 
                        href="buku_ubah.php?id=<?= $b['id'] ?>" 
                        class="btn-ubah"
                    >
                        ✏️ Ubah
                    </a> 

                    <a 
                        href="buku_hapus.php?id=<?= $b['id'] ?>" 
                        class="btn-hapus" 
                        onclick="return confirm('Yakin hapus buku ini?')"
                    >
                        🗑️ Hapus
                    </a> 

                </td> 

            </tr> 


            <?php 

            $no++; 

            endwhile; 

            ?> 

        </table> 

    </div> 

</div> 


<!-- SCRIPT PENCARIAN --> 

<script> 

function cariBuku(){ 

    var kata = document
        .getElementById("cari")
        .value
        .toLowerCase(); 

    var tabel = document
        .getElementById("tabel-buku"); 

    var baris = tabel
        .getElementsByTagName("tr"); 


    for(var i = 1; i < baris.length; i++){ 

        var teks = baris[i]
            .textContent
            .toLowerCase(); 


        if(teks.indexOf(kata) > -1){ 

            baris[i].style.display = ""; 

        }else{ 

            baris[i].style.display = "none"; 

        } 

    } 

} 

</script> 


</body> 
</html>