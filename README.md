# 🛒 TechNest — E-Commerce Platform

A full-stack e-commerce web application built with **Laravel 12** and **Alpine.js**, featuring a complete shopping experience from product browsing to secure Stripe-powered checkout, with a dedicated admin dashboard for store management.

> Built as a portfolio project to demonstrate full-stack web development skills including payment integration, role-based access control, and responsive UI design.

---

## ✨ Key Features

### 🛍️ Customer Experience
- **Product Catalog** — Browse products by category, subcategory, and brand with dynamic filtering
- **Product Variants** — Support for multi-attribute variants (color, size, storage) with variant-specific images and pricing
- **Shopping Cart** — Real-time cart management with stock validation and quantity controls
- **Smart Checkout** — Cascading address dropdowns (State → City → Postcode) using Malaysian postal data
- **Secure Payments** — Stripe Elements integration with PCI-compliant tokenized card processing
- **Order Tracking** — Detailed order history with product images, variant attributes, and status tracking
- **Email Confirmation** — Automated order receipt emails via Laravel Markdown Mailables
- **User Authentication** — Registration, login, password reset, and profile management via Laravel Breeze

### 🔧 Admin Dashboard
- **Product Management** — Full CRUD with image uploads, variant creation, bulk discount pricing, and status toggles
- **Category & Brand Management** — Organize products with categories, subcategories, and brands (with logo uploads)
- **Order Management** — View complete order details (customer info, shipping address, purchased items with variant details) and update order status (pending → paid → shipped → delivered)

### 🛡️ Security & Data Integrity
- **Mass Assignment Protection** — All Eloquent models use explicit `$fillable` arrays
- **Database Transactions** — Checkout wrapped in DB transactions to prevent partial orders
- **Pessimistic Locking** — `lockForUpdate()` on stock during checkout to prevent overselling
- **Stripe Tokenization** — Card data never touches the server; processed directly by Stripe
- **CSRF Protection** — All forms and API calls protected with Laravel's CSRF tokens
- **Role-Based Access** — Admin middleware guards for dashboard routes

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12 (PHP 8.2) |
| **Frontend** | Alpine.js, Blade Templates, Tailwind CSS |
| **Database** | MySQL |
| **Payments** | Stripe Elements + Stripe PHP SDK |
| **Email** | Laravel Markdown Mailables (SMTP / Mailtrap) |
| **Address Data** | malaysia-postcodes npm package (CDN) |
| **Authentication** | Laravel Breeze |
| **Server** | XAMPP (Apache + MySQL) |

---

## 📁 Project Structure

```
technest/
├── app/
│   ├── Http/Controllers/       # API & web controllers
│   │   ├── Admin/              # Admin CRUD controllers
│   │   ├── Auth/               # Authentication controllers
│   │   ├── OrderController.php # Checkout & order management
│   │   └── ...
│   ├── Mail/                   # Mailable classes
│   ├── Models/                 # Eloquent models
│   └── Http/Middleware/        # Custom middleware (IsAdmin)
├── database/migrations/        # Database schema
├── resources/views/
│   ├── admin/                  # Admin dashboard views
│   ├── auth/                   # Login, register, password reset
│   ├── components/             # Reusable Blade components
│   ├── emails/                 # Email templates
│   ├── layouts/                # App & admin layouts
│   ├── checkout.blade.php      # Checkout with Stripe Elements
│   ├── home.blade.php          # Landing page
│   ├── shop.blade.php          # Product catalog
│   └── orders.blade.php       # Order history
├── routes/
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
└── ...
```

---

## ⚡ Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- MySQL
- Node.js & npm

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/SinJie00/TechNest.git
cd TechNest

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure your .env file
#    - Set DB_DATABASE, DB_USERNAME, DB_PASSWORD
#    - Set STRIPE_KEY and STRIPE_SECRET (from https://dashboard.stripe.com/apikeys)
#    - Set MAIL_* credentials (Mailtrap for testing)

# 6. Run migrations
php artisan migrate

# 7. Create storage symlink
php artisan storage:link

# 8. Build frontend assets
npm run build

# 9. Start the development server
php artisan serve
```

### Test Payment
Use Stripe's test card to simulate payments:
| Field | Value |
|-------|-------|
| Card Number | `4242 4242 4242 4242` |
| Expiry | Any future date (e.g., `12/30`) |
| CVC | Any 3 digits (e.g., `123`) |

---

## 🔑 Default Credentials

After running the migrations and seeders (`php artisan migrate --seed`), you can login with the following accounts:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@technest.com` | `admin123` |
| **Customer** | `customer@technest.com` | `cust123` |

---

## 📌 Architecture Highlights

### Checkout Flow
```
Customer fills form → Stripe tokenizes card on frontend → Token + order data sent to backend
→ DB transaction starts → Stock locked & validated → Stripe charge created → Payment recorded
→ Order status updated to "paid" → Cart cleared → Confirmation email sent → Success response
```

### Cascading Address Selection
```
State (dropdown) → City (auto-populated) → Postcode (auto-populated)
```
Powered by the `malaysia-postcodes` package, ensuring valid Malaysian address combinations.

### Variant System
Products support dynamic multi-attribute variants (e.g., Color + Storage Size), each with:
- Independent pricing & discount pricing
- Individual stock tracking
- Variant-specific image galleries
- SKU codes

---

## 🧑‍💻 Author

**Lim Sin Jie**  
GitHub: [@SinJie00](https://github.com/SinJie00)

---

## 📄 License

This project is open-sourced for portfolio and educational purposes.
