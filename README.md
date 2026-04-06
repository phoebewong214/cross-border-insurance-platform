# 🛡️ Cross-Border Private Car Insurance Platform
> **Undergraduate Capstone Project** · University of Macau · ISOM4007 · 2025

A full-stack web platform that **digitalizes the Guangdong–Hong Kong–Macao Greater Bay Area cross-border car insurance process** — reducing application processing time from 7–10 days to 1–2 days.

---

## 📌 Problem Statement

Cross-border private car insurance in the GBA relies entirely on paper-based workflows. Car owners must fill out redundant forms, submit physical documents across multiple departments, and wait weeks with no visibility into their application status. Data silos between insurance companies, traffic authorities, and users make the process slow, error-prone, and frustrating.

**Key pain points identified:**
- Repeated manual data entry across departments
- No real-time application tracking
- 7–10 day approval cycle
- High document error rates due to manual handling
- Zero system integration between stakeholders

---

## 💡 Solution

A one-stop digital insurance platform connecting **users, insurance companies, and management departments** with end-to-end automation — from registration to policy delivery.

### Target Metrics Achieved (Projected)
| Metric | Before | After |
|---|---|---|
| Processing Time | 7–10 days | **1–2 days** |
| Document Error Rate | High | **< 5%** |
| User Satisfaction | — | **> 85%** |

---

## ✨ Key Features

### 👤 User Portal
- **Product Selection** — Browse and compare cross-border insurance plans
- **Online Application** — Streamlined proposal & quotation flow
- **Vehicle Management** — Add/manage vehicles with document uploads
- **Policy Management** — View active policies, renewal reminders
- **Order Management** — Track payment history and order status
- **Online Payment** — Credit card, WeChat Pay, and Alipay
- **Claims Guide** — Step-by-step claims process with required materials
- **Digital Policy Generation** — Auto-generated PDF insurance documents

### 🔧 Admin Panel
- **Application Approval** — Review and approve/reject policy applications
- **User Management** — Manage customer accounts and status
- **Data Analytics Dashboard** — Party analysis, policy analysis, product analysis
- **Policy Cancellation & Renewal Processing**

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────┐
│                  Frontend                    │
│     Bootstrap 5 · Font Awesome · Custom CSS  │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│               PHP Backend                    │
│  Session Auth · PDO · PHPMailer · FPDI/FPDF  │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│              MySQL Database                  │
│   Users · Vehicles · Policies · Orders       │
└─────────────────────────────────────────────┘
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript |
| Backend | PHP 8, PDO (prepared statements) |
| Database | MySQL 8 |
| PDF Generation | FPDF + FPDI (policy documents) |
| Email | PHPMailer (notifications) |
| Deployment | AWS EC2 / Alibaba Cloud ECS |

---

## 📂 Project Structure

```
ISOM4007_V2.1.0/
├── index.php              # Landing page
├── home.php               # User dashboard
├── login.php              # Authentication
├── register.php           # User registration
├── admin.php              # Admin dashboard
├── pages/                 # Feature modules
│   ├── product.php        # Insurance product browsing
│   ├── proposal.php       # Application flow
│   ├── quotation.php      # Price calculation
│   ├── payment.php        # Payment processing
│   ├── policy_management.php
│   ├── order_management.php
│   ├── claim_guide.php
│   ├── DataAnalysisCatalog.php
│   ├── party_analysis.php
│   ├── policy_analysis.php
│   └── product_analysis.php
├── config/
│   ├── database.php       # DB connection
│   └── functions.php      # Shared utilities
├── css/style.css
├── assets/                # Images, icons
└── insurance_system.sql   # Database schema
```

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.0+
- MySQL 8.0+
- Apache / Nginx web server

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/cross-border-insurance-platform.git

# 2. Move project files to your web server root
cp -r ISOM4007_V2.1.0/* /var/www/html/

# 3. Import the database
mysql -u root -p < insurance_system.sql

# 4. Configure database connection
# Edit config/database.php with your DB credentials

# 5. Visit your server IP in the browser
http://localhost/
```

---

## 🎨 Design Highlights

- **Figma-prototyped** UI following Norman's design principles
- Modularized workflow reducing processing time by **70%** (projected)
- Mobile-responsive layout for on-the-go access
- Consistent design system with custom CSS variables

---

## 📊 Product Thinking

This project was built with a PM-first approach:

1. **Discovery** — Competitive analysis of existing insurance portals in HK/Macau; user interviews identifying friction points
2. **Definition** — Mapped 6 core functional modules from user journey analysis
3. **Design** — 10+ high-fidelity Figma prototypes before development
4. **Delivery** — Iterative development with version-controlled releases (V1.0 → V2.1.0)
5. **Impact** — Measurable KPIs tracked: processing time, error rate, user satisfaction

---

## 🔍 AI & Fintech Innovation

- Explored **AI-powered underwriting** mechanisms for automated risk assessment
- Designed **document recognition** pipeline for uploaded vehicle documents
- Compliance-aware architecture suitable for HK/Macau financial regulations

---

## 👥 Team

Developed as part of **ISOM4007** at the University of Macau (2025)

---

## 📄 Documentation

- [User Manual](./User%20Manual%202.docx)
- [Final Report](./Final%20Report%202.docx)
- [002-Group5-Digitizing Cross-boundary Private Car Insurance.docx](./002-Group5-Digitizing%20Cross-boundary%20Private%20Car%20Insurance.docx)
---

