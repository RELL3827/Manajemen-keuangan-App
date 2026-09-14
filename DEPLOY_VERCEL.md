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

Sebelum menekan tombol Deploy, buka bagian **Environment Variables** di Vercel dan tambahkan variabel berikut:

| Key | Value / Penjelasan |
| :--- | :--- |
| `APP_NAME` | `Eltrack` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Salin nilai `APP_KEY` dari file `.env` lokal Anda (misal `base64:...`) |
| `APP_URL` | Kosongkan dahulu atau isi `https://nama-proyek-anda.vercel.app` setelah deploy |
| `LOG_CHANNEL` | `stderr` |
| `SESSION_DRIVER` | `cookie` *(atau `database` jika tabel sessions sudah dimigrasi)* |
| `CACHE_STORE` | `array` |

---

## Langkah 4: Database Cloud (Wajib untuk Vercel)

> [!IMPORTANT]
> **Mengapa Database Lokal (`127.0.0.1:3306`) Tidak Bisa Digunakan?**  
> Server Vercel berjalan di cloud secara terisolasi dan tidak dapat menjangkau komputer lokal Anda. Oleh karena itu, aplikasi membutuhkan database cloud yang dapat diakses publik via internet.

### Rekomendasi Database Cloud Gratis:
1. **TiDB Cloud Serverless (MySQL):**
   - Gratis hingga 25 GB storage (kompatibel penuh dengan MySQL Laravel tanpa perlu ubah kode).
   - Buka [tidbcloud.com](https://tidbcloud.com), buat cluster Serverless gratis.
   - Ambil Host, Port (4000), User, Password, dan Database.
2. **Supabase / Neon (PostgreSQL):**
   - Gratis dan sangat cepat. Ganti `DB_CONNECTION=pgsql` di Vercel.
3. **Aiven / Railway (MySQL):**
   - Menyediakan free tier / credit untuk MySQL.

### Tambahkan Variabel Database di Vercel Environment Variables:
```env
DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com  # contoh host cloud
DB_PORT=4000                                            # port cloud DB
DB_DATABASE=fintrack
DB_USERNAME=user_anda
DB_PASSWORD=password_anda
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
