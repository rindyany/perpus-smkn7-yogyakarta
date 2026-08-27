<?php
// Mencegah error "Undefined array key" untuk nama siswa
$nama_siswa = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? 'Siswa / Anggota';
?>

<!-- KODE SIDEBAR MILIKMU -->
<div class="sidebar">
    <div class="brand">
        <!-- Logo / Judul Perpustakaan Kamu -->
        <h2>PERPUSTAKAAN</h2>
    </div>
    
    <!-- Profil Pengguna Sidebar -->
    <div class="user-info" style="margin-bottom: 20px;">
        <h4 style="margin: 0; color: #D6336C;"><?php echo htmlspecialchars($nama_siswa); ?></h4>
        <small style="color: #888;">Siswa / Anggota</small>
    </div>

    <!-- Menu Navigasi -->
    <ul class="nav-links" style="list-style: none; padding: 0;">
        <li><a href="beranda.php">🏠 Beranda</a></li>
        <li><a href="katalog_buku.php">📚 Katalog Buku</a></li>
        <li><a href="pinjaman_saya.php">📖 Pinjaman Saya</a></li>
        <li><a href="riwayat_pinjam.php">📋 Riwayat Peminjaman</a></li>
        <!-- Menu Laporan yang sudah diganti menjadi Ulasan Saya -->
        <li><a href="ulasan_saya.php">💬 Ulasan Saya</a></li>
        <li><a href="logout.php">🚪 Keluar</a></li>
    </ul>
</div>