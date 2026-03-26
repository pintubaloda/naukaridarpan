# Naukaridarpan — India's Competitive Exam Mock Test Marketplace

> A full-stack Laravel 11 platform for buying and selling mock tests for UPSC, SSC, Banking, Railway, State PSC and all competitive exams in India.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 + Laravel 11 |
| Exam Engine | TAO Testing (open-source, self-hosted, QTI 2.2) |
| Frontend | Blade + Alpine.js + Custom CSS (Saffron/Teal theme) |
| Database | MySQL 8 + Redis (cache/sessions/queues) |
| File Storage | AWS S3 / Cloudflare R2 |
| Payment | Razorpay (UPI, Card, Netbanking, Wallet) |
| AI | Claude API (paper parsing, blog generation) |
| Scraper | Node.js + Playwright (headless Chrome) |
| Email | Mailgun / Amazon SES |
| Search | Meilisearch (optional) |

---

## Project Structure

```
naukaridarpan/
├── app/
│   ├── Console/Commands/          # Artisan CLI commands
│   │   ├── GenerateBlogPostCommand.php   # php artisan blog:generate
│   │   ├── ScrapePapersCommand.php       # php artisan scrape:papers
│   │   └── ProcessSettlementsCommand.php # php artisan settlements:process
│   ├── Http/Controllers/
│   │   ├── Auth/AuthController.php
│   │   ├── MarketplaceController.php
│   │   ├── Student/{StudentController,ExamController}.php
│   │   ├── Seller/{SellerController,PaperController,PayoutController}.php
│   │   ├── Admin/{AdminController,ExamApprovalController,KYCController,BlogAdminController}.php
│   │   └── Blog/BlogController.php
│   ├── Http/Middleware/RoleMiddleware.php
│   ├── Jobs/ParseExamPaperJob.php         # Queue job for AI parsing
│   ├── Models/                            # All Eloquent models
│   └── Services/
│       ├── AI/PaperParserService.php      # Claude API - PDF/text → QTI
│       ├── AI/BlogGeneratorService.php    # Claude API - auto blog posts
│       ├── Payment/RazorpayService.php    # Razorpay + settlement logic
│       ├── Scraper/ScraperService.php     # PYQ + professor lead scraping
│       └── TAO/TaoService.php             # TAO platform QTI integration
├── database/
│   ├── migrations/2024_01_00_create_all_tables.php
│   └── seeders/DatabaseSeeder.php
├── resources/views/
│   ├── layouts/app.blade.php              # Master layout
│   ├── home.blade.php                     # Landing page
│   ├── exam-detail.blade.php              # Exam purchase page
│   ├── auth/{login,register,register-seller}.blade.php
│   ├── student/{dashboard,my-exams,results,checkout}.blade.php
│   ├── seller/{dashboard,papers/create,kyc,payouts,earnings}.blade.php
│   ├── admin/{dashboard,exams-pending,kyc-pending,payouts,blog/}.blade.php
│   ├── exam/{take,result,start}.blade.php
│   └── blog/{index,show}.blade.php
├── public/css/app.css                     # Complete design system
├── routes/web.php                         # All routes
├── scripts/playwright/                    # Node.js scrapers
│   ├── scrape_papers.js
│   └── scrape_professors.js
└── config/services.php
```

---

## Setup Instructions

### 1. Install PHP dependencies
```bash
composer install
```

### 2. Environment configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and fill in:
- `DB_*` — MySQL connection
- `ANTHROPIC_API_KEY` — Get from https://console.anthropic.com
- `RAZORPAY_KEY_ID` + `RAZORPAY_KEY_SECRET` — From Razorpay dashboard
- `TAO_URL` — Your self-hosted TAO instance URL
- `AWS_*` — For S3 file storage (or use local disk for development)

### 3. Database setup
```bash
php artisan migrate
php artisan db:seed
```

Seeder creates:
- Admin: `admin@naukaridarpan.com` / `Admin@1234`
- Demo Seller: `seller@naukaridarpan.com` / `Seller@1234`
- Demo Student: `student@naukaridarpan.com` / `Student@1234`
- All 12 exam categories
- Platform default settings

### 4. Install Node.js scraper dependencies
```bash
cd scripts/playwright
npm install
npx playwright install chromium
```

### 5. Configure queue worker
```bash
# Start Redis (required for queues)
redis-server

# Start queue worker
php artisan queue:work --queue=default --tries=2
```

### 6. Set up cron (Linux/Ubuntu)
```
# Add to crontab: crontab -e
* * * * * cd /var/www/naukaridarpan && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks:
- `06:00` — AI blog post (English)
- `07:00` — AI blog post (Hindi)
- `Every hour` — Process 48-hr payment settlements
- `Sunday 02:00` — Scrape new PYQ papers
- `Monday 03:00` — Scrape professor leads

### 7. TAO Testing Platform Setup
1. Download TAO from https://www.taotesting.com/get-tao/
2. Install on a PHP server (Apache/Nginx)
3. Set `TAO_URL`, `TAO_USERNAME`, `TAO_PASSWORD` in `.env`
4. TAO handles all exam delivery — questions are pushed via QTI 2.2 XML

### 8. Start development server
```bash
php artisan serve
```

Visit `http://localhost:8000`

---

## Key Features

### For Students
- Browse exams by category, difficulty, language, price
- Secure Razorpay checkout (UPI/Card/Netbanking)
- TAO-powered exam with timer, anti-cheat, question navigator
- Supports MCQ, MSQ, OMR, Math (LaTeX), Fill-in-blank, Short/Long answer
- Instant result with answer-by-answer review and explanations
- Configurable retakes per purchase

### For Sellers (Professors)
- Upload PDF (any quality — scanned supported) or type questions
- Claude AI automatically extracts all questions, options, answers
- Set your price — platform adds 15% markup for student-facing price
- Public profile page with qualifications, ratings, all papers listed
- Analytics: daily sales chart, top papers, revenue breakdown
- KYC: PAN, Aadhaar, bank account verification
- Wallet: 48-hour settlement hold, then request payout anytime
- Minimum threshold enforced before payout (configurable)

### For Platform (Admin)
- Dashboard: sales, revenue, pending actions at a glance
- Exam approval queue: review, approve or reject with reason
- KYC verification: review documents, approve/reject
- Payout processing: mark paid with UTR number
- AI blog: generate daily posts, auto-publish, SEO-optimised
- Scraped papers: review and publish PYQ papers from govt sites
- Professor leads: CRM for outreach, send bulk onboarding emails
- Settings: commission rate, payout threshold, settlement hours

### AI Integration
- **Paper parsing**: Claude reads PDF → extracts all questions as structured JSON → converts to QTI 2.2 XML for TAO
- **Blog generation**: Daily auto-posts about Sarkari Naukri, results, admit cards, study tips
- **Scraper AI**: Converts scraped PDF papers to exam format

---

## Commission Structure

```
Student pays: ₹100
└── Platform commission (15%): ₹15
└── Seller credit: ₹85
    ├── Held 48 hours (refund window)
    └── Released to wallet after 48 hours
        └── Seller requests payout (min ₹500)
            └── Admin processes to bank account (KYC required)
```

---

## Android App

The Android app (Kotlin + Jetpack Compose) structure is documented separately.
Key screens: Browse Exams, Exam Detail, Secure Exam (WebView kiosk or native), Results.
The app connects to the same Laravel API backend.

To build the API layer, register routes in `routes/api.php` and use Laravel Sanctum for authentication.

---

## Support & Customisation

- Change commission rate: Admin → Settings → `default_commission`
- Change payout threshold: Admin → Settings → `min_payout_threshold`
- Change settlement hours: Admin → Settings → `settlement_hours`
- Add new category: Admin → Categories (or via Seeder)
- Change AI model: `.env` → `ANTHROPIC_MODEL`

---

## License

Proprietary — Naukaridarpan Technologies Pvt. Ltd. All rights reserved.
