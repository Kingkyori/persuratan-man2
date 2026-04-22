# 📋 SISTEM PERSURATAN MAN 2 SURAKARTA
## Surat Masuk Registration System

**Version**: 2.0.0 (Google Apps Script Edition)
**Status**: ✅ PRODUCTION READY
**Last Updated**: 2024-04-22

---

## 🚀 QUICK START (2 MINUTES)

```bash
# Step 1: Migrate database
php artisan migrate

# Step 2: Run Laravel
php artisan serve

# Step 3: Open browser
http://localhost:8000/surat-masuk
```

✅ **Done!** System is ready to use.

---

## ✨ FEATURES

- ✅ Modal form untuk register surat masuk
- ✅ File upload ke Google Drive (via Apps Script)
- ✅ MySQL database storage
- ✅ Complete CRUD operations
- ✅ Status management (Pending, Done, Disposed)
- ✅ Search & filter functionality
- ✅ Responsive design (desktop, tablet, mobile)
- ✅ CSRF protected
- ✅ Form validation

---

## 📚 DOCUMENTATION

| Document | Time | Purpose |
|----------|------|---------|
| [QUICK_SETUP_APPS_SCRIPT.md](QUICK_SETUP_APPS_SCRIPT.md) | 30 sec | Quick reference |
| [SETUP_APPS_SCRIPT.md](SETUP_APPS_SCRIPT.md) | 5 min | Complete setup guide |
| [START_HERE.md](START_HERE.md) | 5 min | Getting started |
| [UPDATE_SUMMARY_v2.md](UPDATE_SUMMARY_v2.md) | 5 min | What changed |
| [DOKUMENTASI_SURAT_MASUK.md](DOKUMENTASI_SURAT_MASUK.md) | 10 min | How to use |

---

## 🎯 MAIN FEATURES

### 1. Register New Surat Masuk
Click "✚ Register New Surat Masuk" → Modal form with:
- Origin (required) - Asal/pengirim
- Reception Date (required) - Tanggal penerimaan
- Letter Number (required, unique) - Nomor surat
- Subject (required) - Perihal
- Status - Pending/Done/Disposed
- File upload (max 10MB, PDF/JPG/PNG)

File automatically uploads to Google Drive via your Apps Script endpoint!

### 2. View & Manage
- Table displays all surat masuk
- Change status via dropdown
- Delete with confirmation
- View details with modal
- Search by origin/subject/number

---

## 🔗 API ENDPOINTS

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/surat-masuk` | Display page |
| POST | `/surat-masuk/store` | Create entry |
| POST | `/surat-masuk/{id}/status` | Update status |
| DELETE | `/surat-masuk/{id}` | Delete entry |

---

## 💾 DATABASE

```sql
CREATE TABLE surat_masuk (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  origin VARCHAR(255) NOT NULL,
  reception_date DATE NOT NULL,
  letter_number VARCHAR(255) NOT NULL UNIQUE,
  subject VARCHAR(255) NOT NULL,
  status ENUM('pending','done','disposed') DEFAULT 'pending',
  google_drive_link VARCHAR(500),
  user_id BIGINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## 📁 PROJECT STRUCTURE

```
app/
├── Http/Controllers/SuratMasukController.php
└── Models/SuratMasuk.php

database/
├── migrations/2024_04_22_000001_*
└── surat_masuk.sql

resources/views/
└── surat-masuk.blade.php

public/css/
└── modal.css

routes/web.php
```

---

## 🔐 SECURITY

✅ CSRF Protection
✅ File validation (PDF/JPG/PNG only)
✅ File size limit (10MB max)
✅ Input sanitization (Eloquent ORM)
✅ Secure uploads via Google Apps Script

---

## 🧪 TESTING

```bash
# 1. Run migrations
php artisan migrate

# 2. Start Laravel
php artisan serve

# 3. Open browser
http://localhost:8000/surat-masuk

# 4. Test the form:
- Fill in all required fields
- Upload a PDF/JPG file
- Click Submit
- Verify data appears in table
- Check Google Drive for uploaded file
```

---

## 🛠️ TROUBLESHOOTING

| Issue | Solution |
|-------|----------|
| Database error | Check `.env` DB settings |
| File upload failed | Check file size < 10MB, format OK |
| Page not loading | Run `php artisan migrate` |
| Form not submitting | Check browser console (F12) |

---

## 📊 REQUIREMENTS

- PHP 8.2+
- Laravel 12
- MySQL 8.0+
- Composer

---

## 🚀 DEPLOYMENT

```bash
# Prepare for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Run migrations on server
php artisan migrate --force
```

---

## 📝 VERSION

- **Version**: 2.0.0
- **Google Apps Script**: Fully integrated
- **Setup Time**: 2 minutes
- **Status**: ✅ Production Ready

---

## 🙏 THANK YOU!

Built for MAN 2 Surakarta

**Happy organizing! 📋✨**

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
