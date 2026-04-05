<?php
// ============================================
// db.php — Veritabanı bağlantısı
// Bu dosyayı sadece Kişi 1 düzenler.
// Diğer herkes: include '../includes/db.php';
// ============================================

$host     = "localhost";
$username = "root";
$password = "";          // XAMPP'te boş bırakın
$database = "gym_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("<div style='color:red;padding:20px'>
        Veritabanı bağlantı hatası: " . $conn->connect_error . "
        <br><small>XAMPP çalışıyor mu? MySQL aktif mi?</small>
    </div>");
}

$conn->set_charset("utf8");
?>
