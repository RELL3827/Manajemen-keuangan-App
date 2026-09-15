# Panduan Lengkap Deploy FinTrack ke GitHub dan Vercel

Panduan ini memandu Anda langkah demi langkah untuk mengunggah proyek **FinTrack (Laravel 12)** ke **GitHub** dan menghubungkannya ke **Vercel**.

---

## 📋 Ringkasan File yang Sudah Dikonfigurasi
Kami telah menyiapkan konfigurasi berikut agar Laravel siap berjalan di lingkungan serverless Vercel:
1. **`vercel.json`**: Mengatur runtime serverless PHP (`vercel-php@0.7.3`), routing asset statis Vite (`/public/build`), dan environment default.
2. **`api/index.php`**: Entrypoint serverless Vercel yang otomatis menyiapkan folder temporary (`/tmp/storage` & `/tmp/bootstrap/cache`) karena filesystem Vercel bersifat *read-only*.
3. **`.vercelignore`**: Mencegah pengunggahan file lokal yang tidak diperlukan (`node_modules`, `tests`, dll).
4. **`package.json`**: Menambahkan skrip `"vercel-build": "vite build"` agar Vercel otomatis mengompilasi CSS/JS frontend saat deployment.

---

## Langkah 1: Inisialisasi & Push ke GitHub

Anda dapat memilih salah satu dari dua opsi repositori:

### Opsi A (Direkomendasikan: Repositori Khusus Web `fintrack`)
1. Buka terminal (PowerShell atau Command Prompt) di dalam folder `fintrack`:
   ```bash
   cd "c:\Users\PIXWAR\Documents\Manajemen Keuangan\fintrack"
   ```
2. Inisialisasi Git dan buat commit pertama:
   ```bash
   git init
   git add .
   git commit -m "feat: setup deployment untuk GitHub dan Vercel"
   ```
3. Buka [GitHub](https://github.com), login, dan klik **New Repository**.
   - Beri nama repository, misalnya: `fintrack` atau `eltrack-web`.
   - Biarkan opsi *Add README*, *.gitignore*, dan *license* tidak dicentang (karena kita sudah memilikinya).
   - Klik **Create repository**.
4. Hubungkan remote GitHub dan push kode Anda:
   ```bash
   git branch -M main
   git remote add origin https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git
   git push -u origin main
   ```

*(Ganti `USERNAME_ANDA` dan `NAMA_REPO_ANDA` sesuai akun GitHub Anda).*

---

## Langkah 2: Deploy ke Vercel

1. Buka [vercel.com](https://vercel.com) dan login (disarankan login menggunakan akun **GitHub**).
2. Di dashboard Vercel, klik tombol **Add New...** -> **Project**.
3. Cari repositori yang baru saja Anda push ke GitHub (`fintrack`), lalu klik **Import**.
4. Pada form **Configure Project**:
   - **Project Name**: Masukkan nama proyek (misal `fintrack-app`).
   - **Framework Preset**: Pilih **Other** (biarkan default).
   - **Root Directory**:
     - Jika menggunakan *Opsi A*, biarkan `./`.
     - Jika menggunakan *Opsi B (root folder)*, klik Edit dan pilih folder `fintrack`.
   - **Build and Output Settings**: Biarkan default (Vercel otomatis menjalankan build Vite).

---

## Langkah 3: Pengaturan Environment Variables di Vercel

Buka dashboard Vercel -> Proyek Anda -> **Settings** -> **Environment Variables**. Tambahkan variabel-variabel berikut (pastikan hanya mengisi nilainya saja, jangan sertakan tanda kurung atau penjelasan):

| Key | Value | Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `Eltrack` | Nama aplikasi |
| `APP_ENV` | `production` | Environment |
| `APP_DEBUG` | `false` | Nonaktifkan debug di production |
| `APP_KEY` | `base64:k0kzXETEssPeFOZA+G/jQ8Rjvj+fqBQQkguUzwaq9vs=` | Kunci enkripsi aplikasi |
| `APP_URL` | `https://manajemen-keuangan-d6pxcmxwu-rell3827s-projects.vercel.app` | URL Vercel aplikasi Anda |
| `LOG_CHANNEL` | `stderr` | Agar error log tercatat di Vercel Functions Log |
| `SESSION_DRIVER` | `cookie` | Sangat direkomendasikan untuk Vercel Serverless |
| `CACHE_STORE` | `array` | Cache memori untuk serverless |
| `DB_CONNECTION` | `pgsql` | Koneksi database Supabase PostgreSQL |
| `DB_HOST` | `aws-0-ap-northeast-1.pooler.supabase.com` | Host Supabase |
| `DB_PORT` | `5432` | Port Supabase |
| `DB_DATABASE` | `postgres` | Nama database Supabase |
| `DB_USERNAME` | `postgres.uxochfhkivcpufnhlqmj` | Username Supabase |
| `DB_PASSWORD` | `BSAashgf123856` | Password database Supabase |

> [!TIP]
> **Penting Mengenai `SESSION_DRIVER`:**  
> Selalu gunakan value `cookie` pada `SESSION_DRIVER` di Vercel. Jangan biarkan kosong! `cookie` membuat session login pengguna disimpan secara aman dan terenkripsi di browser pengguna, sehingga tidak terpengaruh oleh restart serverless container Vercel.

---

## Langkah 4: Verifikasi Database Supabase

Database Supabase sudah terhubung dan semua migrasi tabel sudah berhasil dijalankan:
- `users`, `password_reset_tokens`, `sessions`
- `cache`, `jobs`
- `accounts`, `categories`, `transactions`, `transfers`, `budgets`, `voice_notes`, `financial_insights`, `notifications`

Jika di kemudian hari Anda menambahkan migrasi baru, jalankan dari terminal lokal:
```bash
php artisan migrate --force
```

### Cara Menjalankan Migrasi ke Database Cloud:
Di komputer lokal Anda, buka file `.env` sementara, ubah `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` mengarah ke database cloud, lalu jalankan:
```bash
php artisan migrate --force
```
Setelah migrasi selesai di database cloud, kembalikan `.env` lokal Anda ke konfigurasi lokal semula jika ingin tetap menggunakan MySQL lokal untuk development.

---

## Langkah 5: Selesaikan Deployment

1. Klik tombol **Deploy** di Vercel.
2. Vercel akan mengunduh dependensi Composer via `vercel-php`, menjalankan build asset Vite, dan mempublikasikan aplikasi Anda.
3. Setelah proses selesai, klik URL domain `https://xxxx.vercel.app` yang disediakan untuk membuka aplikasi FinTrack Anda!

---

## 💡 Catatan Tambahan:
- **Pengembangan Lokal:** File `.bat` (`Eltrack-Windows.bat`, `Buat-Shortcut-Desktop.bat`) dan perintah lokal `php artisan serve` tetap berfungsi normal seperti biasa tanpa terpengaruh oleh konfigurasi Vercel.
- **Setiap kali melakukan perubahan kode:** Anda hanya perlu melakukan `git commit` dan `git push origin main`, dan Vercel akan otomatis men-deploy versi terbaru secara otomatis (*Continuous Deployment*).
