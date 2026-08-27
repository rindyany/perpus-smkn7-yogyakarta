<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$host = "localhost";
$user = "root";
$pass = "";
$db = "perpustakaan";

$koneksi = mysqli_connect($host,$user,$pass,$db);
if (!$koneksi) die("Koneksi gagal: ".mysqli_connect_error());

// JANGAN UBAH FUNGSI DI BAWAH INI
function cek_login(){
  if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit;
  }
}

function cek_level($level_diizinkan){
  if($_SESSION['level'] != $level_diizinkan){
    die("<script>alert('Akses Ditolak!');location.href='index.php';</script>");
  }
}
?>