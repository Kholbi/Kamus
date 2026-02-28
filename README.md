# ⚡ Kamus Digital Kilat (Pro)

Aplikasi Web Kamus Digital satu file (Single-File PHP) yang super cepat, ringan, dan kaya fitur. Dibangun menggunakan PHP native untuk pemrosesan backend yang hemat memori dan Tailwind CSS untuk antarmuka pengguna (UI) modern berbasis Chips/Tags.

## ✨ Fitur Unggulan

- 🚀 Performa Tinggi (Memory-Efficient): Membaca file kamus besar secara baris-demi-baris (fopen/fgets) tanpa membebani RAM server.

- 📦 Multi-Sumber Otomatis: Mendukung banyak sumber kamus (KBBI, Kata Dasar, dll). Jika file belum ada, sistem akan otomatis mengunduhnya dari GitHub.

- 🎨 Modern Chips UI: Hasil pencarian ditampilkan dalam bentuk chips yang interaktif dan responsif.

- 📋 Copy to Clipboard: Cukup klik salah satu kata (chip) untuk menyalinnya secara instan (dilengkapi Toast Notification).

- 🔍 Mode Pencarian Pintar:

  - Awalan: Mencari kata yang berawalan spesifik.

  - Akhiran (Rima): Mencari kata yang berakhiran spesifik (sangat cocok untuk mencari sajak/rima puisi).

  - Mengandung: Mencari kata yang mengandung teks tertentu di mana saja.

- 🎛️ Filter Canggih:

  - Hanya Alfabet (A-Z) (Abaikan angka/simbol).

  - Minimal jumlah karakter.

  - Maksimal jumlah kata dalam satu hasil (berguna untuk menyaring frasa).

  - Batasan jumlah hasil pencarian (Limit).

- 🍪 Smart Cookies: Semua pengaturan filter dan mode pencarian otomatis tersimpan di peramban (browser) Anda.

## 🛠️ Prasyarat (Prerequisites)

- Web Server lokal (XAMPP, Laragon, MAMP, dll) atau server hosting dengan dukungan PHP 7.4 atau lebih baru.

- Koneksi Internet aktif (untuk memuat Tailwind CSS via CDN dan mengunduh file kamus pertama kali).

## 🚀 Cara Instalasi & Penggunaan

1. Unduh Script:
Simpan file kamus.php ke dalam folder root web server Anda (contoh: htdocs untuk XAMPP atau www untuk Laragon).
  - `cd path-to-your-xampp/htdocs` 
  - `git clone https://github.com/Kholbi/Kamus.git`

3. Jalankan Aplikasi:
Buka browser dan akses file tersebut, misalnya: http://localhost/kamus.php.

4. Auto-Download Kamus:
Saat pertama kali dijalankan, aplikasi akan otomatis mengunduh file teks kamus (seperti kbbi_lengkap.txt) ke direktori yang sama dengan kamus.php. Tunggu beberapa saat hingga halaman siap digunakan.

5. Mulai Mencari!
Ketik kata di kolom pencarian, sesuaikan filter, dan klik hasilnya untuk menyalin kata tersebut.

## ⚙️ Kustomisasi Sumber Kamus

Anda dapat dengan mudah menambahkan sumber kamus baru (file .txt, .csv, .lst, dsb yang berisi daftar kata per baris) dengan mengedit array $kamusSources di bagian paling atas file kamus.php:

```
$kamusSources = [
    'kbbi' => [
        'name' => 'KBBI Lengkap v1',
        'url' => '[https://raw.githubusercontent.com/.../WL01.txt](https://raw.githubusercontent.com/.../WL01.txt)',
        'filename' => 'kbbi_lengkap.txt'
    ],
    // Tambahkan kamus baru di sini
    'kamus_sunda' => [
        'name' => 'Kamus Bahasa Sunda',
        'url' => '[https://link-ke-file-kamus-sunda.txt](https://link-ke-file-kamus-sunda.txt)', // Kosongkan URL jika menggunakan file lokal murni
        'filename' => 'kamus_sunda.txt'
    ]
];
```


Catatan: Sistem akan otomatis mengubah ekstensi file yang diunduh menjadi .txt untuk keamanan dan konsistensi.

## 📂 Struktur File

Keseluruhan logika frontend dan backend berada di dalam satu file agar mudah dipindahkan (portable).

```
📁 Direktori Root/
├── 📄 kamus.php          (Aplikasi Utama)
├── 📄 kbbi_lengkap.txt   (Otomatis terunduh - Sumber KBBI)
└── 📄 kata_dasar.txt     (Otomatis terunduh - Sumber Kata Dasar)
```

## 📜 Lisensi

Proyek ini bersifat Open-Source. Anda bebas untuk memodifikasi, mengembangkan ulang, atau menggunakannya untuk keperluan pribadi dan komersial.

Dibuat dengan ❤️ untuk kemudahan eksplorasi kosakata Bahasa Indonesia.
