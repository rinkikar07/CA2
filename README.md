# HIM - Her Intelligent Mate

HIM is a specialized PHP application designed for personalized support and intelligence.

## Features

- **Personalized Chat**: AI-powered conversational assistance.
- **Cycle Tracker**: Monitor and track wellness cycles.
- **Mood Journal**: Log and analyze emotional well-being.
- **Insights & Wellness**: Data-driven insights and health recommendations.

## Setup Instructions

1. **Prerequisites**: XAMPP (Apache & MySQL) and PHP 8.x.
2. **Database Setup**:
    - Import the SQL files from the `sql/` directory into your MySQL database (`him_db`).
3. **Environment Configuration**:
    - Rename `.env.example` to `.env`.
    - Update the database credentials and API keys as needed.
4. **Run Local Server**:
    - Place the project in `c:\xampp\htdocs\MCA PHP\CA2`.
    - Access via `http://localhost/MCA PHP/CA2/`.

## Deployment

Ensure the following are configured correctly on your production server:
- `.htaccess` for URL rewriting.
- Production environment variables in the project root.
- SMTP credentials for email functionality.

---
Created by [Your Name]
