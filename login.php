<?php

// session_start() sudah ditangani di config.php
include 'config.php';

$pesan_error = "";
$warna_user  = "";
$warna_pass  = "";
$warna_level = "";


if (isset($_POST['login'])) {

    // Ambil data dari form
    $user = trim($_POST['username'] ?? '');

    // Password asli yang diketik user
    $password_asli = trim($_POST['password'] ?? '');

    // Level login
    $level = $_POST['level'] ?? '';


    // =====================================================
    // VALIDASI INPUT
    // =====================================================

    if (empty($level)) {

        $pesan_error = "Pilih dulu jenis login!";
        $warna_level = "err";

    } elseif (empty($user)) {

        $pesan_error = "Username tidak boleh kosong!";
        $warna_user = "err";

    } elseif (empty($password_asli)) {

        $pesan_error = "Password tidak boleh kosong!";
        $warna_pass = "err";

    } else {

        // =================================================
        // CARI USER BERDASARKAN USERNAME
        // =================================================

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, nama, username, password, level
             FROM pengguna
             WHERE username = ?"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $user
            );


            mysqli_stmt_execute($stmt);


            $result = mysqli_stmt_get_result($stmt);


            $data = mysqli_fetch_assoc($result);


            // =============================================
            // USERNAME TIDAK DITEMUKAN
            // =============================================

            if (!$data) {

                $pesan_error = "Username tidak terdaftar!";
                $warna_user = "err";

            }


            // =============================================
            // CEK PASSWORD
            //
            // Bisa untuk:
            // 1. Password lama biasa
            // 2. Password baru hasil MD5
            // =============================================

            elseif (
                $data['password'] !== $password_asli &&
                $data['password'] !== md5($password_asli)
            ) {

                $pesan_error = "Password salah!";
                $warna_pass = "err";

            }


            // =============================================
            // CEK LEVEL
            // =============================================

            elseif ($data['level'] !== $level) {

                if ($level == "admin") {

                    $pesan_error =
                        "Kamu bukan admin! Silakan pilih jenis yang benar.";

                }

                elseif ($level == "petugas") {

                    $pesan_error =
                        "Kamu bukan petugas! Silakan pilih jenis yang benar.";

                }

                else {

                    $pesan_error =
                        "Akun ini bukan untuk siswa!";

                }


                $warna_level = "err";

            }


            // =============================================
            // LOGIN BERHASIL
            // =============================================

            else {

                // Mencegah session lama digunakan kembali
                session_regenerate_id(true);


                // Simpan data user ke session
                $_SESSION['id'] = $data['id'];

                $_SESSION['nama'] = $data['nama'];

                $_SESSION['username'] = $data['username'];

                $_SESSION['level'] = $data['level'];

                $_SESSION['sudah_login'] = "YA";


                // Tentukan halaman tujuan
                if (
                    $data['level'] == "admin" ||
                    $data['level'] == "petugas"
                ) {

                    $tujuan = "beranda.php";

                } else {

                    $tujuan = "beranda_siswa.php";

                }


                // =========================================
                // HALAMAN BERHASIL LOGIN
                // =========================================

                ?>

                <!DOCTYPE html>
                <html lang="id">

                <head>

                    <meta charset="UTF-8">

                    <meta
                        name="viewport"
                        content="width=device-width, initial-scale=1.0"
                    >

                    <title>Berhasil Masuk...</title>


                    <style>

                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }


                        body {

                            height: 100vh;

                            display: flex;

                            align-items: center;

                            justify-content: center;

                            background: #3E1626;

                            font-family: 'Inter', sans-serif;

                            color: #fff;

                        }


                        .cek {

                            width: 64px;

                            height: 64px;

                            border-radius: 50%;

                            background: linear-gradient(
                                135deg,
                                #D6336C,
                                #B02358
                            );

                            display: flex;

                            align-items: center;

                            justify-content: center;

                            font-size: 28px;

                            margin: 0 auto 14px;

                            animation:
                                pop .5s cubic-bezier(
                                    .34,
                                    1.56,
                                    .64,
                                    1
                                );

                        }


                        p {

                            font-size: 14px;

                            color:
                                rgba(
                                    255,
                                    255,
                                    255,
                                    .75
                                );

                            text-align: center;

                        }


                        @keyframes pop {

                            from {

                                transform: scale(0);

                                opacity: 0;

                            }


                            to {

                                transform: scale(1);

                                opacity: 1;

                            }

                        }

                    </style>

                </head>


                <body>


                    <div style="text-align:center;">


                        <div class="cek">

                            ✓

                        </div>


                        <p>

                            Berhasil masuk, mengalihkan...

                        </p>


                    </div>


                    <script>


                        // Efek suara lonceng kecil
                        function mainkanLoncengLirih() {

                            try {

                                const AudioCtx =
                                    window.AudioContext ||
                                    window.webkitAudioContext;


                                if (!AudioCtx) {

                                    return;

                                }


                                const ctx = new AudioCtx();


                                const sekarang =
                                    ctx.currentTime;


                                const frekuensi =
                                    [2093, 3135];


                                frekuensi.forEach(
                                    (freq, index) => {


                                        const osc =
                                            ctx.createOscillator();


                                        const gain =
                                            ctx.createGain();


                                        osc.type = "sine";


                                        osc.frequency.setValueAtTime(
                                            freq,
                                            sekarang
                                        );


                                        const maxVol =
                                            index === 0
                                            ? 0.05
                                            : 0.025;


                                        gain.gain.setValueAtTime(
                                            0.0001,
                                            sekarang
                                        );


                                        gain.gain.exponentialRampToValueAtTime(
                                            maxVol,
                                            sekarang + 0.015
                                        );


                                        gain.gain.exponentialRampToValueAtTime(
                                            0.0001,
                                            sekarang + 0.8
                                        );


                                        osc.connect(gain);


                                        gain.connect(
                                            ctx.destination
                                        );


                                        const waktuMulai =
                                            sekarang +
                                            (index * 0.06);


                                        osc.start(
                                            waktuMulai
                                        );


                                        osc.stop(
                                            waktuMulai + 0.8
                                        );

                                    }
                                );

                            }

                            catch (e) {

                            }

                        }


                        mainkanLoncengLirih();


                        setTimeout(
                            function () {

                                window.location.href =
                                    "<?= $tujuan ?>";

                            },
                            750
                        );


                    </script>


                </body>

                </html>

                <?php

                exit;

            }


            // Tutup prepared statement
            mysqli_stmt_close($stmt);

        }

        else {

            $pesan_error =
                "Terjadi kesalahan saat memproses login!";

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


    <title>
        Login Perpustakaan SMK N 7 Yogyakarta
    </title>


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
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <style>


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }


        :root {
            --rose-600: #D6336C;
            --rose-700: #B02358;
            --rose-300: #F3A9C3;
            --gold: #D9A441;
            --ink: #3E1626;
        }


        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }


        @media (prefers-reduced-motion: reduce) {

            * {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }

        }


        body {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .video-latar {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }


        .lapisan {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;

            background:
                linear-gradient(
                    180deg,
                    rgba(62, 22, 38, .35) 0%,
                    rgba(62, 22, 38, .1) 45%,
                    rgba(62, 22, 38, .4) 100%
                );

            z-index: -1;
        }


        /* TOMBOL KEMBALI */

        .btn-kembali {

            position: fixed;

            top: 22px;

            left: 24px;

            z-index: 10;

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 10px 18px;

            border-radius: 999px;

            background:
                rgba(255,255,255,0.14);

            backdrop-filter:
                blur(6px);

            -webkit-backdrop-filter:
                blur(6px);

            border:
                1px solid
                rgba(255,255,255,0.3);

            color: #fff;

            font-size: 13px;

            font-weight: 600;

            text-decoration: none;

            transition:
                background .2s ease,
                transform .2s ease;

        }


        .btn-kembali:hover {

            background:
                rgba(255,255,255,0.24);

            transform:
                translateX(-2px);

        }


        .btn-kembali:focus-visible {

            outline:
                2px solid #fff;

            outline-offset: 3px;

        }


        .btn-kembali svg {

            width: 15px;

            height: 15px;

            flex-shrink: 0;

        }


        /* CARD LOGIN */

        .card-login {

            width: 92%;

            max-width: 420px;

            background:
                rgba(255,255,255,0.06);

            backdrop-filter:
                blur(6px);

            -webkit-backdrop-filter:
                blur(6px);

            border:
                1px solid
                rgba(255,255,255,0.22);

            border-radius: 22px;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,.25);

            padding:
                38px 34px 30px;

            color: #fff;

            opacity: 0;

            animation:
                rise .6s ease forwards;

        }


        @keyframes rise {

            from {

                opacity: 0;

                transform:
                    translateY(18px);

            }


            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        .stamp {

            width: 52px;

            height: 52px;

            margin:
                0 auto 16px;

            border:
                1.5px dashed
                var(--gold);

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

        }


        .card-login h3 {

            font-family:
                'Fraunces',
                serif;

            font-weight: 600;

            font-size: 24px;

            text-align: center;

            letter-spacing: .2px;

            text-shadow:
                0 2px 8px
                rgba(0,0,0,.3);

        }


        .card-login p.sub {

            text-align: center;

            font-size: 12.5px;

            letter-spacing: 1.5px;

            text-transform: uppercase;

            color:
                rgba(255,255,255,.75);

            margin-top: 6px;

            margin-bottom: 26px;

        }


        /* PESAN ERROR */

        .pesan-error {

            background:
                rgba(255,235,238,0.95);

            color: #8a1c3a;

            padding:
                10px 14px;

            border-radius: 10px;

            text-align: center;

            margin-bottom: 16px;

            font-size: 13px;

            font-weight: 600;

            border-left:
                4px solid
                var(--rose-600);

        }


        /* LEVEL LOGIN */

        .field-label {

            font-size: 12px;

            font-weight: 600;

            letter-spacing: .5px;

            text-transform: uppercase;

            color:
                rgba(255,255,255,.85);

            margin-bottom: 8px;

            display: block;

        }


        .level-group {

            display: grid;

            grid-template-columns:
                repeat(3,1fr);

            gap: 8px;

            margin-bottom: 18px;

            border-radius: 12px;

            padding: 5px;

            background:
                rgba(0,0,0,0.10);

        }


        .level-group.err {

            box-shadow:
                0 0 0 2px
                var(--rose-300);

        }


        .level-opt {
            position: relative;
        }


        .level-opt input {

            position: absolute;

            opacity: 0;

            inset: 0;

            cursor: pointer;

        }


        .level-opt label {

            display: flex;

            flex-direction: column;

            align-items: center;

            gap: 3px;

            padding: 9px 4px;

            border-radius: 9px;

            cursor: pointer;

            font-size: 11.5px;

            font-weight: 600;

            color:
                rgba(255,255,255,.8);

            transition:
                background .25s ease,
                color .25s ease,
                transform .2s ease;

        }


        .level-opt label span.ic {
            font-size: 16px;
        }


        .level-opt input:checked + label {

            background:
                var(--rose-600);

            color: #fff;

            box-shadow:
                0 4px 12px
                rgba(214,51,108,.4);

        }


        .level-opt label:hover {
            transform: translateY(-1px);
        }


        .level-opt input:focus-visible + label {

            outline:
                2px solid #fff;

            outline-offset: 2px;

        }


        /* INPUT */

        .field {
            margin-bottom: 16px;
        }


        .field input {

            width: 100%;

            background:
                rgba(255,255,255,0.10);

            border:
                1.5px solid
                rgba(255,255,255,0.28);

            border-radius: 11px;

            padding:
                12px 14px;

            color: #fff;

            font-size: 14.5px;

            transition:
                border-color .25s ease,
                background .25s ease;

        }


        .field input::placeholder {

            color:
                rgba(255,255,255,.65);

        }


        .field input:focus {

            outline: none;

            border-color:
                var(--gold);

            background:
                rgba(255,255,255,0.16);

        }


        .field input.err {

            border-color:
                var(--rose-300);

        }


        /* TOMBOL MASUK */

        .btn-masuk {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 11px;

            background:

                linear-gradient(
                    135deg,
                    var(--rose-600),
                    var(--rose-700)
                );

            color: #fff;

            font-weight: 700;

            font-size: 14.5px;

            letter-spacing: .5px;

            cursor: pointer;

            transition:
                transform .2s ease,
                box-shadow .2s ease;

            box-shadow:
                0 10px 22px
                rgba(214,51,108,.35);

            margin-top: 4px;

        }


        .btn-masuk:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 14px 26px
                rgba(214,51,108,.45);

        }


        .btn-masuk:focus-visible {

            outline:
                2px solid #fff;

            outline-offset: 3px;

        }


        /* LINK DAFTAR */

        .daftar-link {

            text-align: center;

            margin-top: 18px;

            font-size: 12.5px;

            color:
                rgba(255,255,255,.8);

        }


        .daftar-link a {

            color: #fff;

            font-weight: 700;

            text-decoration: underline;

            text-underline-offset: 3px;

        }


        /* RESPONSIVE */

        @media (max-width: 420px) {

            .card-login {

                padding:
                    30px 22px 24px;

            }


            .btn-kembali {

                top: 16px;

                left: 16px;

                padding:
                    8px 14px;

                font-size: 12px;

            }

        }


    </style>


</head>


<body>


<!-- VIDEO LATAR -->

<video
    class="video-latar"
    autoplay
    muted
    loop
    playsinline
    preload="auto"
>

    <source
        src="vidio/vidio1.mp4"
        type="video/mp4"
    >

</video>


<div class="lapisan"></div>


<!-- TOMBOL KEMBALI -->

<a
    href="index.php"
    class="btn-kembali"
>

    <svg
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >

        <path
            d="M19 12H5M5 12L12 19M5 12L12 5"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

    </svg>

    Kembali ke Beranda

</a>


<!-- CARD LOGIN -->

<div class="card-login">


    <div class="stamp">
        🔐
    </div>


    <h3>
        Portal Anggota
    </h3>


    <p class="sub">
        Perpustakaan SMK Negeri 7 Yogyakarta
    </p>


    <!-- PESAN ERROR -->

    <?php if (!empty($pesan_error)) { ?>

        <div class="pesan-error">

            <?= htmlspecialchars($pesan_error) ?>

        </div>

    <?php } ?>


    <!-- FORM LOGIN -->

    <form
        method="POST"
        id="formLogin"
    >


        <span class="field-label">
            Login Sebagai
        </span>


        <!-- PILIH LEVEL -->

        <div
            class="level-group <?= $warna_level ?>"
            id="levelGroup"
        >


            <!-- ADMIN -->

            <div class="level-opt">

                <input
                    type="radio"
                    name="level"
                    id="lvl-admin"
                    value="admin"
                    <?= (($_POST['level'] ?? '') === 'admin') ? 'checked' : '' ?>
                >

                <label for="lvl-admin">

                    <span class="ic">
                        🛡️
                    </span>

                    Admin

                </label>

            </div>


            <!-- PETUGAS -->

            <div class="level-opt">

                <input
                    type="radio"
                    name="level"
                    id="lvl-petugas"
                    value="petugas"
                    <?= (($_POST['level'] ?? '') === 'petugas') ? 'checked' : '' ?>
                >

                <label for="lvl-petugas">

                    <span class="ic">
                        📋
                    </span>

                    Petugas

                </label>

            </div>


            <!-- SISWA -->

            <div class="level-opt">

                <input
                    type="radio"
                    name="level"
                    id="lvl-siswa"
                    value="siswa"
                    <?= (($_POST['level'] ?? '') === 'siswa') ? 'checked' : '' ?>
                >

                <label for="lvl-siswa">

                    <span class="ic">
                        🎓
                    </span>

                    Siswa

                </label>

            </div>


        </div>


        <!-- USERNAME -->

        <div class="field">

            <span class="field-label">
                Username
            </span>


            <input
                type="text"
                name="username"
                class="<?= $warna_user ?>"
                placeholder="Masukkan username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required
            >

        </div>


        <!-- PASSWORD -->

        <div class="field">

            <span class="field-label">
                Password
            </span>


            <input
                type="password"
                name="password"
                class="<?= $warna_pass ?>"
                placeholder="Masukkan password"
                required
            >

        </div>


        <!-- TOMBOL MASUK -->

        <button
            type="submit"
            name="login"
            class="btn-masuk"
        >

            MASUK

        </button>


        <!-- LINK DAFTAR -->

        <div class="daftar-link">

            Belum punya akun?

            <a href="daftar_siswa.php">
                Daftar di sini
            </a>

        </div>


    </form>


</div>


</body>

</html>