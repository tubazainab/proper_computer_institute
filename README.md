# Proper Computer — PHP & MySQL Institute Management System & Public Website

A complete, modern, responsive, and functional institute website and management system built with **PHP (PDO)**, **MySQL**, **Bootstrap 5**, **Font Awesome**, and **Chart.js**.

Designed and styled based on real-world vocational institute ERP reference screenshots, fully rebranded as **Proper Computer**.

---

## 🌟 Key Features

### 1. Public Institute Website
- **Modern Responsive Portal**: Sleek hero section, statistics, 7 domain categories, trending courses, "Why Choose Us", student testimonials, and inquiry call-to-actions.
- **Dynamic 65+ Course Catalogue**: Every course from all 7 domains (Programming & Web, Data & AI, Cybersecurity & Cloud, Design & Video, Digital Marketing, Finance & Management, Blockchain). Real-time search and category filtering without page reload.
- **Detailed Course Pages**: Complete module-by-module syllabus breakdown, duration, level, mode, and direct enrollment button.
- **Online Enquiry & Admission Form**: Prospective students can submit inquiries online with immediate validation and database storage.
- **Student Portal**: Students log in using their 10-digit mobile number or Admission ID to view enrolled courses, batch schedules, fees paid, balance due, active notices, and print official fee receipts.

### 2. Admin Management System (Disha Reference Design Rebranded)
- **Interactive Dashboard**:
  - Top Monthly Status Card with live counts for Total Admission, Total Collection, Total Billing, and Total Enquiries.
  - Daily Status Card with daily date picker.
  - Quick Links with soft pastel badge backgrounds (Admission, Enquiry, Fees, Add Expenses, Task/Target, Student Reports, Enquiry Reports, Notices, Attendance, Print Receipt).
  - Annual admission trend bar charts & domain distribution doughnut charts powered by Chart.js.
  - Recent enquiries, notice board, and pending fee collection tables.
- **Course Catalogue Management**: Add, edit, toggle active status, adjust fees, duration, and curriculum outlines.
- **Enquiry Management**: Track Walk-in, Telephonic, and Online leads. Tab filters for All Enquiry, FollowUps, Visits, and Reminders. Detailed call logs and next follow-up dates.
- **Atomic Enquiry-to-Admission Conversion**: Converts prospective inquiries into admissions within a single database transaction, reusing existing student records to prevent duplicates and generating initial fee receipts.
- **Admissions Registry**: Filter by Ongoing, Completed, Cancelled, and All Admissions. Form number, teacher, course, batch time, track, net fees, paid fees, due balance, and date.
- **Fee Collections & Printable Receipts**: Record installment payments, print high-resolution official Proper Computer receipts with watermark, breakdown, amount in words, and authorized signature blocks.
- **Employee Management**: Profile cards with skill tags and status indicators, daily attendance marking, attendance approval, leave applications and approvals, and monthly salary generator with printable payslips.
- **Master Modules**: Monthly performance targets (admissions, billing, collection), monthly budget planning, expense vouchers, and student notices.
- **Audited Reports**: DMR (Daily Management Report), Target vs Achievements Report, Course-wise Admissions, and Pending Fees with instant CSV export.

---

## 🚀 Setup Instructions for Windows & XAMPP

### Step 1: Copy Project to XAMPP
Copy the entire `Proper Computer` folder into your XAMPP web root:
```
C:\xampp\htdocs\Proper Computer
```

### Step 2: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 3: Run the 1-Click Database Installer
Open your browser and navigate to:
```
http://localhost/Proper%20Computer/install.php
```
- Click **"Run 1-Click Database Setup & Seeder"**.
- This automatically creates the `proper_computer_db` database, generates all 21 tables, seeds all 65+ courses, sample students, staff members, admissions, and financial records.

*Alternative Manual Setup:*
- Open `http://localhost/phpmyadmin`
- Create database `proper_computer_db`
- Import `database.sql`

---

## 🔑 Default Login Credentials

### Administrator / Staff Portal
- **URL**: `http://localhost/Proper%20Computer/login.php`
- **Username**: `admin`
- **Password**: `admin123`

### Student Portal
- **URL**: `http://localhost/Proper%20Computer/student-login.php`
- **Mobile Number**: `7020031922` or `8379066454`
- *(Or use Admission ID e.g. `PC-ADM-2026-0001`)*

---

## 📁 Directory Structure

```
Proper Computer/
├── config/
│   ├── config.php            # App constants, paths, session security, timezone
│   ├── db.php                # PDO connection with auto-reconnect & installer prompt
│   └── helpers.php           # CSRF, auth guards, XSS escaping, flash messages, formatting
├── assets/
│   ├── css/
│   │   ├── style.css         # Public website modern CSS design system
│   │   ├── admin.css         # Admin dashboard styles (faithful to reference design)
│   │   └── print.css         # High-resolution print styles for receipts and payslips
│   ├── js/
│   │   ├── main.js           # Public live search & category filtering
│   │   └── admin.js          # Table live filters, CSV export, delete confirmations
│   └── images/
│       └── logo.svg          # Proper Computer official SVG logo
├── includes/
│   ├── header.php            # Public header with top bar and navigation
│   ├── footer.php            # Public footer with domain links and contact
│   ├── admin_header.php      # Admin topbar with welcome greeting and profile
│   ├── admin_sidebar.php     # Admin sidebar navigation matching reference layout
│   └── admin_footer.php      # Admin footer & scripts
├── admin/
│   ├── index.php             # Dashboard (Monthly/Daily status, Quick Links, Chart.js)
│   ├── courses.php           # Course catalogue management
│   ├── course-add.php        # Add course
│   ├── course-edit.php       # Edit course (fees, duration, syllabus, mode)
│   ├── enquiries.php         # Enquiries list with status badges & filter tabs
│   ├── enquiry-add.php       # Add enquiry form
│   ├── enquiry-followup.php  # Follow-up notes & history
│   ├── enquiry-convert.php   # Convert enquiry into admission (atomic transaction)
│   ├── admissions.php        # Admissions registry (Ongoing, Completed, Cancelled)
│   ├── admission-add.php     # New direct admission
│   ├── admission-view.php    # Student admission ledger & fee status
│   ├── fees.php              # Fee collection & payment tracking
│   ├── fee-collect.php       # Fee collection form
│   ├── receipts.php          # Receipts list & filtering
│   ├── receipt-print.php     # Printable fee receipt (Proper Computer branded)
│   ├── employees.php         # Employee profiles (Grid view with skill tags)
│   ├── employee-add.php      # Add/edit employee profile
│   ├── attendance.php        # Daily attendance marking
│   ├── attendance-approval.php # Attendance approval workflow
│   ├── leaves.php            # Leave applications & approval
│   ├── salaries.php          # Salary calculations & monthly generation
│   ├── payslip-print.php     # Printable employee payslip
│   ├── targets.php           # Monthly target setup & tracking
│   ├── budget.php            # Monthly budget & expense tracking
│   ├── notices.php           # Student notices management
│   ├── reports.php           # Unified reporting center (DMR, Target vs Ach, Collections)
│   ├── users.php             # System users & roles
│   └── settings.php          # Institute & website settings
├── index.php                 # Public Homepage
├── about.php                 # About Us page
├── courses.php               # Public Course Catalogue (dynamic search & category filter)
├── course-details.php        # Single course page with full syllabus breakdown
├── contact.php               # Contact Us page
├── enquiry.php               # Public online enquiry & admission request
├── student-login.php         # Student portal login
├── student-portal.php        # Student personal dashboard (courses, receipts, dues)
├── login.php                 # Staff & Admin Login
├── logout.php                # Secure logout
├── install.php               # 1-Click web installer & seeder for XAMPP
├── database.sql              # Complete MySQL schema and seed data
└── README.md                 # Documentation
```

---

## 🔒 Security Implementations
- **SQL Injection Prevention**: All queries execute via PDO prepared statements with parameterized inputs.
- **Cross-Site Scripting (XSS)**: Output sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Cross-Site Request Forgery (CSRF)**: Cryptographic tokens validated on every POST request via `verify_csrf()`.
- **Password Security**: Passwords encrypted using standard `password_hash` with BCRYPT.
- **Session Protection**: HttpOnly cookies, strict session start guards, and role-based access validation.
