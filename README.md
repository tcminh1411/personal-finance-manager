# Personal Finance Manager

A full-stack personal finance web application to track income and expenses, visualize spending, and manage transactions in real time.

Live demo: https://personalfinance.lovestoblog.com/  
Demo login: `admin` / `123456` or `minh` / `141103`

---

## 📸 Screenshot

![Dashboard](assets/images/demo/fullpage.png)

---

## Features

**Transaction Management**

- Create, read, update, delete with AJAX (no page reload)
- Smart category filtering based on transaction type (income/expense)
- Real-time validation on both frontend and backend
- Date defaults to today and blocks future dates

**Filtering and Search**

- Filter by type, category, date range, or keyword
- Quick filters: Today, This Week, This Month
- Debounced keyword search (500ms)
- Sortable columns: date, amount, category, description

**Dashboard and Charts**

- Chart.js shows a pie chart (spending by category) and a bar chart (monthly trend)
- Charts update live after every transaction change
- Summary cards: balance, total income, total expense

**User Experience**

- Responsive design with Tailwind CSS (works on screens from 360px to 1400px+)
- Sticky header that hides and shows on scroll
- One-click demo login button
- Loading indicators, toast notifications, and confirmation dialogs

**Data Export**

- Export transactions to CSV (respects active filters)
- UTF-8 BOM included for Excel compatibility

**Security**

- Bcrypt password hashing
- PDO prepared statements (prevents SQL injection)
- XSS sanitisation and CSRF session checks
- User data isolated by user_id
- Session timeout

---

## Technology Stack

- Backend: PHP 8.0+, MySQL 5.7+, PDO
- Frontend: Vanilla JavaScript (ES6+), Tailwind CSS
- Charts: Chart.js 4.4.0
- Authentication: bcrypt, PHP sessions
- Hosting: InfinityFree

The application uses 7 REST-like API endpoints (under /api/), 14 JavaScript modules, 3 SQL migration files, and semantic data-action attributes for JavaScript hooks.

---

## Project Structure

```
personal-finance-manager/
├── api/
│   ├── analytics/summary.php       # Chart & summary data
│   ├── categories/list.php
│   └── transactions/               # save, update, delete, filter, export
├── assets/
│   ├── css/                        # Tailwind CSS v4 entry + utilities
│   ├── images/demo/
│   └── js/modules/
│       ├── filter/                 # 5-file filter system
│       ├── chart-handler.js
│       ├── form-handler.js
│       ├── sticky-header.js
│       └── ...
├── auth/                           # login, logout, register processes
├── config/database.php
├── includes/                       # header, footer, helpers
├── migrations/
│   ├── 001_init.sql
│   ├── main.sql
├── index.php
├── login.php
└── register.php
```

---

## Local Setup

Requirements: XAMPP or Laragon with PHP 8.0+ and MySQL 5.7+

1. Clone the repository:  
   `git clone https://github.com/tcminh1411/personal-finance-manager.git`

2. Move the folder into your web server's document root (e.g. htdocs for XAMPP)

3. Create a database in phpMyAdmin:  
   Name: `finance_db` | Collation: `utf8mb4_unicode_ci`

4. Import the migration files in order (found in the migrations/ folder):
   - `main.sql`

5. (Optional) Update database credentials in `config/database.php`

6. Open your browser and visit:  
   `http://localhost/personal-finance-manager`

---

## Quick Usage Guide

| Action               | How to do it                                            |
| -------------------- | ------------------------------------------------------- |
| Add transaction      | Fill the form, choose type and category, then submit    |
| Filter transactions  | Use search box, dropdowns, or date range → click Filter |
| Sort                 | Click any column header                                 |
| Edit a transaction   | Click Edit → modify → update                            |
| Delete a transaction | Click Delete → confirm                                  |
| Export to CSV        | (Optional: apply filters) → click Export CSV            |
| View spending charts | Scroll down to the Spending Analysis section            |

---

## License

MIT License – (c) 2026 Thai Cao Minh  
Free to use, modify, and distribute.

---

## Contact

Thai Cao Minh – tcminh1411@gmail.com  
GitHub: https://github.com/tcminh1411
