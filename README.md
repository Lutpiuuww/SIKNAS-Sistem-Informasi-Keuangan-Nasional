# SIKNAS Enterprise (Sistem Informasi Keuangan Nasional)

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)

SIKNAS Enterprise adalah **Prototipe Sistem Pusat Komando Data Keuangan Nasional** berskala *Enterprise* yang dirancang untuk mensimulasikan pemantauan, otorisasi, dan analisis APBN negara secara *Real-Time*.

Aplikasi ini sangat cocok digunakan sebagai referensi arsitektur **SPBE (Sistem Pemerintahan Berbasis Elektronik)** untuk **Tugas Akhir**.

##  Fitur Unggulan (Killer Features)
1. **Live Stream Ticker & Pulsing Map**: Pemantauan arus kas nasional dan anomali secara *real-time* ke 38 Provinsi.
2. **Sentinel AI & Copilot**: 
   - *Backend*: Mendeteksi otomatis transaksi anomali (*Fraud Detection*).
   - *Frontend*: AI Copilot interaktif (Chatbot) untuk memprediksi defisit dan menganalisis anggaran.
3. **Role-Based Access Control (RBAC) Berjenjang**: Hak akses terisolasi untuk **Super Admin (Menteri)**, **Auditor**, dan **Gubernur** (hanya bisa melihat data daerahnya sendiri).
4. **Master Data Immutable**: Master Data Kementerian bersifat kebal manipulasi dari UI, menjaga standar keamanan APBN.
5. **Business Intelligence (BI) Dashboard**: Visualisasi data analitik kompleks menggunakan *ApexCharts* (Pie & Bar chart tingkat serapan).
6. **Workflow Otorisasi (Maker-Checker)**: Sistem *Approval* berjenjang (Pending -> Approved/Rejected) untuk entri manual.
7. **Official PDF Export**: Fitur cetak Laporan Ber-kop surat negara menggunakan injeksi CSS `@media print` murni.
8. **API Gateway Vault**: Simulasi *vault* penyediaan API Keys untuk integrasi mesin dengan sistem eksternal (OMSPAN, KRISNA, SIPD).

##  Arsitektur Database (Entity-Relationship)
Sistem ini menggunakan 4 tabel inti dan 1 tabel keamanan:
- `users`: Menyimpan data autentikasi dan peran (Menteri, Auditor, Gubernur).
- `regions`: Master data 38 Provinsi di Indonesia berserta koordinat lintang/bujur.
- `ministries`: Master data kementerian berserta triliunan pagu anggarannya.
- `transactions`: Tabel Big Data tempat mencatat keluar masuknya uang (IN/OUT), relasi wilayah, dan status (Success/Pending/Anomaly).
- `audit_logs`: Tabel *Immutable* (tidak bisa diubah/dihapus) yang merekam setiap gerak-gerik *user*, dari mulai login, cetak PDF, hingga menyetujui transaksi.

##  Cara Menjalankan Aplikasi
1. Pastikan **XAMPP / MySQL** sudah menyala.
2. Buka terminal, masuk ke direktori `D:\AI\siknas-enterprise`.
3. Jalankan server lokal: `php artisan serve`
4. Buka tab terminal baru, jalankan simulator arus kas otomatis: `php artisan siknas:stream`
5. Akses aplikasi di *browser*: `http://127.0.0.1:8000`

### Akun Demo Tersedia:
- **Menteri Keuangan (Super Admin):** `menteri@siknas.gov` (Akses Penuh + Otorisasi API)
- **Auditor BPK:** `auditor@siknas.gov` (Akses Audit & Baca Seluruh Transaksi)
- **Gubernur Jabar:** `gubernur@siknas.gov` (Akses Terbatas ke daerah Jawa Barat)
*Password untuk semua akun: `password`*

---
*Dikembangkan menggunakan pendekatan Arsitektur tingkat lanjut. Siap untuk di gunakan lembaga pemerintahan*
