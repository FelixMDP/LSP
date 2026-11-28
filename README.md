# VaksinSehat

VaksinSehat adalah aplikasi pendaftaran vaksinasi yang mengelola perjalanan peserta dari pembuatan akun, pemilihan jadwal, verifikasi, hingga pembayaran. Admin mengelola persetujuan peserta, pendaftaran, pembayaran, dan pengumuman.

## Fitur dan kegunaannya

### Informasi publik

- Halaman utama `/` menyediakan satu tombol masuk menuju halaman `/login`. Pada halaman login, pengguna memilih peran admin atau peserta; pendaftaran akun peserta tersedia melalui `/register`.
- Halaman `/jadwal` dapat dibuka tanpa login. Pengunjung dapat melihat tanggal, jam, kuota, dan sisa kuota vaksinasi, tetapi tidak dapat mendaftar dari halaman ini.
- Rute lama `/login/admin` dan `/login/peserta` tetap tersedia sebagai pengalihan ke halaman login bersama.
- Beranda menampilkan panduan umum persiapan vaksinasi dan tiga pengumuman terbaru yang dapat dibaca publik.
- Beranda sebelum login juga menampilkan carousel berita imunisasi terbaru dengan ringkasan, tanggal, dan tautan sumber.
- Kuota dihitung dari pendaftaran yang masih berstatus `pending` atau `diterima`. Pendaftaran yang ditolak atau kedaluwarsa tidak lagi menggunakan kuota.

### Akun dan akses

- Peserta membuat akun dengan nama, email, kata sandi, NIK, nomor telepon, dan alamat.
- Akun peserta baru berstatus menunggu verifikasi. Admin menerima atau menolak akun sebelum peserta dapat menggunakan fitur pendaftaran.
- Admin dan peserta menggunakan satu halaman `/login` lalu memilih peran pada formulir. Pemeriksaan peran melindungi halaman masing-masing.
- Akun yang belum disetujui diarahkan ke halaman status akun. Peserta yang belum login atau belum disetujui tidak dapat menggunakan fitur pendaftaran dan pembayaran.

### Pendaftaran jadwal

- Peserta yang akunnya telah disetujui memilih jadwal yang masih memiliki kuota.
- Jadwal baru dibuat otomatis setiap hari pukul 00.05 waktu Jakarta jika tanggal hari tersebut belum memiliki jadwal. Slot bergantian pukul 08.00–12.00 dan 09.00–13.00, masing-masing berkuota 50, termasuk akhir pekan.
- Perintah dapat dijalankan manual dengan `php artisan vaksinasi:generate-daily-schedule`. Jika jadwal hari ini sudah ada, perintah tidak membuat duplikat atau mengubah jadwal yang ada.
- Jadwal lampau, kuota penuh, dan pendaftaran ganda ditolak oleh server.
- Pendaftaran baru berstatus `pending`. Pendaftaran tersebut menggunakan kuota sambil menunggu keputusan admin.
- Admin dapat melihat data peserta dan jadwal, kemudian menerima atau menolak pendaftaran. Keputusan dan identitas verifikator disimpan.

### Pembayaran

- Form pembayaran tersedia setelah pendaftaran jadwal diterima admin.
- Biaya ditetapkan server melalui `VAKSINASI_BIAYA` (nilai proyek saat ini Rp500.000); peserta tidak menentukan nominal sendiri.
- Setelah pendaftaran diterima, halaman pembayaran menampilkan nomor rekening simulasi yang stabil sebagai contoh alur transfer. Nomor tersebut fiktif dan bukan untuk transaksi nyata.
- Peserta mengunggah bukti pembayaran berformat JPG, JPEG, PNG, atau PDF dengan ukuran maksimum 5 MB. Bukti disimpan pada penyimpanan privat dan hanya admin berwenang yang dapat mengunduhnya.
- Admin menerima atau menolak bukti pembayaran. Peserta dapat melihat status dan alasan penolakan.
- Jika bukti ditolak, peserta memiliki waktu satu jam sejak penolakan admin untuk mengunggah ulang. Batas waktu ditampilkan menurut zona waktu perangkat, sementara perhitungan tenggat dilakukan server.
- Jika tidak ada unggahan ulang sebelum batas waktu, pendaftaran lama menjadi `kedaluwarsa`. Riwayat dan bukti yang ditolak tetap tersimpan, kuota dilepaskan, dan peserta harus membuat pendaftaran baru. Peserta dapat memilih jadwal yang sama kembali bila kuotanya masih tersedia.

### Pengumuman dan dashboard

- Admin dapat membuat, mengubah, dan menghapus pengumuman.
- Peserta dapat melihat daftar pengumuman yang diterbitkan.
- Dashboard admin dan peserta menampilkan ringkasan pengumuman terbaru agar informasi penting mudah ditemukan.
- Berita di dashboard peserta dapat digeser dengan kontrol sebelumnya/berikutnya atau scroll horizontal.
- Dashboard peserta menampilkan berita imunisasi bersumber dengan tanggal dan tautan. Berita baru dicari setiap hari menggunakan Gemini dan Google Search grounding, hanya disimpan bila topiknya relevan serta URL-nya terverifikasi; konten tetap bersifat edukatif, bukan diagnosis.
- Sapaan dashboard admin dan peserta menyesuaikan waktu lokal perangkat dan menampilkan tanggal hari ini.

## Alur kerja aplikasi

1. **Pengunjung melihat jadwal** — pengunjung dapat memeriksa kuota tanpa akun, tetapi tidak dapat melakukan pendaftaran.
2. **Peserta mendaftar akun** — akun baru dibuat dengan status menunggu verifikasi.
3. **Admin memverifikasi akun** — admin menerima atau menolak akun. Peserta yang diterima dapat masuk ke fitur peserta.
4. **Peserta memilih jadwal** — sistem memeriksa tanggal, kuota, dan pendaftaran ganda, lalu menyimpan pendaftaran berstatus menunggu.
5. **Admin memverifikasi pendaftaran** — admin menerima atau menolak pilihan jadwal dan sistem mencatat keputusan serta verifikator.
6. **Peserta mengirim pembayaran** — setelah pendaftaran diterima, peserta membayar sesuai biaya yang ditetapkan dan mengunggah bukti.
7. **Admin memverifikasi pembayaran** — pembayaran diterima atau ditolak. Jika ditolak, peserta dapat mengunggah ulang dalam satu jam.
8. **Sistem memulihkan kuota bila tenggat terlewati** — pendaftaran kedaluwarsa dan kuotanya tersedia kembali; peserta perlu mendaftar ulang.
9. **Peserta membaca pengumuman** — peserta melihat informasi terbaru melalui halaman pengumuman atau ringkasan di dashboard.

## Peran komponen aplikasi

- `routes/web.php` mendefinisikan URL publik, autentikasi, peserta, dan admin.
- `app/Http/Controllers/` menjalankan proses pendaftaran, pembayaran, verifikasi, dan pengumuman.
- `app/Models/` mewakili data akun, profil, jadwal, pendaftaran, pembayaran, serta relasinya.
- Middleware `role` dan `akun.diterima` membatasi fitur berdasarkan peran dan status akun.
- `database/migrations/` membangun struktur tabel dan indeks. Riwayat pendaftaran lama disimpan agar peserta dapat mendaftar kembali tanpa menghapus catatan sebelumnya.
- `config/vaksinasi.php` membaca nominal biaya dari `VAKSINASI_BIAYA`.
- `app/Console/Commands/ExpireRejectedPaymentRegistrations.php` mengubah pendaftaran yang melewati tenggat menjadi kedaluwarsa. Pengecekan juga dilakukan saat halaman jadwal atau pembayaran dibuka maupun saat peserta mencoba mengunggah ulang.
- `app/Console/Commands/GenerateDailyVaksinasiSchedule.php` menambahkan satu slot jadwal untuk tanggal hari ini bila belum tersedia.

## Menjalankan aplikasi

1. Pasang dependensi PHP dan JavaScript:

   ```sh
   composer install
   npm install
   ```

2. Siapkan `.env` dari `.env.example`, buat kunci aplikasi, dan atur koneksi database yang digunakan:

   ```sh
   php artisan key:generate
   ```

   Pastikan biaya tersedia di `.env`:

   ```dotenv
   VAKSINASI_BIAYA=500000
   ```

3. Jalankan migrasi dan seeder:

   ```sh
   php artisan migrate --seed
   ```

   Seeder menambahkan akun admin awal, contoh jadwal vaksinasi, dan berita edukasi awal.

4. Bangun aset frontend dan jalankan aplikasi:

   ```sh
   npm run build
   composer run dev
   ```

   `composer run dev` menjalankan web server, Vite, worker antrean, dan Laravel Scheduler. Scheduler memproses pendaftaran kedaluwarsa setiap menit, membuat jadwal pukul 00.05 WIB, serta mencari berita kesehatan pukul 08.00 WIB. Pada production, daftarkan Laravel Scheduler melalui cron atau penjadwal tugas sistem operasi.

5. Untuk pencarian berita otomatis, isi `GEMINI_API_KEY` di `.env` dengan API key dari Google AI Studio. `GEMINI_MODEL` secara default memakai `gemini-3.8-flash`. Jalankan `php artisan berita:ambil-harian` untuk mencoba secara manual; perintah menyimpan paling banyak satu berita per tanggal WIB, hanya bila ada berita baru yang relevan dan URL-nya terverifikasi melalui Google Search grounding. Komponen Search Suggestions dari Google turut ditampilkan sesuai ketentuan grounding. Penggunaan API dapat dikenai kuota atau biaya sesuai akun Google.

## Pengujian

Jalankan pengujian otomatis dengan:

```sh
php artisan test --compact
```

Pengujian fitur mencakup autentikasi dan verifikasi akun, kuota dan pendaftaran ganda, verifikasi pendaftaran, unggah serta verifikasi pembayaran, tenggat unggah ulang, pemulihan kuota, pengumuman, dan pembatasan akses.
