<?php

class SimpleCache
{
    private string $cacheDir;

    public function __construct($cacheDir = './cache/')
    {
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true); // Buat direktori cache jika belum ada
        }
    }

    public function get($key)
    {
        $cacheFile = $this->cacheDir . md5($key) . '.cache';
        
        if (file_exists($cacheFile)) {
            $cacheData = unserialize(file_get_contents($cacheFile));
            if ($cacheData['expiry'] >= time()) {
                return $cacheData['data'];
            } else {
                unlink($cacheFile); // Hapus file cache jika sudah kadaluarsa
            }
        }
        
        return false; // Mengembalikan false jika data tidak ada atau sudah kadaluarsa
    }

    public function set($key, $data, $expiry = 3600)
    {
        $cacheFile = $this->cacheDir . md5($key) . '.cache';
        $cacheData = [
            'data' => $data,
            'expiry' => time() + $expiry, // Waktu kadaluarsa dalam detik dari sekarang
        ];

        file_put_contents($cacheFile, serialize($cacheData), LOCK_EX); // Menyimpan data ke file cache
    }

    public function delete($key)
    {
        $cacheFile = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile); // Hapus file cache jika ada
        }
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file); // Hapus semua file cache dalam direktori cache
        }
    }
}
?>
