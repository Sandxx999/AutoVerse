<div align="center">

# ⬡ AutoVerse
### Smart Car Marketplace — Full Stack Web App

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap_5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

A premium, fully functional car marketplace web app inspired by CarDekho, Spinny, and Cars24 — built with a PHP + MySQL backend and a sleek dark-themed frontend.

[Features](#-features) • [Tech Stack](#-tech-stack) • [Folder Structure](#-folder-structure) • [Setup](#-setup-guide) • [API Docs](#-api-endpoints) • [Screenshots](#-screenshots)

</div>

---

## 🚗 About AutoVerse

AutoVerse is a full-stack car marketplace where users can browse, search, filter, and enquire about cars. It includes an admin dashboard, user authentication, a wishlist system, an EMI calculator, and a complete REST API built in PHP.

---

## ✨ Features

- 🏠 **Hero Section** — Animated floating car, counter stats, multi-tab search bar
- 🔍 **Live Search & Filters** — Filter by type, fuel, brand, price, and sort order
- 🚗 **Car Listings Grid** — Dynamic cards with badges, ratings, and EMI hints
- ❤️ **Wishlist / Favorites** — Add and manage favourite listings
- 📊 **EMI Calculator** — Animated donut chart with adjustable sliders
- 📝 **Sell Your Car** — Submit a listing form that adds to the grid live
- 🔐 **User Authentication** — Register, login, logout via PHP sessions
- 📋 **Car Detail Modal** — Full specs, images, and enquiry button
- 📬 **Contact / Enquiry Forms** — Submit and track test-drive requests
- 🛠️ **Admin Dashboard** — Manage listings, users, and enquiries
- 📱 **Fully Responsive** — Mobile, tablet, and desktop ready
- 🎨 **Premium Dark Theme** — Red accent, Bebas Neue + Syne typography

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, Vanilla JavaScript |
| Backend | PHP 7.4+ (compatible with PHP 8.x) |
| Database | MySQL 8 / MariaDB 10.5 |
| Local Server | XAMPP (Apache + MySQL) |
| Icons | Font Awesome 6 |
| Fonts | Bebas Neue, Syne, DM Sans (Google Fonts) |

---

## 📁 Folder Structure

```
autoverse/
├── index.html              ← Main frontend page
├── style.css               ← All CSS styles & animations
├── app.js                  ← Frontend JavaScript
├── guide.html              ← Frontend build guide
├── guide-php.html          ← PHP integration guide
│
├── config/
│   └── db.php              ← Database connection & helpers
│
├── api/
│   ├── cars.php            ← CRUD API for car listings
│   ├── auth.php            ← Register / Login / Logout
│   ├── favorites.php       ← Wishlist toggle
│   └── enquiries.php       ← Contact / test-drive forms
│
├── admin/
│   ├── index.php           ← Admin dashboard
│   └── login.php           ← Admin login page
│
├── database/
│   └── schema_fixed.sql    ← Run this in phpMyAdmin to set up DB
│
└── tools/
    ├── fix_admin.php        ← Fix admin password (run once)
    ├── download_images.php  ← Auto-download car images (run once)
    ├── manual_images.php    ← Upload images manually
    └── reset_admin.php      ← Emergency password reset
```

---

## ⚙️ Setup Guide

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + MySQL stack)
- PHP 7.4 or higher
- A modern browser

---

### Step 1 — Install XAMPP
Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/).  
Start both **Apache** and **MySQL** modules from the XAMPP Control Panel.

### Step 2 — Place Project Files

Clone this repository into your XAMPP `htdocs` folder:

```bash
# Windows
C:\xampp\htdocs\autoverse

# Linux
/opt/lampp/htdocs/autoverse/

# macOS
/Applications/XAMPP/htdocs/autoverse/
```

```bash
git clone https://github.com/Sandxx999/AutoVerse.git autoverse
```

### Step 3 — Create the Database

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** → Name it `autoverse_db` → Collation: `utf8mb4_unicode_ci` → **Create**
3. Click on `autoverse_db` → go to the **SQL** tab
4. Paste the contents of `database/schema_fixed.sql` → Click **Go**

### Step 4 — Configure Database Connection

Open `config/db.php` and update the credentials if needed:

```php
$host     = 'localhost';
$dbname   = 'autoverse_db';
$username = 'root';
$password = '';        // default XAMPP password is empty
```

### Step 5 — Fix Admin Password

Open in your browser:
```
http://localhost/autoverse/tools/fix_admin.php
```
You should see: **"Hash verifies YES ✅"**

### Step 6 — Download Car Images

Open in your browser:
```
http://localhost/autoverse/tools/download_images.php
```
Wait for all images to show a green status.

### Step 7 — Launch the App 🎉

| Page | URL |
|---|---|
| Frontend | http://localhost/autoverse/index.html |
| Admin Panel | http://localhost/autoverse/admin/login.php |

---

## 🔑 Default Login Credentials

### Admin
| Field | Value |
|---|---|
| Email | `admin@autoverse.in` |
| Password | `Admin@123` |

> ⚠️ **Change the admin password** after your first login in production.

---

## 📡 API Endpoints

### Cars

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/cars.php` | List all cars |
| `GET` | `/api/cars.php?id=1` | Get single car |
| `GET` | `/api/cars.php?search=tata` | Search cars |
| `GET` | `/api/cars.php?type=SUV` | Filter by type |
| `GET` | `/api/cars.php?fuel=Electric` | Filter by fuel |
| `GET` | `/api/cars.php?sort=price_asc` | Sort results |
| `GET` | `/api/cars.php?featured=1` | Featured cars only |
| `POST` | `/api/cars.php` | Create listing *(login required)* |
| `PUT` | `/api/cars.php?id=1` | Update car *(owner/admin)* |
| `DELETE` | `/api/cars.php?id=1` | Delete car *(admin only)* |

### Auth

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/auth.php?action=register` | Register new user |
| `POST` | `/api/auth.php?action=login` | Login |
| `POST` | `/api/auth.php?action=logout` | Logout |
| `GET` | `/api/auth.php?action=me` | Get session user |

### Favorites

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/favorites.php` | Get user favourites |
| `POST` | `/api/favorites.php` | Toggle favourite |

### Enquiries

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/enquiries.php` | Submit enquiry |
| `GET` | `/api/enquiries.php` | List enquiries *(admin)* |
| `PUT` | `/api/enquiries.php?id=1` | Update status *(admin)* |

---

## 🩹 Common Issues & Fixes

| Problem | Fix |
|---|---|
| Images not showing | Run `tools/download_images.php` |
| Admin login fails | Run `tools/fix_admin.php` |
| DB connection error | Check credentials in `config/db.php` |
| Blank car grid | Open browser console (F12) and check for errors |
| Forgot admin password | Run `tools/reset_admin.php` |

---

## 🌐 Deployment (Free Hosting)

To host AutoVerse online for free with PHP + MySQL support:

1. Sign up at [InfinityFree](https://infinityfree.com) or [000webhost](https://www.000webhost.com/)
2. Create a hosting account — you get a free subdomain
3. Go to **Control Panel → MySQL Databases** → create a new database
4. Open **phpMyAdmin** → import `database/schema_fixed.sql`
5. Update `config/db.php` with the new host, DB name, username, and password
6. Upload all project files via the **File Manager** or FTP
7. Visit your subdomain — AutoVerse is live! 🚀

---

## 🔮 Future Improvements

- [ ] JWT-based authentication (replace PHP sessions)
- [ ] Image upload via Cloudinary
- [ ] Razorpay EMI payment integration
- [ ] Car comparison feature
- [ ] Advanced admin analytics dashboard
- [ ] Email notifications for enquiries

---

## 👨‍💻 Author

**Sandxx999**  
GitHub: [@Sandxx999](https://github.com/Sandxx999)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<div align="center">
Made with ❤️ by Sandxx999
</div>
