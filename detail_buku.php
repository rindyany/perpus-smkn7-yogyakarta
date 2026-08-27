<?php
include "config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$q_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id = $id");
$buku = $q_buku ? mysqli_fetch_assoc($q_buku) : null;

if(!$buku){
    header("Location: index.php");
    exit;
}

$pesan = "";

// ===== Simpan ulasan baru (khusus siswa yang sudah login) =====
if(isset($_POST['kirim_ulasan'])){
    if(!isset($_SESSION['id']) || $_SESSION['level'] !== 'siswa'){
        $pesan = "Silakan login sebagai siswa dulu untuk memberi ulasan.";
    } else {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $komentar = trim($_POST['komentar'] ?? '');

        if($rating < 1 || $rating > 5){
            $pesan = "Pilih rating bintang dulu ya.";
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO ulasan (id_buku, id_pengguna, rating, komentar) VALUES (?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "iiis", $id, $_SESSION['id'], $rating, $komentar);
            mysqli_stmt_execute($stmt);
            header("Location: detail_buku.php?id=$id");
            exit;
        }
    }
}

// ===== Rata-rata rating & daftar ulasan =====
$rata_rating = 0;
$jumlah_ulasan = 0;
$daftar_ulasan = [];

$q_rating = @mysqli_query($koneksi, "SELECT COALESCE(AVG(rating),0) AS rata, COUNT(*) AS total FROM ulasan WHERE id_buku = $id");
if($q_rating){
    $r = mysqli_fetch_assoc($q_rating);
    $rata_rating = $r['rata'];
    $jumlah_ulasan = $r['total'];

    $q_list = mysqli_query($koneksi, "
        SELECT u.rating, u.komentar, u.dibuat_pada, p.nama
        FROM ulasan u
        JOIN pengguna p ON p.id = u.id_pengguna
        WHERE u.id_buku = $id
        ORDER BY u.dibuat_pada DESC
    ");
    while($row = mysqli_fetch_assoc($q_list)){
        $daftar_ulasan[] = $row;
    }
}
$tabel_ulasan_ada = ($q_rating !== false);

function render_bintang($rating){
    $bulat = round($rating);
    $html = "";
    for($i = 1; $i <= 5; $i++){
        $html .= $i <= $bulat ? "★" : "☆";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($buku['judul']) ?> — Perpustakaan SMK N 7</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
  :root{
    --rose-50:#FFF4F7;--rose-100:#FCE4EC;--rose-300:#F3A9C3;--rose-600:#D6336C;
    --rose-700:#B02358;--rose-900:#3E1626;--gold:#C9963E;--cream:#FFFDFB;
    --ink:#4A1F30;--ink-soft:#8A6373;--hijau:#2F9E5B;--hijau-bg:#E6F6EC;
    --merah-bg:#FDEAEA;--merah:#C43030;
  }
  body{background:var(--rose-50);color:var(--ink);}
  a{text-decoration:none;color:inherit;}

  nav{
    padding:18px 64px;background:rgba(255,244,247,0.95);
    border-bottom:1px solid rgba(214,51,108,.12);
    display:flex;align-items:center;justify-content:space-between;
  }
  .brand{font-family:'Fraunces',serif;font-weight:600;color:var(--rose-900);font-size:18px;}
  .balik{font-size:13.5px;color:var(--rose-700);font-weight:600;}

  .wrap{max-width:1000px;margin:0 auto;padding:44px 24px 80px;}

  .panel{
    display:grid;grid-template-columns:280px 1fr;gap:40px;
    background:var(--cream);border-radius:20px;padding:32px;
    border:1px solid rgba(214,51,108,.12);box-shadow:0 8px 24px rgba(62,22,38,.06);
  }
  .cover img{width:100%;border-radius:14px;box-shadow:0 10px 24px rgba(62,22,38,.18);}

  .judul{font-family:'Fraunces',serif;font-weight:600;font-size:28px;color:var(--rose-900);line-height:1.25;}
  .penulis{font-size:14.5px;color:var(--ink-soft);margin-top:6px;}

  .rating-ringkas{display:flex;align-items:center;gap:10px;margin-top:16px;}
  .rating-ringkas .bintang{color:var(--gold);font-size:20px;letter-spacing:2px;}
  .rating-ringkas .angka{font-weight:700;color:var(--rose-900);}
  .rating-ringkas .jumlah{color:var(--ink-soft);font-size:13px;}

  .info-grid{
    display:grid;grid-template-columns:repeat(2,1fr);gap:10px 24px;
    margin-top:22px;font-size:13.5px;
  }
  .info-grid div span.label{display:block;color:var(--ink-soft);font-size:11.5px;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;}
  .info-grid div span.nilai{color:var(--ink);font-weight:600;}

  .badge-stok{
    display:inline-block;margin-top:20px;font-size:12.5px;font-weight:700;
    padding:6px 16px;border-radius:999px;background:var(--hijau-bg);color:var(--hijau);
  }
  .badge-stok.habis{background:var(--merah-bg);color:var(--merah);}

  .section-title{
    font-family:'Fraunces',serif;font-weight:600;font-size:22px;color:var(--rose-900);
    margin:50px 0 20px;
  }

  .form-ulasan{
    background:var(--cream);border-radius:16px;padding:26px;
    border:1px solid rgba(214,51,108,.12);
  }
  .bintang-pilih{display:flex;gap:6px;font-size:28px;margin-bottom:14px;}
  .bintang-pilih label{cursor:pointer;color:#e5c9d3;transition:color .15s;}
  .bintang-pilih input{display:none;}
  .bintang-pilih input:checked ~ label,.bintang-pilih label:hover,.bintang-pilih label:hover ~ label{color:var(--gold);}
  .bintang-pilih{flex-direction:row-reverse;justify-content:flex-end;}

  .form-ulasan textarea{
    width:100%;padding:12px 14px;border-radius:10px;border:1.5px solid rgba(214,51,108,.25);
    font-size:14px;font-family:inherit;resize:vertical;min-height:80px;
  }
  .form-ulasan textarea:focus{outline:none;border-color:var(--rose-600);}
  .btn-kirim{
    margin-top:14px;background:var(--rose-600);color:#fff;border:none;
    padding:11px 28px;border-radius:999px;font-weight:700;font-size:13.5px;cursor:pointer;
    transition:background .2s ease;
  }
  .btn-kirim:hover{background:var(--rose-700);}

  .pesan{
    background:var(--merah-bg);color:var(--merah);padding:10px 16px;border-radius:10px;
    font-size:13px;margin-bottom:16px;font-weight:600;
  }
  .cta-login{
    background:var(--rose-100);color:var(--rose-700);padding:16px 20px;border-radius:12px;
    font-size:13.5px;font-weight:600;text-align:center;
  }
  .cta-login a{text-decoration:underline;}

  .daftar-ulasan{display:flex;flex-direction:column;gap:16px;margin-top:24px;}
  .kartu-ulasan{
    background:var(--cream);border-radius:14px;padding:18px 20px;
    border:1px solid rgba(214,51,108,.1);display:flex;gap:14px;
  }
  .avatar{
    width:38px;height:38px;border-radius:50%;background:var(--rose-600);color:#fff;
    display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;
  }
  .isi-ulasan .baris-atas{display:flex;align-items:center;gap:10px;margin-bottom:4px;}
  .isi-ulasan .nama{font-weight:700;font-size:13.5px;color:var(--rose-900);}
  .isi-ulasan .bintang-kecil{color:var(--gold);font-size:13px;letter-spacing:1px;}
  .isi-ulasan .tanggal{font-size:11.5px;color:var(--ink-soft);}
  .isi-ulasan .komentar{font-size:13.5px;color:var(--ink);line-height:1.5;margin-top:4px;}

  .ulasan-kosong{color:var(--ink-soft);font-size:13.5px;padding:20px 0;}

  @media(max-width:700px){
    .panel{grid-template-columns:1fr;}
    .cover{max-width:200px;margin:0 auto;}
  }
</style>
</head>
<body>

<nav>
  <span class="brand">📚 Perpustakaan Digital</span>
  <a href="index.php#katalog" class="balik">← Kembali ke Koleksi</a>
</nav>

<div class="wrap">

  <div class="panel">
    <div class="cover">
      <?php
        $src_gambar = !empty($buku['gambar']) ? "gambar_buku/".htmlspecialchars($buku['gambar']) : 'https://placehold.co/300x400?text=Tanpa+Sampul';
      ?>
      <img src="<?= $src_gambar ?>"
           alt="Sampul <?= htmlspecialchars($buku['judul']) ?>"
           onerror="this.src='https://placehold.co/300x400?text=Tanpa+Sampul'">
    </div>
    <div>
      <div class="judul"><?= htmlspecialchars($buku['judul']) ?></div>
      <div class="penulis">oleh <?= htmlspecialchars($buku['penulis']) ?></div>

      <div class="rating-ringkas">
        <span class="bintang"><?= render_bintang($rata_rating) ?></span>
        <span class="angka"><?= number_format($rata_rating, 1) ?></span>
        <span class="jumlah">(<?= (int)$jumlah_ulasan ?> ulasan)</span>
      </div>

      <div class="info-grid">
        <div><span class="label">Penerbit</span><span class="nilai"><?= htmlspecialchars($buku['penerbit'] ?: '-') ?></span></div>
        <div><span class="label">Tahun</span><span class="nilai"><?= htmlspecialchars($buku['tahun'] ?: '-') ?></span></div>
        <div><span class="label">Kategori</span><span class="nilai"><?= htmlspecialchars($buku['kategori'] ?: '-') ?></span></div>
        <div><span class="label">ISBN</span><span class="nilai"><?= htmlspecialchars($buku['isbn'] ?: '-') ?></span></div>
        <div><span class="label">Lokasi Rak</span><span class="nilai"><?= htmlspecialchars($buku['lokasi'] ?: '-') ?></span></div>
        <div><span class="label">Kode Buku</span><span class="nilai"><?= htmlspecialchars($buku['kode_buku'] ?: '-') ?></span></div>
      </div>

      <?php if($buku['jumlah'] > 0){ ?>
        <span class="badge-stok"><?= (int)$buku['jumlah'] ?> Tersedia</span>
      <?php } else { ?>
        <span class="badge-stok habis">Stok Habis</span>
      <?php } ?>
    </div>
  </div>

  <div class="section-title">Ulasan Pembaca</div>

  <?php if(!$tabel_ulasan_ada){ ?>
    <div class="ulasan-kosong">Fitur ulasan belum aktif — jalankan <code>ulasan.sql</code> di database dulu.</div>
  <?php } else { ?>

    <?php if(!empty($pesan)){ ?>
      <div class="pesan"><?= htmlspecialchars($pesan) ?></div>
    <?php } ?>

    <?php if(isset($_SESSION['id']) && $_SESSION['level'] === 'siswa'){ ?>
      <div class="form-ulasan">
        <form method="POST">
          <div class="bintang-pilih">
            <input type="radio" name="rating" value="5" id="r5"><label for="r5">★</label>
            <input type="radio" name="rating" value="4" id="r4"><label for="r4">★</label>
            <input type="radio" name="rating" value="3" id="r3"><label for="r3">★</label>
            <input type="radio" name="rating" value="2" id="r2"><label for="r2">★</label>
            <input type="radio" name="rating" value="1" id="r1"><label for="r1">★</label>
          </div>
          <textarea name="komentar" placeholder="Ceritakan pendapatmu tentang buku ini... (opsional)"></textarea>
          <button type="submit" name="kirim_ulasan" class="btn-kirim">Kirim Ulasan</button>
        </form>
      </div>
    <?php } else { ?>
      <div class="cta-login"><a href="login.php">Masuk sebagai siswa</a> dulu untuk memberi rating & ulasan.</div>
    <?php } ?>

    <div class="daftar-ulasan">
      <?php if(count($daftar_ulasan) == 0){ ?>
        <div class="ulasan-kosong">Belum ada ulasan. Jadilah yang pertama memberi ulasan buku ini!</div>
      <?php } else { foreach($daftar_ulasan as $u){ ?>
        <div class="kartu-ulasan">
          <div class="avatar"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
          <div class="isi-ulasan">
            <div class="baris-atas">
              <span class="nama"><?= htmlspecialchars($u['nama']) ?></span>
              <span class="bintang-kecil"><?= render_bintang($u['rating']) ?></span>
              <span class="tanggal"><?= date('d M Y', strtotime($u['dibuat_pada'])) ?></span>
            </div>
            <?php if(!empty($u['komentar'])){ ?>
              <div class="komentar"><?= nl2br(htmlspecialchars($u['komentar'])) ?></div>
            <?php } ?>
          </div>
        </div>
      <?php } } ?>
    </div>

  <?php } ?>

</div>

</body>
</html>