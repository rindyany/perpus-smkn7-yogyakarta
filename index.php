<?php
include "config.php";

// ===============================
// STATISTIK
// ===============================

$total_judul = 0;
$q_judul = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM buku"
);

if ($q_judul) {
    $data_judul = mysqli_fetch_assoc($q_judul);
    $total_judul = $data_judul['total'];
}


$total_anggota = 0;
$q_anggota = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pengguna WHERE level = 'siswa'"
);

if ($q_anggota) {
    $data_anggota = mysqli_fetch_assoc($q_anggota);
    $total_anggota = $data_anggota['total'];
}


$total_dipinjam = 0;
$q_dipinjam = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'"
);

if ($q_dipinjam) {
    $data_dipinjam = mysqli_fetch_assoc($q_dipinjam);
    $total_dipinjam = $data_dipinjam['total'];
}


// ===============================
// DATA GRAFIK
// ===============================

$labels_kategori = [];
$data_kategori = [];

$q_grafik = mysqli_query(
    $koneksi,
    "SELECT 
        b.kategori,
        COUNT(p.id) AS jumlah
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id
    GROUP BY b.kategori
    ORDER BY jumlah DESC
    LIMIT 6"
);

if ($q_grafik && mysqli_num_rows($q_grafik) > 0) {

    while ($row = mysqli_fetch_assoc($q_grafik)) {

        $labels_kategori[] =
            !empty($row['kategori'])
            ? $row['kategori']
            : 'Umum';

        $data_kategori[] =
            (int)$row['jumlah'];
    }

} else {

    $labels_kategori = [
        'Fiksi',
        'Sains',
        'Sejarah',
        'Komputer',
        'Teknologi'
    ];

    $data_kategori = [
        $total_dipinjam,
        12,
        8,
        15,
        5
    ];
}


// ===============================
// KOLEKSI BUKU
// ===============================

$koleksi_buku = [];

$q_koleksi = mysqli_query(
    $koneksi,
    "SELECT 
        id,
        judul,
        penulis,
        jumlah,
        gambar,
        kategori
    FROM buku
    ORDER BY id DESC
    LIMIT 24"
);

if ($q_koleksi) {

    while ($row = mysqli_fetch_assoc($q_koleksi)) {

        $row['kategori'] =
            !empty($row['kategori'])
            ? $row['kategori']
            : 'Umum';

        $koleksi_buku[] = $row;
    }
}


// ===============================
// DAFTAR KATEGORI
// ===============================

$daftar_kategori = [];

foreach ($koleksi_buku as $buku) {

    if (
        !in_array(
            $buku['kategori'],
            $daftar_kategori
        )
    ) {

        $daftar_kategori[] =
            $buku['kategori'];
    }
}

sort($daftar_kategori);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Perpustakaan SMK N 7 Yogyakarta
    </title>


    <!-- GOOGLE FONT -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- CHART JS -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        :root {

            --rose-50: #FFF4F7;
            --rose-100: #FCE4EC;
            --rose-300: #F3A9C3;
            --rose-600: #D6336C;
            --rose-700: #B02358;
            --rose-900: #3E1626;

            --gold: #C9963E;

            --cream: #FFFDFB;

            --ink: #4A1F30;
            --ink-soft: #8A6373;

            --hijau: #2F9E5B;
            --hijau-bg: #E6F6EC;

            --merah: #C43030;
            --merah-bg: #FDEAEA;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            background-color: var(--rose-50);

            background-image:
                linear-gradient(
                    180deg,
                    rgba(255, 244, 247, 0.72),
                    rgba(255, 244, 247, 0.88)
                ),
                url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1920&auto=format&fit=crop');

            background-size: cover;

            background-position: center;

            background-attachment: fixed;

            color: var(--ink);

            font-family:
                'Inter',
                sans-serif;

            -webkit-font-smoothing: antialiased;
        }


        a {
            text-decoration: none;
            color: inherit;
        }


        /* =========================
           HEADER ATAS
        ========================= */

        .top-header {

            background:
                var(--rose-900);

            color:
                rgba(
                    255,
                    255,
                    255,
                    0.85
                );

            font-size: 12.5px;

            padding:
                8px
                64px;

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;
        }


        .top-header span {

            display: flex;

            align-items: center;

            gap: 6px;
        }


        /* =========================
           NAVBAR
        ========================= */

        nav {

            display: flex;

            align-items:
                center;

            justify-content:
                space-between;

            padding:
                14px
                64px;

            position: sticky;

            top: 0;

            z-index: 100;

            background:
                rgba(
                    255,
                    244,
                    247,
                    0.95
                );

            backdrop-filter:
                blur(10px);

            border-bottom:
                1px solid
                rgba(
                    214,
                    51,
                    108,
                    0.12
                );

            box-shadow:
                0 4px 20px
                rgba(
                    62,
                    22,
                    38,
                    0.04
                );
        }


        /* BRAND */

        .brand {

            display: flex;

            align-items: center;

            gap: 14px;
        }


        /* LOGO */

        .logo-wrapper {

            width: 68px;

            height: 68px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 12px;

            background: #ffffff;

            padding: 6px;
        }


        .logo-wrapper img {

            width: 100%;

            height: 100%;

            object-fit: contain;

            display: block;
        }


        /* NAMA */

        .brand span {

            font-family:
                'Fraunces',
                serif;

            font-weight: 600;

            font-size: 19px;

            color:
                var(--rose-900);

            letter-spacing:
                0.2px;
        }


        /* MENU */

        .nav-menu {

            display: flex;

            list-style: none;

            gap: 24px;

            align-items: center;
        }


        .nav-menu a {

            font-size: 13px;

            font-weight: 500;

            color:
                var(--ink-soft);

            transition:
                color
                0.25s
                ease;

            padding:
                4px
                0;
        }


        .nav-menu a:hover {

            color:
                var(--rose-700);
        }


        .nav-links {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        /* BUTTON */

        .btn {

            display:
                inline-block;

            padding:
                10px
                22px;

            border-radius:
                999px;

            font-weight:
                600;

            font-size:
                13.5px;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease;
        }


        .btn-solid {

            background:
                var(--rose-600);

            color:
                #ffffff;

            box-shadow:
                0 6px 16px
                rgba(
                    214,
                    51,
                    108,
                    0.28
                );
        }


        .btn-solid:hover {

            background:
                var(--rose-700);

            transform:
                translateY(-2px);
        }


        .btn-ghost {

            border:
                1.5px solid
                var(--rose-300);

            color:
                var(--rose-700);

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.6
                );
        }


        .btn-ghost:hover {

            background:
                var(--rose-100);
        }


        /* =========================
           HERO
        ========================= */

        .hero {

            max-width:
                1180px;

            margin:
                0 auto;

            padding:
                70px
                40px
                40px;

            display: grid;

            grid-template-columns:
                1.1fr
                0.9fr;

            gap:
                60px;

            align-items:
                center;
        }


        .stamp {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            border:
                1px dashed
                var(--gold);

            color:
                var(--gold);

            font-family:
                'Fraunces',
                serif;

            font-weight:
                600;

            font-size:
                12.5px;

            letter-spacing:
                1.5px;

            text-transform:
                uppercase;

            padding:
                7px
                16px;

            border-radius:
                999px;

            margin-bottom:
                26px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.75
                );
        }


        .hero h1 {

            font-family:
                'Fraunces',
                serif;

            font-weight:
                600;

            font-size:
                52px;

            line-height:
                1.1;

            color:
                var(--rose-900);
        }


        .hero h1 em {

            color:
                var(--rose-600);
        }


        .hero p.lede {

            margin-top:
                22px;

            font-size:
                16px;

            line-height:
                1.7;

            color:
                var(--ink);

            max-width:
                500px;
        }


        .hero .btn-group {

            display: flex;

            gap:
                16px;

            margin-top:
                32px;

            flex-wrap:
                wrap;
        }


        .hero .btn {

            padding:
                14px
                30px;
        }


        /* =========================
           RAK BUKU
        ========================= */

        .shelf {

            display: flex;

            align-items:
                flex-end;

            gap:
                7px;

            height:
                220px;

            padding:
                0 14px;

            border-bottom:
                10px solid
                var(--rose-900);

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.4
                );
        }


        .spine {

            flex: 1;

            border-radius:
                5px
                5px
                2px
                2px;

            display: flex;

            align-items:
                flex-start;

            justify-content:
                center;

            padding-top:
                14px;

            color:
                #ffffff;

            font-family:
                'Fraunces',
                serif;

            font-size:
                11px;

            letter-spacing:
                2px;

            writing-mode:
                vertical-rl;

            text-transform:
                uppercase;

            cursor:
                pointer;

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;

            box-shadow:
                inset -3px 0 6px
                rgba(
                    0,
                    0,
                    0,
                    0.15
                );
        }


        .spine:hover {

            transform:
                translateY(-22px);

            box-shadow:
                0 14px 20px
                rgba(
                    62,
                    22,
                    38,
                    0.25
                );
        }


        .spine:nth-child(1) {
            height: 88%;
            background: var(--rose-600);
        }


        .spine:nth-child(2) {
            height: 100%;
            background: var(--rose-900);
        }


        .spine:nth-child(3) {
            height: 70%;
            background: var(--gold);
            color: var(--rose-900);
        }


        .spine:nth-child(4) {
            height: 94%;
            background: var(--rose-300);
            color: var(--rose-900);
        }


        .spine:nth-child(5) {
            height: 80%;
            background: var(--rose-700);
        }


        .spine:nth-child(6) {
            height: 60%;
            background: var(--rose-900);
        }


        .spine:nth-child(7) {
            height: 100%;
            background: var(--rose-600);
        }


        .shelf-caption {

            text-align:
                center;

            font-size:
                12.5px;

            color:
                var(--ink);

            margin-top:
                14px;

            font-style:
                italic;
        }


        /* =========================
           STATISTIK
        ========================= */

        .stats-bar {

            max-width:
                1180px;

            margin:
                20px auto 0;

            padding:
                30px
                40px;

            display: flex;

            justify-content:
                space-around;

            flex-wrap:
                wrap;

            gap:
                20px;

            background:
                rgba(
                    255,
                    253,
                    251,
                    0.88
                );

            backdrop-filter:
                blur(8px);

            border-radius:
                16px;

            border:
                1px solid
                rgba(
                    214,
                    51,
                    108,
                    0.15
                );

            box-shadow:
                0 4px 15px
                rgba(
                    62,
                    22,
                    38,
                    0.05
                );
        }


        .stat {

            text-align:
                center;
        }


        .stat h3 {

            font-family:
                'Fraunces',
                serif;

            font-size:
                32px;

            color:
                var(--rose-700);
        }


        .stat p {

            font-size:
                12px;

            color:
                var(--ink-soft);

            text-transform:
                uppercase;

            letter-spacing:
                1px;

            margin-top:
                4px;
        }


        /* =========================
           KOLEKSI BUKU
        ========================= */

        .koleksi-section {

            max-width:
                1180px;

            margin:
                80px auto 0;

            padding:
                0 40px;
        }


        .section-head {

            text-align:
                center;

            margin-bottom:
                40px;
        }


        .eyebrow {

            font-size:
                12px;

            letter-spacing:
                2px;

            text-transform:
                uppercase;

            color:
                var(--gold);

            font-weight:
                700;
        }


        .section-head h2 {

            font-family:
                'Fraunces',
                serif;

            font-size:
                34px;

            color:
                var(--rose-900);

            margin-top:
                8px;
        }


        /* FILTER */

        .filter-kategori {

            display: flex;

            flex-wrap:
                wrap;

            gap:
                10px;

            justify-content:
                center;

            margin-bottom:
                34px;
        }


        .filter-kategori button {

            font-family:
                'Inter',
                sans-serif;

            border:
                1.5px solid
                var(--rose-300);

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.7
                );

            color:
                var(--rose-700);

            padding:
                8px
                20px;

            border-radius:
                999px;

            font-size:
                13px;

            font-weight:
                600;

            cursor:
                pointer;

            transition:
                0.2s;
        }


        .filter-kategori button:hover {

            transform:
                translateY(-1px);
        }


        .filter-kategori button.aktif {

            background:
                var(--rose-600);

            border-color:
                var(--rose-600);

            color:
                #ffffff;
        }


        /* GRID BUKU */

        .buku-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(200px, 1fr)
                );

            gap:
                22px;
        }


        .buku-card {

            background:
                var(--cream);

            border-radius:
                16px;

            overflow:
                hidden;

            border:
                1px solid
                rgba(
                    214,
                    51,
                    108,
                    0.12
                );

            box-shadow:
                0 4px 14px
                rgba(
                    62,
                    22,
                    38,
                    0.05
                );

            transition:
                0.3s;

            display:
                flex;

            flex-direction:
                column;
        }


        .buku-card:hover {

            transform:
                translateY(-6px);

            box-shadow:
                0 16px 28px
                rgba(
                    62,
                    22,
                    38,
                    0.14
                );
        }


        .buku-card.tersembunyi {

            display:
                none;
        }


        .buku-cover {

            width:
                100%;

            aspect-ratio:
                3 / 4;

            background:
                var(--rose-100);

            overflow:
                hidden;
        }


        .buku-cover img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

            display:
                block;
        }


        .buku-body {

            padding:
                16px
                16px
                18px;

            display:
                flex;

            flex-direction:
                column;

            gap:
                7px;

            flex:
                1;
        }


        .buku-judul {

            font-family:
                'Fraunces',
                serif;

            font-weight:
                600;

            font-size:
                16px;

            color:
                var(--rose-900);

            line-height:
                1.3;
        }


        .buku-penulis {

            font-size:
                12.5px;

            color:
                var(--ink-soft);
        }


        .buku-footer {

            margin-top:
                auto;

            padding-top:
                10px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            border-top:
                1px dashed
                rgba(
                    214,
                    51,
                    108,
                    0.18
                );
        }


        .badge-stok {

            font-size:
                11px;

            font-weight:
                700;

            padding:
                4px
                10px;

            border-radius:
                999px;

            background:
                var(--hijau-bg);

            color:
                var(--hijau);
        }


        .badge-stok.habis {

            background:
                var(--merah-bg);

            color:
                var(--merah);
        }


        .link-detail {

            font-size:
                12px;

            font-weight:
                700;

            color:
                var(--rose-700);
        }


        /* =========================
           CARA KERJA
        ========================= */

        .how-it-works {

            max-width:
                1180px;

            margin:
                70px auto 0;

            padding:
                0 40px;
        }


        .steps-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(220px, 1fr)
                );

            gap:
                24px;
        }


        .step-card {

            background:
                rgba(
                    255,
                    253,
                    251,
                    0.92
                );

            padding:
                30px
                24px;

            border-radius:
                16px;

            border:
                1px solid
                rgba(
                    214,
                    51,
                    108,
                    0.12
                );

            box-shadow:
                0 4px 14px
                rgba(
                    62,
                    22,
                    38,
                    0.04
                );
        }


        .step-num {

            width:
                40px;

            height:
                40px;

            border-radius:
                50%;

            background:
                var(--rose-100);

            color:
                var(--rose-700);

            font-family:
                'Fraunces',
                serif;

            font-weight:
                700;

            font-size:
                18px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            margin-bottom:
                18px;
        }


        .step-card h4 {

            font-size:
                17px;

            color:
                var(--rose-900);

            margin-bottom:
                8px;

            font-family:
                'Fraunces',
                serif;
        }


        .step-card p {

            font-size:
                13.5px;

            color:
                var(--ink-soft);

            line-height:
                1.5;
        }


        /* =========================
           GRAFIK
        ========================= */

        .chart-section {

            max-width:
                1180px;

            margin:
                80px auto 0;

            padding:
                0 40px;
        }


        .chart-box {

            background:
                rgba(
                    255,
                    253,
                    251,
                    0.92
                );

            padding:
                35px;

            border-radius:
                20px;

            border:
                1px solid
                rgba(
                    214,
                    51,
                    108,
                    0.15
                );

            box-shadow:
                0 8px 30px
                rgba(
                    62,
                    22,
                    38,
                    0.06
                );
        }


        /* =========================
           MINTA BANTUAN
        ========================= */

        .bantuan-section {

            max-width:
                1180px;

            margin:
                80px auto 0;

            padding:
                0 40px;
        }


        .bantuan-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(280px, 1fr)
                );

            gap:
                24px;
        }


        .bantuan-card {

            background:
                rgba(
                    255,
                    253,
                    251,
                    0.94
                );

            border-radius:
                18px;

            padding:
                28px;

            border:
                1px solid
                rgba(
                    214,
                    51,
                    108,
                    0.15
                );

            box-shadow:
                0 8px 25px
                rgba(
                    62,
                    22,
                    38,
                    0.06
                );

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }


        .bantuan-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 16px 30px
                rgba(
                    62,
                    22,
                    38,
                    0.12
                );
        }


        .bantuan-icon {

            width:
                58px;

            height:
                58px;

            border-radius:
                16px;

            background:
                var(--rose-100);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                28px;

            margin-bottom:
                18px;
        }


        .bantuan-card h3 {

            font-family:
                'Fraunces',
                serif;

            font-size:
                22px;

            color:
                var(--rose-900);

            margin-bottom:
                10px;
        }


        .bantuan-card > p {

            font-size:
                13.5px;

            color:
                var(--ink-soft);

            line-height:
                1.6;

            margin-bottom:
                18px;
        }


        .bantuan-list {

            list-style:
                none;

            display:
                flex;

            flex-direction:
                column;

            gap:
                10px;
        }


        .bantuan-list li {

            background:
                var(--rose-50);

            border-left:
                4px solid
                var(--rose-600);

            border-radius:
                8px;

            padding:
                10px
                12px;

            font-size:
                13px;

            color:
                var(--ink);

            line-height:
                1.5;
        }


        .bantuan-list strong {

            color:
                var(--rose-700);
        }


        /* =========================
           FOOTER
        ========================= */

        footer {

            margin-top:
                80px;

            background:
                var(--rose-900);

            color:
                #ffffff;

            padding:
                60px
                40px
                30px;

            text-align:
                center;
        }


        .info-title {

            font-size:
                20px;

            font-weight:
                700;

            letter-spacing:
                1.5px;

            margin-bottom:
                24px;
        }


        .info-list {

            display:
                flex;

            flex-direction:
                column;

            gap:
                16px;
        }


        .info-item {

            color:
                rgba(
                    255,
                    255,
                    255,
                    0.85
                );

            font-size:
                15px;
        }


        .footer-bottom {

            margin-top:
                40px;

            padding-top:
                24px;

            border-top:
                1px solid
                rgba(
                    255,
                    255,
                    255,
                    0.15
                );

            color:
                rgba(
                    255,
                    255,
                    255,
                    0.65
                );

            font-size:
                13px;
        }


        .brand-mini {

            font-family:
                'Fraunces',
                serif;

            color:
                var(--rose-300);

            margin-bottom:
                8px;

            font-size:
                16px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 880px) {

            .top-header {
                display: none;
            }


            nav {

                padding:
                    12px
                    20px;
            }


            .nav-menu {
                display: none;
            }


            .nav-links {
                display: none;
            }


            .logo-wrapper {

                width:
                    52px;

                height:
                    52px;
            }


            .brand span {

                font-size:
                    14px;
            }


            .hero {

                grid-template-columns:
                    1fr;

                padding:
                    45px
                    24px
                    20px;

                gap:
                    40px;
            }


            .hero h1 {

                font-size:
                    38px;
            }


            .stats-bar,
            .how-it-works,
            .chart-section,
            .koleksi-section,
            .bantuan-section {

                padding:
                    0 24px;
            }


            .stats-bar {

                margin-left:
                    20px;

                margin-right:
                    20px;
            }


            .shelf-wrap {
                display: none;
            }


            footer {

                padding:
                    40px
                    24px
                    24px;
            }

        }

    </style>

</head>


<body id="beranda">


<!-- =========================
     HEADER ATAS
========================= -->

<div class="top-header">

    <span>
        📍 SMK Negeri 7 Yogyakarta — Jl. Gowongan Kidul No.48
    </span>

    <span>
        🕒 Buka: Senin - Jumat (07.00 - 15.30 WIB)
    </span>

</div>



<!-- =========================
     NAVBAR
========================= -->

<nav>

    <div class="brand">

        <div class="logo-wrapper">

            <img
                src="gambar_buku/logosmk7.jpeg"
                alt="Logo SMK N 7 Yogyakarta"
                onerror="this.src='https://placehold.co/68x68/FCE4EC/D6336C?text=SMK7'"
            >

        </div>


        <span>
            Perpustakaan Digital SMK N 7 YOGYAKARTA
        </span>

    </div>


    <ul class="nav-menu">

        <li>
            <a href="#beranda">
                Beranda
            </a>
        </li>

        <li>
            <a href="#katalog">
                Koleksi Buku
            </a>
        </li>

        <li>
            <a href="#alur">
                Cara Kerja
            </a>
        </li>

        <li>
            <a href="#statistik">
                Statistik
            </a>
        </li>

        <li>
            <a href="#bantuan">
                Minta Bantuan
            </a>
        </li>

    </ul>


    <div class="nav-links">

        <a
            href="login.php"
            class="btn btn-ghost"
        >
            Masuk
        </a>


        <a
            href="pengguna_tambah.php"
            class="btn btn-solid"
        >
            Daftar Anggota
        </a>

    </div>

</nav>



<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div>

        <span class="stamp">
            ✦ Kartu Anggota Digital
        </span>


        <h1>

            Rak buku sekolahmu,

            <br>

            kini

            <em>
                selalu terbuka
            </em>.

        </h1>


        <p class="lede">

            Cari, pinjam, dan kembalikan buku
            tanpa antre di meja pustakawan.
            Semua koleksi dan riwayat bacaanmu
            tersimpan rapi dan mudah diakses.

        </p>


        <div class="btn-group">

            <a
                href="login.php"
                class="btn btn-solid"
            >
                Masuk Akun
            </a>


            <a
                href="pengguna_tambah.php"
                class="btn btn-ghost"
            >
                Daftar Anggota
            </a>

        </div>

    </div>


    <div class="shelf-wrap">

        <div class="shelf">

            <div class="spine">
                Fiksi
            </div>

            <div class="spine">
                Sains
            </div>

            <div class="spine">
                Sejarah
            </div>

            <div class="spine">
                Puisi
            </div>

            <div class="spine">
                Biografi
            </div>

            <div class="spine">
                Atlas
            </div>

            <div class="spine">
                Komik
            </div>

        </div>


        <p class="shelf-caption">

            Arahkan kursor ke rak —
            koleksi kami menunggu untuk dijelajahi.

        </p>

    </div>

</section>



<!-- =========================
     STATISTIK
========================= -->

<div class="stats-bar">

    <div class="stat">

        <h3>

            <?php
            echo number_format(
                $total_judul,
                0,
                ',',
                '.'
            );
            ?>

        </h3>

        <p>
            Judul Buku
        </p>

    </div>


    <div class="stat">

        <h3>

            <?php
            echo number_format(
                $total_anggota,
                0,
                ',',
                '.'
            );
            ?>

        </h3>

        <p>
            Anggota Aktif
        </p>

    </div>


    <div class="stat">

        <h3>

            <?php
            echo number_format(
                $total_dipinjam,
                0,
                ',',
                '.'
            );
            ?>

        </h3>

        <p>
            Sedang Dipinjam
        </p>

    </div>

</div>



<!-- =========================
     KOLEKSI BUKU
========================= -->

<section
    class="koleksi-section"
    id="katalog"
>

    <div class="section-head">

        <span class="eyebrow">
            Kartu Katalog
        </span>

        <h2>
            Koleksi Buku
        </h2>

    </div>


    <?php if (count($koleksi_buku) > 0) { ?>


        <div
            class="filter-kategori"
            id="filterKategori"
        >

            <button
                type="button"
                class="aktif"
                data-filter="semua"
            >
                Semua
            </button>


            <?php foreach ($daftar_kategori as $kat) { ?>

                <button
                    type="button"
                    data-filter="<?php echo htmlspecialchars($kat); ?>"
                >
                    <?php echo htmlspecialchars($kat); ?>
                </button>

            <?php } ?>

        </div>


        <div
            class="buku-grid"
            id="gridBuku"
        >


            <?php foreach ($koleksi_buku as $b) { ?>


                <a
                    class="buku-card"
                    data-kategori="<?php echo htmlspecialchars($b['kategori']); ?>"
                    href="detail_buku.php?id=<?php echo (int)$b['id']; ?>"
                >


                    <div class="buku-cover">

                        <?php

                        if (!empty($b['gambar'])) {

                            $src_gambar =
                                "gambar_buku/" .
                                $b['gambar'];

                        } else {

                            $src_gambar =
                                "https://placehold.co/300x400?text=Tanpa+Sampul";
                        }

                        ?>


                        <img
                            src="<?php echo htmlspecialchars($src_gambar); ?>"
                            alt="Sampul <?php echo htmlspecialchars($b['judul']); ?>"
                            onerror="this.src='https://placehold.co/300x400?text=Tanpa+Sampul';"
                        >

                    </div>


                    <div class="buku-body">


                        <div class="buku-judul">

                            <?php
                            echo htmlspecialchars(
                                $b['judul']
                            );
                            ?>

                        </div>


                        <div class="buku-penulis">

                            <?php
                            echo htmlspecialchars(
                                $b['penulis']
                            );
                            ?>

                        </div>


                        <div class="buku-footer">


                            <?php if ($b['jumlah'] > 0) { ?>

                                <span class="badge-stok">

                                    <?php
                                    echo (int)$b['jumlah'];
                                    ?>

                                    Tersedia

                                </span>

                            <?php } else { ?>

                                <span class="badge-stok habis">
                                    Stok Habis
                                </span>

                            <?php } ?>


                            <span class="link-detail">
                                Lihat Detail →
                            </span>


                        </div>

                    </div>


                </a>


            <?php } ?>


        </div>


    <?php } else { ?>


        <div
            style="
                text-align: center;
                padding: 40px;
                background: white;
                border-radius: 16px;
            "
        >

            Belum ada buku di koleksi.

        </div>


    <?php } ?>


</section>



<!-- =========================
     CARA KERJA
========================= -->

<section
    class="how-it-works"
    id="alur"
>

    <div class="section-head">

        <span class="eyebrow">
            Alur Layanan
        </span>

        <h2>
            Cara Kerja Perpustakaan Digital
        </h2>

    </div>


    <div class="steps-grid">


        <div class="step-card">

            <div class="step-num">
                1
            </div>

            <h4>
                Daftar & Login
            </h4>

            <p>

                Buat akun anggota siswa baru
                atau login dengan akun
                yang sudah terdaftar.

            </p>

        </div>


        <div class="step-card">

            <div class="step-num">
                2
            </div>

            <h4>
                Cari Buku
            </h4>

            <p>

                Telusuri katalog buku
                berdasarkan judul,
                penulis, atau kategori.

            </p>

        </div>


        <div class="step-card">

            <div class="step-num">
                3
            </div>

            <h4>
                Ajukan Peminjaman
            </h4>

            <p>

                Pilih buku yang diinginkan
                dan ajukan peminjaman
                melalui aplikasi.

            </p>

        </div>


        <div class="step-card">

            <div class="step-num">
                4
            </div>

            <h4>
                Bawa & Kembalikan
            </h4>

            <p>

                Ambil buku di perpustakaan
                dan kembalikan sesuai
                tanggal yang ditentukan.

            </p>

        </div>


    </div>

</section>



<!-- =========================
     GRAFIK
========================= -->

<section
    class="chart-section"
    id="statistik"
>

    <div class="chart-box">


        <div
            class="section-head"
            style="
                margin-bottom: 24px;
                text-align: left;
            "
        >

            <span class="eyebrow">
                Visualisasi Data
            </span>

            <h2
                style="
                    font-size: 26px;
                "
            >
                Statistik Peminjaman per Kategori
            </h2>

        </div>


        <div
            style="
                height: 300px;
                position: relative;
            "
        >

            <canvas id="kategoriChart"></canvas>

        </div>


    </div>

</section>



<!-- =========================
     MINTA BANTUAN
========================= -->

<section
    class="bantuan-section"
    id="bantuan"
>

    <div class="section-head">

        <span class="eyebrow">
            Pusat Bantuan
        </span>

        <h2>
            Cara Menggunakan Aplikasi
        </h2>

    </div>


    <div class="bantuan-grid">


        <!-- ADMIN -->

        <div class="bantuan-card">

            <div class="bantuan-icon">
                👨‍💼
            </div>

            <h3>
                Panduan Admin
            </h3>

            <p>
                Admin memiliki akses penuh
                untuk mengelola seluruh sistem
                perpustakaan.
            </p>


            <ul class="bantuan-list">

                <li>

                    <strong>
                        1. Login
                    </strong>

                    <br>

                    Masuk menggunakan
                    akun admin.

                </li>


                <li>

                    <strong>
                        2. Kelola Pengguna
                    </strong>

                    <br>

                    Tambah, edit, hapus,
                    atau melihat data
                    siswa dan petugas.

                </li>


                <li>

                    <strong>
                        3. Kelola Buku
                    </strong>

                    <br>

                    Menambahkan buku baru
                    dan memperbarui
                    informasi buku.

                </li>


                <li>

                    <strong>
                        4. Kelola Peminjaman
                    </strong>

                    <br>

                    Memantau seluruh buku
                    yang sedang dipinjam.

                </li>


                <li>

                    <strong>
                        5. Cek Kondisi Buku
                    </strong>

                    <br>

                    Melihat laporan buku
                    yang rusak, sobek,
                    hilang, atau kondisi lainnya.

                </li>


                <li>

                    <strong>
                        6. Laporan
                    </strong>

                    <br>

                    Melihat data dan statistik
                    perpustakaan.

                </li>

            </ul>

        </div>



        <!-- PETUGAS -->

        <div class="bantuan-card">

            <div class="bantuan-icon">
                👩‍💻
            </div>

            <h3>
                Panduan Petugas
            </h3>

            <p>
                Petugas membantu mengelola
                kegiatan peminjaman dan
                pengembalian buku.
            </p>


            <ul class="bantuan-list">

                <li>

                    <strong>
                        1. Login
                    </strong>

                    <br>

                    Masuk menggunakan
                    akun petugas.

                </li>


                <li>

                    <strong>
                        2. Melihat Data Buku
                    </strong>

                    <br>

                    Memeriksa daftar
                    dan stok buku
                    yang tersedia.

                </li>


                <li>

                    <strong>
                        3. Memproses Peminjaman
                    </strong>

                    <br>

                    Memeriksa data siswa
                    yang melakukan peminjaman.

                </li>


                <li>

                    <strong>
                        4. Memproses Pengembalian
                    </strong>

                    <br>

                    Mengubah status buku
                    setelah buku dikembalikan.

                </li>


                <li>

                    <strong>
                        5. Cek Kondisi Buku
                    </strong>

                    <br>

                    Mencatat kondisi buku
                    seperti baik, rusak,
                    sobek, hilang,
                    atau lainnya.

                </li>


                <li>

                    <strong>
                        6. Melihat Riwayat
                    </strong>

                    <br>

                    Memeriksa riwayat transaksi
                    peminjaman dan pengembalian.

                </li>

            </ul>

        </div>



        <!-- SISWA -->

        <div class="bantuan-card">

            <div class="bantuan-icon">
                🎓
            </div>

            <h3>
                Panduan Siswa
            </h3>

            <p>
                Siswa dapat mencari,
                meminjam, mengembalikan,
                dan memberikan ulasan buku.
            </p>


            <ul class="bantuan-list">

                <li>

                    <strong>
                        1. Daftar Akun
                    </strong>

                    <br>

                    Daftarkan diri sebagai
                    anggota perpustakaan.

                </li>


                <li>

                    <strong>
                        2. Login
                    </strong>

                    <br>

                    Masuk menggunakan
                    akun siswa.

                </li>


                <li>

                    <strong>
                        3. Cari Buku
                    </strong>

                    <br>

                    Gunakan katalog
                    untuk mencari buku
                    berdasarkan judul
                    atau kategori.

                </li>


                <li>

                    <strong>
                        4. Ajukan Peminjaman
                    </strong>

                    <br>

                    Pilih buku yang tersedia
                    dan lakukan peminjaman.

                </li>


                <li>

                    <strong>
                        5. Lihat Riwayat
                    </strong>

                    <br>

                    Memeriksa buku yang
                    sedang dipinjam dan
                    sudah dikembalikan.

                </li>


                <li>

                    <strong>
                        6. Berikan Ulasan
                    </strong>

                    <br>

                    Memberikan rating
                    dan ulasan setelah
                    membaca buku.

                </li>

            </ul>

        </div>


    </div>

</section>



<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3 class="info-title">
        INFORMASI
    </h3>


    <div class="info-list">

        <div class="info-item">
            📍 Jl. Gowongan Kidul No.48
        </div>

        <div class="info-item">
            ✉ sekolah@smkn7yogyakarta.sch.id
        </div>

        <div class="info-item">
            ☎ 0898-9187-869
        </div>

    </div>


    <div class="footer-bottom">

        <div class="brand-mini">
            Perpustakaan Digital SMK N 7 Yogyakarta
        </div>

        &copy;

        <?php echo date("Y"); ?>

        — Dibuat dengan ♥ untuk generasi pembaca.

    </div>

</footer>



<!-- =========================
     JAVASCRIPT FILTER
========================= -->

<script>

const tombolFilter =
    document.querySelectorAll(
        '#filterKategori button'
    );


const semuaKartu =
    document.querySelectorAll(
        '#gridBuku .buku-card'
    );


tombolFilter.forEach(
    tombol => {

        tombol.addEventListener(
            'click',

            () => {


                tombolFilter.forEach(
                    t => {

                        t.classList.remove(
                            'aktif'
                        );

                    }
                );


                tombol.classList.add(
                    'aktif'
                );


                const pilihan =
                    tombol.dataset.filter;


                semuaKartu.forEach(
                    kartu => {


                        if (

                            pilihan === 'semua' ||

                            kartu.dataset.kategori === pilihan

                        ) {

                            kartu.classList.remove(
                                'tersembunyi'
                            );

                        } else {

                            kartu.classList.add(
                                'tersembunyi'
                            );

                        }


                    }
                );


            }
        );

    }
);

</script>



<!-- =========================
     JAVASCRIPT GRAFIK
========================= -->

<script>

const canvasChart =
    document.getElementById(
        'kategoriChart'
    );


if (canvasChart) {


    const ctx =
        canvasChart.getContext(
            '2d'
        );


    const labels =
        <?php
        echo json_encode(
            $labels_kategori
        );
        ?>;


    const dataValues =
        <?php
        echo json_encode(
            $data_kategori
        );
        ?>;


    new Chart(

        ctx,

        {

            type:
                'bar',

            data: {

                labels:
                    labels,

                datasets: [

                    {

                        label:
                            'Jumlah Buku Dipinjam',

                        data:
                            dataValues,

                        backgroundColor:
                            'rgba(214, 51, 108, 0.75)',

                        borderColor:
                            '#B02358',

                        borderWidth:
                            1.5,

                        borderRadius:
                            8

                    }

                ]

            },


            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,


                plugins: {

                    legend: {

                        display:
                            false

                    }

                },


                scales: {

                    y: {

                        beginAtZero:
                            true,

                        ticks: {

                            stepSize:
                                1

                        }

                    }

                }

            }

        }

    );

}

</script>


</body>
</html>