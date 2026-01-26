# SIKAP – Sistem Informasi Kerja Praktik

Sistem informasi manajemen Kerja Praktik (KP) untuk **Fakultas Teknik Universitas Jenderal Soedirman (UNSOED)**. Aplikasi ini dibangun menggunakan **Laravel 12**, **Livewire**, dan **Flux UI**.

---

## 📋 Daftar Isi

- [Fitur](#-fitur)
- [Tech Stack](#-tech-stack)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Akun Demo](#-akun-demo)
- [Struktur Role & Hak Akses](#-struktur-role--hak-akses)
- [Alur Kerja Praktik](#-alur-kerja-praktik)

---

## 🚀 Fitur

### Mahasiswa
- 📝 Mengajukan Surat Pengantar KP
- 📄 Mengajukan Kerja Praktik
- 💬 Melakukan Konsultasi/Bimbingan dengan Dosen Pembimbing
- 📅 Mendaftar Seminar KP
- 📊 Melihat Nilai KP
- 📥 Download dokumen (Surat Pengantar, SPK, Berita Acara)

### Dosen Pembimbing
- ✅ Verifikasi konsultasi mahasiswa bimbingan
- 📋 Menyetujui pendaftaran seminar
- 📊 Input nilai KP mahasiswa

### Dosen Komisi
- 🔍 Review pengajuan KP mahasiswa
- ✅ Approve/reject pengajuan KP
- 📊 Melihat rekap nilai

### Bapendik (Bagian Pendidikan)
- 📄 Validasi & menerbitkan Surat Pengantar
- 📑 Menerbitkan SPK (Surat Penunjukan Kerja Praktik)
- 📅 Menjadwalkan Seminar KP
- 📃 Menerbitkan Berita Acara Seminar
- 👥 Manajemen Master Data (Dosen, Mahasiswa, Ruangan, Jurusan, Penandatangan)
- 📊 Melihat rekap nilai

---

## 🛠️ Tech Stack

| Komponen        | Teknologi                        |
| --------------- | -------------------------------- |
| Framework       | Laravel 12                       |
| Frontend        | Livewire + Volt + Flux UI        |
| CSS             | Tailwind CSS 4                   |
| Database        | SQLite (default) / MySQL         |
| Authentication  | Laravel Fortify                  |
| Authorization   | Spatie Laravel Permission        |
| PDF Generation  | DomPDF, mPDF                     |
| Document Export | PhpWord                          |
| Excel Export    | Maatwebsite Excel                |
| Build Tool      | Vite 7                           |

---

## 📦 Persyaratan Sistem

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x
- **NPM** >= 9.x
- **Git**

---

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/your-username/sikap-ft-unsoed.git
cd sikap-ft-unsoed
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Setup Database

Secara default, aplikasi menggunakan **SQLite**. File database akan otomatis dibuat.

```bash
# Jalankan migrasi database
php artisan migrate

# (Opsional) Jalankan seeder untuk data demo
php artisan db:seed
```

> **💡 Tips:** Untuk menggunakan MySQL, edit file `.env` dan ubah konfigurasi database:
> ```env
> DB_CONNECTION=mysql
> DB_HOST=127.0.0.1
> DB_PORT=3306
> DB_DATABASE=sikap_ft_unsoed
> DB_USERNAME=root
> DB_PASSWORD=your_password
> ```

### 5. Build Assets

```bash
# Build untuk production
npm run build
```

---

## ▶️ Menjalankan Aplikasi

### Mode Development

Gunakan perintah berikut untuk menjalankan server development dengan hot reload:

```bash
composer dev
```

Perintah ini akan menjalankan:
- 🌐 **Laravel Server** di `http://localhost:8000`
- 📬 **Queue Worker** untuk proses background
- ⚡ **Vite Dev Server** untuk hot reload assets

### Mode Alternatif (Manual)

Jika ingin menjalankan secara terpisah:

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Dev Server
npm run dev

# Terminal 3: Queue Worker (opsional)
php artisan queue:listen
```

Buka browser dan akses: **http://localhost:8000**

---

## 👤 Akun Demo

Setelah menjalankan `php artisan db:seed`, Anda dapat login dengan akun-akun berikut:

| Role              | Email                     | Password   | Keterangan                      |
| ----------------- | ------------------------- | ---------- | ------------------------------- |
| **Bapendik**      | bapendik@example.com      | `password` | Admin Bagian Pendidikan         |
| **Dosen Pembimbing** | dsp@example.com        | `password` | Dosen Pembimbing Demo           |
| **Dosen Komisi**  | kom@example.com           | `password` | Dosen Komisi KP                 |
| **Mahasiswa Baru**| mhs_baru@example.com      | `password` | Mahasiswa belum ada aktivitas   |
| **Mahasiswa (Ajukan SP)** | mhs_sp@example.com | `password` | Sedang mengajukan Surat Pengantar |
| **Mahasiswa (Ajukan KP)** | mhs_submit@example.com | `password` | Sedang mengajukan KP           |
| **Mahasiswa (Bimbingan)** | mhs_active@example.com | `password` | Sedang dalam proses bimbingan  |
| **Mahasiswa (Siap Seminar)** | mhs_seminar@example.com | `password` | Siap mendaftar seminar       |
| **Mahasiswa (Selesai)** | mhs_nilai@example.com | `password` | KP sudah selesai & dinilai     |

---

## 🔐 Struktur Role & Hak Akses

### Roles

| Role             | Deskripsi                                           |
| ---------------- | --------------------------------------------------- |
| Mahasiswa        | Peserta Kerja Praktik                               |
| Dosen Pembimbing | Membimbing mahasiswa selama KP                      |
| Dosen Komisi     | Mereview dan menyetujui pengajuan KP                |
| Bapendik         | Admin Bagian Pendidikan (manajemen dokumen & jadwal)|

### Permissions

| Permission        | Deskripsi                              |
| ----------------- | -------------------------------------- |
| sp.create         | Membuat Surat Pengantar                |
| sp.view           | Melihat Surat Pengantar                |
| sp.validate       | Memvalidasi Surat Pengantar            |
| kp.create         | Membuat pengajuan KP                   |
| kp.view           | Melihat data KP                        |
| kp.review         | Mereview pengajuan KP                  |
| kp.approve        | Menyetujui pengajuan KP                |
| bimbingan.create  | Membuat catatan bimbingan              |
| bimbingan.view    | Melihat catatan bimbingan              |
| bimbingan.verify  | Memverifikasi bimbingan                |
| seminar.register  | Mendaftar seminar                      |
| seminar.schedule  | Menjadwalkan seminar                   |
| seminar.view      | Melihat jadwal seminar                 |
| nilai.input       | Menginput nilai                        |
| nilai.view        | Melihat nilai                          |
| masterdata.manage | Mengelola master data                  |

---

## 📋 Alur Kerja Praktik

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ALUR KERJA PRAKTIK                              │
└─────────────────────────────────────────────────────────────────────────┘

1. PENGAJUAN SURAT PENGANTAR
   ┌──────────┐      ┌──────────┐      ┌──────────┐
   │ Mahasiswa│ ───▶ │ Bapendik │ ───▶ │  Terbit  │
   │ Ajukan SP│      │ Validasi │      │    SP    │
   └──────────┘      └──────────┘      └──────────┘

2. PENGAJUAN KERJA PRAKTIK
   ┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐
   │ Mahasiswa│ ───▶ │  Dosen   │ ───▶ │ Bapendik │ ───▶ │  Terbit  │
   │ Ajukan KP│      │  Komisi  │      │ Terbit   │      │   SPK    │
   └──────────┘      │  Review  │      │   SPK    │      └──────────┘
                     └──────────┘      └──────────┘

3. PELAKSANAAN KP & BIMBINGAN
   ┌──────────┐      ┌──────────┐      ┌──────────┐
   │ Mahasiswa│ ───▶ │  Dosen   │ ───▶ │ Minimal  │
   │Konsultasi│      │ Verifikasi│     │ 8x ACC   │
   └──────────┘      └──────────┘      └──────────┘

4. SEMINAR KP
   ┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐
   │ Mahasiswa│ ───▶ │  Dosen   │ ───▶ │ Bapendik │ ───▶ │ Seminar  │
   │ Daftar   │      │ Approve  │      │ Jadwal   │      │Terlaksana│
   └──────────┘      └──────────┘      └──────────┘      └──────────┘

5. PENILAIAN
   ┌──────────┐      ┌──────────┐      ┌──────────┐
   │  Dosen   │ ───▶ │ Bapendik │ ───▶ │  Selesai │
   │Input Nilai│     │Terbit BA │      │          │
   └──────────┘      └──────────┘      └──────────┘
```

---

## 📁 Struktur Direktori

```
sikap-ft-unsoed/
├── app/
│   ├── Http/Controllers/    # Controller untuk download & verify dokumen
│   ├── Livewire/            # Komponen Livewire per role
│   │   ├── Bapendik/        # Komponen Bapendik
│   │   ├── Dosen/           # Komponen Dosen
│   │   ├── Komisi/          # Komponen Dosen Komisi
│   │   └── Mahasiswa/       # Komponen Mahasiswa
│   ├── Models/              # Eloquent Models
│   └── Services/            # Business Logic Services
├── database/
│   ├── factories/           # Model Factories
│   ├── migrations/          # Database Migrations
│   └── seeders/             # Database Seeders
├── resources/
│   ├── views/               # Blade Templates
│   └── css/                 # Stylesheets
├── routes/
│   └── web.php              # Web Routes
└── public/                  # Public Assets
```

---

## 📝 Lisensi

Proyek ini dikembangkan untuk keperluan internal **Fakultas Teknik Universitas Jenderal Soedirman**.

---

## 🤝 Kontribusi

Silakan buat Pull Request atau laporkan issue jika menemukan bug atau ingin menambahkan fitur baru.

---

**Dibuat dengan ❤️ untuk Fakultas Teknik UNSOED**
