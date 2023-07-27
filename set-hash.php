<?php
function generateHash($string) {
    $salt = 'domain.com'; // Ganti dengan salt atau "domain.com" jika Anda tetap ingin menggunakan kombinasi ini
    $combinedString = $string . $salt;
    return password_hash($combinedString, PASSWORD_BCRYPT);
}

function verifyHash($string, $hashedString) {
    $salt = 'domain.com'; // Ganti dengan salt atau "domain.com" jika Anda tetap ingin menggunakan kombinasi ini
    $combinedString = $string . $salt;
    return password_verify($combinedString, $hashedString);
}

// Contoh penggunaan:
$originalString = "12345678"; // Ganti dengan string yang ingin Anda hash

// Hash string
$hashedString = generateHash($originalString);
echo "Hasil Hash: " . $hashedString . PHP_EOL;

// Verifikasi hash
$isVerified = verifyHash($originalString, $hashedString);
if ($isVerified) {
    echo "Verifikasi Berhasil!" . PHP_EOL;
} else {
    echo "Verifikasi Gagal!" . PHP_EOL;
}
