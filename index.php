<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HIM - Her Intelligent Mate. AI-powered period companion for emotional, physical, and mental wellness during your menstrual cycle.">
    <title>HIM - Her Intelligent Mate | Your AI Period Companion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        .hero {
            min-height: 100vh; display: flex; align-items: center;
            background: linear-gradient(180deg, #FEFBFC 0%, #FFF0F3 40%, #F3EEFF 100%);
            padding-top: var(--header-height); position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -150px; right: -150px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(232,86,127,0.08) 0%, transparent 70%);
        }
        .hero::after {
            content: ''; position: absolute; bottom: -100px; left: -100px;
            width: 400px; height: 400px; border-radius: 50%;
            background: radial-gradient(circle, rgba(155,142,192,0.08) 0%, transparent 70%);
        }
        .hero-content { position: relative; z-index: 1; text-align: center; max-width: 720px; margin: 0 auto; padding: 0 24px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(232,86,127,0.1); color: var(--color-primary);
            padding: 8px 20px; border-radius: 50px; font-size: 14px; font-weight: 600;
            margin-bottom: 24px;
        }
        .hero h1 { font-size: 56px; margin-bottom: 20px; line-height: 1.15; }
        .hero h1 .gradient-text {
            background: linear-gradient(135deg, #E8567F, #9B8EC0);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .hero p { font-size: 20px; color: var(--text-secondary); margin-bottom: 36px; line-height: 1.6; }
        .hero-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .hero-stats { display: flex; gap: 48px; justify-content: center; margin-top: 64px; }
        .hero-stat { text-align: center; }
        .hero-stat .number {
            font-family: var(--font-heading); font-size: 36px; font-weight: 800;
            background: linear-gradient(135deg, #E8567F, #9B8EC0);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .hero-stat .label { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

        /* Features Section */
        .features { padding: 96px 0; background: white; }
        .section-header { text-align: center; max-width: 600px; margin: 0 auto 64px; }
        .section-header p { color: var(--text-muted); margin-top: 12px; font-size: 18px; }
        .feature-card {
            background: var(--bg-body); border-radius: var(--border-radius-lg);
            padding: 32px; border: 1px solid var(--border-light);
            transition: var(--transition-normal); text-align: center;
        }
        .feature-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-glow); }
        .feature-icon {
            width: 72px; height: 72px; border-radius: 20px; margin: 0 auto 20px;
            display: flex; align-items: center; justify-content: center; font-size: 28px;
        }
        .feature-card h3 { margin-bottom: 12px; font-size: 20px; }
        .feature-card p { font-size: 15px; color: var(--text-secondary); line-height: 1.6; }

        /* How it works */
        .how-it-works { padding: 96px 0; background: linear-gradient(180deg, #FFF0F3 0%, #FEFBFC 100%); }
        .steps-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; margin-top: 64px; }
        .step-card { text-align: center; position: relative; }
        .step-number {
            width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 20px;
            background: linear-gradient(135deg, #E8567F, #C73E66);
            color: white; display: flex; align-items: center; justify-content: center;
            font-family: var(--font-heading); font-weight: 800; font-size: 22px;
        }
        .step-card h4 { margin-bottom: 8px; }
        .step-card p { font-size: 14px; color: var(--text-secondary); }

        /* CTA */
        .cta-section { padding: 96px 0; text-align: center; }
        .cta-card {
            background: linear-gradient(135deg, #E8567F, #9B8EC0);
            border-radius: 32px; padding: 64px; color: white; max-width: 800px; margin: 0 auto;
        }
        .cta-card h2 { color: white; margin-bottom: 16px; }
        .cta-card p { opacity: 0.9; margin-bottom: 32px; font-size: 18px; }
        .btn-white {
            background: white; color: var(--color-primary); padding: 16px 36px;
            border-radius: var(--border-radius-md); font-weight: 700; font-size: 16px;
            display: inline-flex; align-items: center; gap: 8px;
            transition: var(--transition-normal);
        }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }

        @media (max-width: 767px) {
            .hero h1 { font-size: 32px; }
            .hero p { font-size: 16px; }
            .hero-stats { gap: 24px; }
            .hero-stat .number { font-size: 28px; }
            .steps-grid { grid-template-columns: repeat(2, 1fr); }
            .cta-card { padding: 40px 24px; }
        }
        @media (max-width: 576px) {
            .steps-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="guest">
    <!-- Header -->
    <header class="header header-guest" id="header">
        <div class="header-inner">
            <a href="index.php" class="logo">
                <span class="logo-icon"><i class="fa-solid fa-heart"></i></span>
                <span class="logo-text">HIM</span>
            </a>
            <nav class="header-nav-guest">
                <a href="#features" class="nav-link-guest">Features</a>
                <a href="#how-it-works" class="nav-link-guest">How It Works</a>
                <a href="login.php" class="btn btn-ghost-nav">Login</a>
                <a href="register.php" class="btn btn-primary-sm">Get Started</a>
            </nav>
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
        </div>
    </header>

    <div class="mobile-menu-overlay" id="mobileMenuOverlay">
        <div class="mobile-menu-content">
            <button class="mobile-menu-close" id="mobileMenuClose"><i class="fa-solid fa-xmark"></i></button>
            <a href="#features">Features</a>
            <a href="#how-it-works">How It Works</a>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn btn-primary">Get Started</a>
        </div>
    </div>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge" data-aos="fade-down">
                AI-Powered Period Companion
            </div>
            <h1 data-aos="zoom-in-up" data-aos-delay="100">
                Your <span class="gradient-text">Intelligent Mate</span><br>During Every Phase
            </h1>
            <p data-aos="zoom-in-up" data-aos-delay="200">
                HIM understands your emotions, predicts your cycle, and provides comfort when you need it most. More than a tracker — your empathetic digital ally.
            </p>
            <div class="hero-buttons" data-aos="zoom-in-up" data-aos-delay="300">
                <a href="register.php" class="btn btn-primary btn-lg">
                    Get Started Free
                </a>
                <a href="#features" class="btn btn-secondary btn-lg">
                    <i class="fa-solid fa-arrow-down"></i> Learn More
                </a>
            </div>
            <div class="hero-stats" data-aos="zoom-in-up" data-aos-delay="400">
                <div class="hero-stat">
                    <div class="number">4</div>
                    <div class="label">Cycle Phases Covered</div>
                </div>
                <div class="hero-stat">
                    <div class="number">AI</div>
                    <div class="label">Empathetic Chat</div>
                </div>
                <div class="hero-stat">
                    <div class="number">24/7</div>
                    <div class="label">Voice Support</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header" data-aos="zoom-in-up">
                <h2>Everything You Need, In One Place</h2>
                <p>Holistic menstrual wellness that cares about your body AND your emotions</p>
            </div>
            <div class="grid grid-3">
                <div class="feature-card" data-aos="zoom-in-up" data-aos-delay="0">
                    <div class="feature-icon" style="background:var(--color-primary-light); color:var(--color-primary);">
                        <i class="fa-solid fa-calendar-heart"></i>
                    </div>
                    <h3>Smart Cycle Tracking</h3>
                    <p>Intelligent predictions that learn from your patterns. Track periods, symptoms, and moods with a beautiful calendar view.</p>
                </div>
                <div class="feature-card" data-aos="zoom-in-up" data-aos-delay="100">
                    <div class="feature-icon" style="background:var(--color-secondary-light); color:var(--color-secondary);">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3>AI Chat Companion</h3>
                    <p>An empathetic AI that understands your mood and cycle phase. Get comfort, motivation, or just someone to talk to.</p>
                </div>
                <div class="feature-card" data-aos="zoom-in-up" data-aos-delay="200">
                    <div class="feature-icon" style="background:var(--color-mint); color:var(--color-sage);">
                        <i class="fa-solid fa-microphone"></i>
                    </div>
                    <h3>Voice Assistant</h3>
                    <p>Hands-free comfort on painful days. Just speak and receive soothing responses, guided breathing, and audiobooks.</p>
                </div>
                <div class="feature-card" data-aos="zoom-in-up" data-aos-delay="0">
                    <div class="feature-icon" style="background:#FFF5EB; color:var(--color-warning);">
                        <i class="fa-solid fa-spa"></i>
                    </div>
                    <h3>Wellness Hub</h3>
                    <p>Phase-specific tips for nutrition, exercise, sleep, and mindfulness. Curated books and audiobooks for mental stimulation.</p>
                </div>
                <div class="feature-card" data-aos="zoom-in-up" data-aos-delay="100">
                    <div class="feature-icon" style="background:var(--color-primary-light); color:var(--color-primary);">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                    <h3>Gamified Wellness</h3>
                    <p>Earn badges, complete challenges, and build streaks. Make self-care fun and build healthy habits that last.</p>
                </div>
                <div class="feature-card" data-aos="zoom-in-up" data-aos-delay="200">
                    <div class="feature-icon" style="background:var(--color-secondary-light); color:var(--color-secondary);">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3>Health Insights</h3>
                    <p>Visual analytics of mood trends, symptom patterns, and cycle history. Early detection of irregularities and PCOS risk.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header" data-aos="zoom-in-up">
                <h2>How HIM Works</h2>
                <p>Simple, private, and always by your side</p>
            </div>
            <div class="steps-grid">
                <div class="step-card" data-aos="zoom-in-up" data-aos-delay="0">
                    <div class="step-number">1</div>
                    <h4>Create Account</h4>
                    <p>Sign up with your basic info and last period date. We keep everything private.</p>
                </div>
                <div class="step-card" data-aos="zoom-in-up" data-aos-delay="100">
                    <div class="step-number">2</div>
                    <h4>Log Your Cycle</h4>
                    <p>Track periods, symptoms, and moods. HIM learns your patterns for better predictions.</p>
                </div>
                <div class="step-card" data-aos="zoom-in-up" data-aos-delay="200">
                    <div class="step-number">3</div>
                    <h4>Chat & Get Support</h4>
                    <p>Talk to your AI companion anytime. Get empathetic, phase-aware responses.</p>
                </div>
                <div class="step-card" data-aos="zoom-in-up" data-aos-delay="300">
                    <div class="step-number">4</div>
                    <h4>Thrive & Grow</h4>
                    <p>Follow wellness tips, complete challenges, and watch your health insights improve.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card" data-aos="zoom-in">
                <h2>Ready to Feel Understood?</h2>
                <p>Join HIM today and experience period care that truly cares about you.</p>
                <a href="register.php" class="btn-white">
                    <i class="fa-solid fa-heart"></i> Get Started Free
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo"><span class="logo-icon"><i class="fa-solid fa-heart"></i></span><span class="logo-text">HIM</span></div>
                    <p>Her Intelligent Mate — Your AI-powered period companion for emotional, physical, and mental wellness.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="register.php">Get Started</a>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Use</a>
                    <a href="#">Contact</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> HIM - Her Intelligent Mate. Made for women everywhere.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
