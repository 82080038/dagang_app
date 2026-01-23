# Dagang App - Panduan Sinkronisasi & Aturan Operasional

## Ringkasan
- Aplikasi siap disinkronkan ke server LAMP dan komputer lain.
- Database referensi wilayah `alamat_db` tidak perlu diikutsertakan di repo (sudah tersedia di server Anda).
- Database utama aplikasi yang perlu diimpor: `perdagangan_system`.

## Aturan & Alur Form Alamat
- Urutan input alamat di seluruh form: Provinsi → Kabupaten/Kota → Kecamatan → Desa/Kelurahan → Alamat Jalan.
- Navigasi otomatis:
  - Saat field diisi/dipilih, fokus berpindah ke field berikutnya sesuai urutan di atas.
  - Saat dropdown (select) menerima fokus, daftar opsi dibuka otomatis.
  - Perpindahan fokus menunggu field berikutnya aktif (enabled) agar tidak melompat ke field lain.
- Kode Pos:
  - Ditampilkan otomatis (display-only) berdasarkan Desa/Kelurahan dari `alamat_db`.
  - Saat Desa dipilih, Kode Pos diisi dan fokus tetap pindah ke Alamat Jalan.
- Pengecualian:
  - Field disabled/readonly dilewati.
  - Jika validasi field belum lolos, fokus tidak berpindah sampai valid.

## Implementasi yang Sudah Dikerjakan
- Perusahaan
  - Menambahkan section alamat terstruktur di form “Tambah/Editar Perusahaan”.
  - Alamat Jalan berada tepat di bawah Desa/Kelurahan.
  - Validasi wajib alamat di create/update (province_id, regency_id, district_id, village_id, address_detail).
  - Kode Pos tampil otomatis dari `alamat_db`.
  - Prefill alamat saat edit, termasuk dropdown cascade.
  - **FIXED**: Field name inconsistency - sekarang menggunakan `address_detail` secara konsisten
- Cabang
  - Menambahkan section alamat terstruktur di “Tambah Cabang”.
  - Provinsi/Kabupaten/Kecamatan mengikuti dan terkunci dari perusahaan induk; Desa opsional.
  - Alamat Jalan wajib; Kode Pos tampil otomatis.
  - **FIXED**: Field name inconsistency - sekarang menggunakan `address_detail` secara konsisten
- Register
  - Menambahkan halaman register dengan section alamat lengkap (wajib).
  - Kode Pos tampil otomatis; Alamat Jalan berada di bawah Desa/Kelurahan.
  - **FIXED**: Field name inconsistency - sekarang menggunakan `address_detail` secara konsisten
- Navigasi otomatis & aksesibilitas
  - Disiapkan secara global agar berlaku di semua form (termasuk modal).
  - Mempertahankan navigasi keyboard manual (Tab/Shift+Tab).
- Konfigurasi database
  - Koneksi membaca variabel environment (.env) agar mudah dipindahkan lintas mesin.

## 🚨 Critical Issues Fixed (Latest Updates)
- **Address Field Standardization**: 
  - Fixed inconsistency between `street_address` vs `address_detail` across entire application
  - Standardized to `address_detail` in all components (database, models, controllers, views, JavaScript)
  - Edit Perusahaan functionality now works correctly
- **Database Schema Updates**:
  - Added address fields to companies and branches tables
  - Updated addresses table to use `address_detail` instead of `street_address`
  - Created comprehensive migration scripts
- **Comprehensive Testing**:
  - Created test scripts to verify address functionality
  - Fixed all maintenance file references
  - Updated documentation with new address management system

## Database & Migrasi
- Database utama: `perdagangan_system`
- Skema & seeds utama:
  - `perdagangan_database.sql` (berisi skema + data contoh).
- Migrasi pendukung:
  - `database_migrations/create_centralized_addresses.sql` (tabel addresses + FK ke companies/branches + contoh alamat).
  - `database_migrations/create_transaction_tables.sql`, `database_migrations/create_inventory_tables.sql`, `database_migrations/create_missing_tables.sql` (tanpa seeds).
- Skrip bantu (opsional):
  - `maintenance/setup_centralized_addresses.php`, `maintenance/setup_addresses_simple.php`
  - `maintenance/run_transaction_migration.php`, `maintenance/run_inventory_migration.php`, `maintenance/run_missing_tables_v2.php`
- Backup siap impor (opsional):
  - `database_exports/perdagangan_system_backup.sql`

## Hak Akses ke alamat_db
- Pastikan user DB aplikasi memiliki hak SELECT ke:
  - `alamat_db.provinces`, `alamat_db.regencies`, `alamat_db.districts`, `alamat_db.villages`
- `alamat_db` harus berada di server MySQL yang sama agar cross-database JOIN berjalan.

## Konfigurasi Koneksi
- Salin `.env.example` menjadi `.env` lalu isi kredensial:
  - DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_CHARSET
- Aplikasi membaca variabel environment untuk koneksi DB.

## Langkah Impor di LAMP / Komputer Tujuan
- Buat database `perdagangan_system`:
  - `CREATE DATABASE perdagangan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- Import skema dan migrasi:
  - `mysql -u <user> -p -D perdagangan_system < perdagangan_database.sql`
  - `mysql -u <user> -p -D perdagangan_system < database_migrations/create_centralized_addresses.sql`
- **NEW**: Run address field standardization migrations:
  - `mysql -u <user> -p -D perdagangan_system < database_migrations/fix_address_field_names.sql`
  - `mysql -u <user> -p -D perdagangan_system < database_migrations/add_address_fields_to_main_tables.sql`
- **NEW**: Test address functionality:
  - `php maintenance/test_address_functionality.php`
- Set `.env` sesuai kredensial DB di mesin tujuan.
- Pastikan `alamat_db` tersedia dan user memiliki hak SELECT.
- Verifikasi endpoints alamat:
  - `index.php?page=address&action=get-provinces`
  - `index.php?page=address&action=get-regencies&province_id=<id>`
  - `index.php?page=address&action=get-districts&regency_id=<id>`
  - `index.php?page=address&action=get-villages&district_id=<id>`

## Skrip Import Otomatis
- Linux:
  - `DB_HOST=localhost DB_USER=root DB_PASS= DB_NAME=perdagangan_system bash scripts/import_perdagangan_system.sh`
- Windows:
  - Set environment DB_HOST, DB_USER, DB_PASS, DB_NAME, lalu jalankan:
  - `scripts\import_perdagangan_system.bat`

## Cara Uji Cepat
- Perusahaan
  - Buka “Tambah Perusahaan Baru”; isi alamat berurutan dan pastikan fokus otomatis berjalan Propinsi → Kabupaten → Kecamatan → Desa → Alamat Jalan.
  - Simpan; pastikan perusahaan tersimpan, `address_id` terhubung.
  - Edit; cek prefill alamat dan Kode Pos tampil otomatis.
- Cabang
  - Pilih Perusahaan Induk; cek Provinsi/Kab/Kec mengikuti dan terkunci; pilih Desa; Kode Pos tampil; isi Alamat Jalan; simpan.
- Register
  - Isi data pengguna dan alamat lengkap; cek navigasi dan Kode Pos otomatis; daftar dan login.

## Langkah Lanjutan di Komputer Lain
- Setup Apache DocumentRoot (opsional) ke `public` jika diperlukan.
- Pastikan PHP ekstensi `pdo_mysql` aktif.
- Tambahkan hak akses user DB terhadap `alamat_db`.
- Jalankan migrasi tambahan (transaction/inventory/missing_tables) jika modul terkait akan digunakan.
- Lengkapi data produksi (produk, kategori, perusahaan, cabang) sesuai kebutuhan.

## Sinkron ke GitHub
- Inisialisasi repo lokal:
  - `git init`
  - `git add .`
  - `git commit -m "Initialize dagang app"`
- Set remote:
  - `git remote add origin https://github.com/82080038/dagang_app.git`
  - `git branch -M main`
- Push menggantikan konten lama:
  - `git push -u origin main --force`
  - Catatan: Penggunaan `--force` akan menimpa isi repo remote dengan konten aplikasi ini.
 - Alternatif skrip:
   - Linux: `REPO_URL=https://github.com/82080038/dagang_app.git BRANCH=main bash scripts/sync_to_github.sh`
   - Windows: `scripts\sync_to_github.bat`
   - Opsional gunakan `GIT_TOKEN` environment untuk akses HTTPS non-interaktif.

## Troubleshooting Ringkas
- Dropdown alamat tidak memuat:
  - Pastikan koneksi DB benar dan user memiliki hak SELECT ke `alamat_db`.
- Kode Pos tidak tampil:
  - Pastikan Desa/Kelurahan dipilih; cek endpoint `get-postal-code`.
- Navigasi otomatis tidak berjalan:
  - Pastikan file `public/assets/js/theme.js` termuat dan tidak ada error JS di console.
