# Dashboard & Sidebar Implementation

## 📁 Struktur File

```
resources/views/
├── layouts/
│   └── sidebar.blade.php       (Sidebar Component - Reusable)
├── auth/
│   └── login.blade.php         (Login Page)
└── dashboard.blade.php         (Dashboard Page)
```

## 🎯 File yang Dibuat

### 1. **Sidebar Component** (`resources/views/layouts/sidebar.blade.php`)
File terpisah yang berisi sidebar navigation. Bisa digunakan di semua halaman dengan hanya menambahkan satu baris:

```blade
@include('layouts.sidebar')
```

**Features:**
- Logo dan branding MAN 2 Surakarta
- Menu navigasi (Dashboard, Mail Management, Academic Records, dll)
- Help Center dan Logout button
- Responsive design
- Active state indicator
- Styling sudah included (tidak perlu CSS terpisah)

### 2. **Dashboard Page** (`resources/views/dashboard.blade.php`)
Halaman dashboard lengkap sesuai design Figma dengan:
- Top bar dengan search dan user info
- Welcome section dengan action buttons
- Stats cards (Surat Masuk, Surat Keluar, Disposisi, SPPD)
- Letter Tracking Status chart
- Recent Activity feed
- Responsive layout
- Semua styling included (CSS embedded, tanpa @vite)

### 3. **Login Page** (`resources/views/auth/login.blade.php`)
Halaman login yang sudah diupdate untuk menghilangkan Vite error.

## 🚀 Cara Menggunakan Sidebar untuk Halaman Baru

### Contoh: Membuat Halaman "Mail Management"

1. **Buat file baru**: `resources/views/mail/index.blade.php`

2. **Copy template dasar ini:**

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Management - MAN 2 Surakarta</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            min-height: 100vh;
            overflow-y: auto;
        }

        .top-bar {
            background: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid #e3e3e0;
        }

        .content {
            padding: 30px;
        }

        .page-title {
            color: #1b5e20;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Include Sidebar -->
        @include('layouts.sidebar')

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="top-bar">
                <h2 style="color: #1b1b18; font-size: 20px;">Mail Management</h2>
            </div>

            <!-- Main Content -->
            <div class="content">
                <h1 class="page-title">Daftar Mail</h1>
                
                <div class="card">
                    <!-- Konten halaman Anda di sini -->
                    <p>Konten Mail Management akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

3. **Update routes** di `routes/web.php`:

```php
Route::get('/mail', function () {
    return view('mail.index');
})->middleware('auth')->name('mail');
```

4. **Update sidebar menu** di `resources/views/layouts/sidebar.blade.php` (bagian `<nav class="sidebar-nav">`):

Ubah link dari `href="#"` menjadi `href="{{ route('mail') }}"`:

```blade
<a href="{{ route('mail') }}" class="nav-item {{ request()->routeIs('mail') ? 'active' : '' }}">
    <span class="nav-icon">📧</span>
    <span class="nav-label">Mail Management</span>
</a>
```

## ✅ Checklist

- [x] Hapus Vite error dengan menghilangkan @vite
- [x] Sidebar component terpisah (reusable)
- [x] Dashboard sesuai design Figma
- [x] Responsive design
- [x] CSS embedded (tidak perlu file CSS terpisah)
- [x] Struktur siap untuk prototype tanpa database

## 🎨 Color Reference

```css
Primary Green: #1b5e20
Dark Green: #155a1d
Light Green: #e8f5e9
Dark Text: #1b1b18
Gray Text: #706f6c
Light Gray: #f9f9f8
Border: #e3e3e0
```

## 📝 Catatan Penting

1. **Sidebar sudah include di setiap halaman** - Cukup copy template dan ganti konten di `.content` div

2. **Active state otomatis** - Sidebar akan highlight menu yang sekarang aktif berdasarkan route name yang digunakan `request()->routeIs()`

3. **Search bar fungsional** - Input search sudah ada di top bar, tinggal tambahkan action JS jika perlu

4. **Responsive** - Sidebar akan hilang di mobile, content akan full width

5. **User info dynamic** - Bisa diganti dengan data user yang login jika sudah ada di session

## 🔒 Proteksi dengan Middleware

Semua halaman sudah di-protect dengan middleware `auth`, jadi user harus login dulu.

## 🚀 Testing

1. Login dengan user: `admin` password: `password123`
2. Akses: `http://localhost:8000/dashboard`
3. Coba buat halaman baru dan include sidebar

---

**Siap untuk di-develop lebih lanjut tanpa khawatir repetisi kode!** 🎉
