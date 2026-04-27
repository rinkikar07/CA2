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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Animations -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <style>
        :root {
            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="guest">
    <div class="scroll-progress" id="scrollProgress"></div>

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

    <!-- Hero Section -->
    <section class="hero-revamp">
        <div class="hero-container">
            <div class="hero-text">
                <div class="hero-badge" data-aos="fade-down" style="background: rgba(255, 112, 150, 0.1); color: var(--color-primary); padding: 8px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; display: inline-block; margin-bottom: 24px;">
                    <i class="fa-solid fa-sparkles"></i> AI-Powered Period Companion
                </div>
                <h1 data-aos="fade-right" data-aos-delay="100">
                    Your <span class="gradient-text">Intelligent Mate</span><br>Through Every Rhythm
                </h1>
                <p data-aos="fade-right" data-aos-delay="200">
                    HIM understands your emotions, predicts your cycle, and provides comfort when you need it most. More than a tracker — your empathetic digital ally.
                </p>
                <div class="hero-buttons" data-aos="fade-up" data-aos-delay="300" style="display:flex; gap:16px; justify-content: flex-start;">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        Get Started Free
                    </a>
                    <a href="#features" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-play" style="font-size:12px;"></i> See Features
                    </a>
                </div>
                
                <div class="hero-stats" data-aos="fade-up" data-aos-delay="400" style="margin-top: 60px; display:flex; gap:40px; justify-content: flex-start;">
                    <div class="hero-stat">
                        <div class="number" style="font-size: 32px; font-weight: 800; color: var(--color-primary);">4</div>
                        <div class="label" style="font-size: 14px; color: var(--text-muted);">Cycle Phases</div>
                    </div>
                    <div class="hero-stat">
                        <div class="number" style="font-size: 32px; font-weight: 800; color: var(--color-secondary);">AI</div>
                        <div class="label" style="font-size: 14px; color: var(--text-muted);">Empathy Engine</div>
                    </div>
                    <div class="hero-stat">
                        <div class="number" style="font-size: 32px; font-weight: 800; color: var(--color-sage);">24/7</div>
                        <div class="label" style="font-size: 14px; color: var(--text-muted);">Support</div>
                    </div>
                </div>

                <div class="hero-context-card" data-aos="fade-up" data-aos-delay="500" style="margin-top: 40px; background: white; padding: 20px; border-radius: 24px; border: 1px solid var(--border-light); display: flex; align-items: center; gap: 20px; max-width: 440px; box-shadow: var(--shadow-md); position:relative; z-index:10;">
                    <div class="ctx-icon" style="width: 50px; height: 50px; border-radius: 16px; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div class="ctx-info">
                        <h4 style="font-size: 14px; margin-bottom: 4px; color: var(--text-primary); font-weight:800; text-align:left;">Phase Insight</h4>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height:1.4; text-align:left;">Your energy levels are highest during the <strong style="color:var(--color-sage);">Follicular Phase</strong>. Perfect for new goals!</p>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-glow-circle"></div>
                
                <!-- Visible Orbit Rings -->
                <div class="orbit-ring ring-1"></div>
                <div class="orbit-ring ring-2"></div>
                <div class="orbit-ring ring-3"></div>

                <div class="orbit-container">
                    <!-- Floating Cards Orbiting -->
                    <div class="floating-card fc-1" data-speed="2">
                        <div class="fc-icon" style="background:#E8FFEF; color:#2E7D32;"><i class="fa-solid fa-heart-pulse"></i></div>
                        <div class="fc-text">Phase Tracking</div>
                    </div>
                    <div class="floating-card fc-2" data-speed="-3">
                        <div class="fc-icon" style="background:#FFF0F3; color:#E8567F;"><i class="fa-solid fa-face-smile-wink"></i></div>
                        <div class="fc-text">Mood Insights</div>
                    </div>
                    <div class="floating-card fc-3" data-speed="4">
                        <div class="fc-icon" style="background:#F3EEFF; color:#9B8EC0;"><i class="fa-solid fa-microchip-ai"></i></div>
                        <div class="fc-text">HIM AI Chat</div>
                    </div>
                    <div class="floating-card fc-4" data-speed="-2">
                        <div class="fc-icon" style="background:#EBF3FB; color:#1976D2;"><i class="fa-solid fa-microphone"></i></div>
                        <div class="fc-text">Voice Support</div>
                    </div>
                    <div class="floating-card fc-5" data-speed="3">
                        <div class="fc-icon" style="background:#FFF5EB; color:#F57C00;"><i class="fa-solid fa-star"></i></div>
                        <div class="fc-text">Wellness Badges</div>
                    </div>
                </div>
                
                <img src="wellness_hero_illustration_1777103912042.png" alt="Wellness Illustration" class="main-hero-img" style="max-width: 450px;">

                <!-- Decorative Elements -->
                <div class="deco-element de-1"><i class="fa-solid fa-heart"></i></div>
                <div class="deco-element de-2"><i class="fa-solid fa-sparkle"></i></div>
                <div class="deco-element de-3"><i class="fa-solid fa-circle"></i></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="glass-features" id="features">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span style="color:var(--color-primary); font-weight:800; font-size:14px; letter-spacing:2px; text-transform:uppercase;">Core Capabilities</span>
                <h2 style="font-size:48px; margin-top:12px;">The Future of Wellness</h2>
                <p>Designed to be as dynamic as your cycle</p>
            </div>
            
            <div class="feature-grid-revamp">
                <div class="feature-card-premium" data-aos="fade-up" data-aos-delay="0">
                    <div class="feature-icon-wrapper" style="background:var(--color-primary-light); color:var(--color-primary);">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3>Smart Cycle Engine</h3>
                    <p>Advanced predictions that learn from your unique body patterns. More accuracy with every cycle.</p>
                </div>
                
                <div class="feature-card-premium" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon-wrapper" style="background:var(--color-secondary-light); color:var(--color-secondary);">
                        <i class="fa-solid fa-message-heart"></i>
                    </div>
                    <h3>Empathetic AI Chat</h3>
                    <p>Your companion HIM understands your phase and mood, offering personalized comfort and science-backed advice.</p>
                    <div style="margin-top:20px; overflow:hidden; border-radius:16px;">
                        <img src="ai_chat_visualization_1777103933063.png" alt="AI Chat" style="width:100%; height:auto; opacity:0.8; transition:transform 0.5s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </div>
                </div>
                
                <div class="feature-card-premium" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon-wrapper" style="background:var(--color-mint); color:var(--color-sage);">
                        <i class="fa-solid fa-microphone-lines"></i>
                    </div>
                    <h3>Voice Companion</h3>
                    <p>Natural, soothing voice interaction powered by ElevenLabs for hands-free support during those tough days.</p>
                </div>
                
                <div class="feature-card-premium" data-aos="fade-up" data-aos-delay="0">
                    <div class="feature-icon-wrapper" style="background:#FFF5EB; color:var(--color-warning);">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <h3>Wellness Library</h3>
                    <p>Curated articles and audiobooks tailored to your current phase, from nutrition to mindfulness.</p>
                </div>
                
                <div class="feature-card-premium" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon-wrapper" style="background:var(--color-primary-light); color:var(--color-primary);">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h3>Gamified Health</h3>
                    <p>Earn badges, unlock achievements, and build streaks as you prioritize your self-care journey.</p>
                </div>
                
                <div class="feature-card-premium" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon-wrapper" style="background:var(--color-secondary-light); color:var(--color-secondary);">
                        <i class="fa-solid fa-chart-mixed"></i>
                    </div>
                    <h3>Deep Analytics</h3>
                    <p>Visualize your health trends over months to identify patterns and maintain optimal wellness.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why HIM (USPs) -->
    <section class="usps-section" style="padding: 100px 0; background: #fff;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 style="font-size:42px;">Why Choose HIM?</h2>
                <p>The HIM difference is in the details</p>
            </div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; width:100%; margin-top:60px;">
                <div class="card" data-aos="zoom-in" data-aos-delay="0">
                    <i class="fa-solid fa-shield-heart" style="font-size:32px; color:var(--color-primary); margin-bottom:20px;"></i>
                    <h4 style="margin-bottom:12px;">Privacy by Design</h4>
                    <p style="font-size:14px; color:var(--text-secondary);">Your data is encrypted and strictly private. We never sell your health information.</p>
                </div>
                <div class="card" data-aos="zoom-in" data-aos-delay="100">
                    <i class="fa-solid fa-brain" style="font-size:32px; color:var(--color-secondary); margin-bottom:20px;"></i>
                    <h4 style="margin-bottom:12px;">AI-First Approach</h4>
                    <p style="font-size:14px; color:var(--text-secondary);">Using Llama 3.1 to provide the most empathetic and context-aware advice.</p>
                </div>
                <div class="card" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fa-solid fa-wand-magic-sparkles" style="font-size:32px; color:var(--color-sage); margin-bottom:20px;"></i>
                    <h4 style="margin-bottom:12px;">Premium Experience</h4>
                    <p style="font-size:14px; color:var(--text-secondary);">A beautiful, calming interface designed to reduce stress and improve mental well-being.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Future Vision Section -->
    <section class="future-section" style="padding: 120px 0; background: linear-gradient(180deg, #fff, #F3EEFF);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span style="color:var(--color-secondary); font-weight:800; font-size:14px; letter-spacing:2px; text-transform:uppercase;">Coming Soon</span>
                <h2 style="font-size:42px; margin-top:12px;">The Future of HIM</h2>
                <p>Expanding the horizon of women's digital health</p>
            </div>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:32px; width:100%; margin-top:60px;">
                <div class="feature-card-premium" style="opacity:0.8; border-style:dashed;" data-aos="fade-up">
                    <div class="feature-icon-wrapper" style="background:#F0F7FF; color:#007AFF;">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <h3>Partner Sharing Mode</h3>
                    <p>Securely sync your cycle with a partner to improve communication and support during different phases.</p>
                </div>
                <div class="feature-card-premium" style="opacity:0.8; border-style:dashed;" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon-wrapper" style="background:#FFF0F0; color:#FF3B30;">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <h3>Smart Doctor Reports</h3>
                    <p>Generate professional health summaries and trend reports to share with your healthcare provider.</p>
                </div>
                <div class="feature-card-premium" style="opacity:0.8; border-style:dashed;" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon-wrapper" style="background:#F5F5F5; color:#333;">
                        <i class="fa-solid fa-masks-theater"></i>
                    </div>
                    <h3>Anonymous Community</h3>
                    <p>A safe, judgment-free space to discuss health, wellness, and share experiences with other women.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works" style="background: #FDF9FB; padding: 120px 0;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 style="font-size:42px;">Your Journey with HIM</h2>
                <p>Getting started is as simple as a heartbeat</p>
            </div>
            
            <div class="steps-container">
                <div class="step-item" data-aos="fade-up" data-aos-delay="0">
                    <div class="step-circle">1</div>
                    <h4>Personalize</h4>
                    <p>Create your secure account and share your basic cycle info.</p>
                </div>
                <div class="step-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-circle">2</div>
                    <h4>Log</h4>
                    <p>Track your daily mood, symptoms, and cycle dates easily.</p>
                </div>
                <div class="step-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-circle">3</div>
                    <h4>Engage</h4>
                    <p>Talk to HIM, explore the library, and earn badges for consistency.</p>
                </div>
                <div class="step-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-circle">4</div>
                    <h4>Thrive</h4>
                    <p>Gain insights and feel more in control of your health rhythm.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-revamp">
        <div class="container">
            <div class="cta-box" data-aos="zoom-in">
                <div class="cta-content">
                    <h2>Ready to meet your Mate?</h2>
                    <p>Join thousands of women who have transformed their cycle journey with HIM.</p>
                    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
                        <a href="register.php" class="btn btn-primary btn-lg" style="box-shadow: 0 10px 30px rgba(255, 112, 150, 0.4);">
                            Create Free Account
                        </a>
                        <a href="login.php" class="btn btn-white btn-lg" style="background:white; color:var(--text-primary);">
                            Login to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo"><span class="logo-icon" style="color:white;"><i class="fa-solid fa-heart"></i></span><span class="logo-text" style="color:white !important; -webkit-text-fill-color:white !important;">HIM</span></div>
                    <p style="text-align:left;">Her Intelligent Mate — Empowering women through AI-driven wellness and empathetic technology.</p>
                </div>
                <div class="footer-links">
                    <h4>Explore</h4>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="register.php">Get Started</a>
                </div>
                <div class="footer-links">
                    <h4>Legal</h4>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Contact Support</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> HIM - Her Intelligent Mate. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({
                duration: 1000,
                once: true,
                offset: 100
            });

            // Mouse Parallax for Floating Cards
            document.addEventListener('mousemove', (e) => {
                const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
                const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
                
                document.querySelectorAll('.floating-card').forEach(card => {
                    const speed = card.getAttribute('data-speed') || 1;
                    card.style.transform = `translate(${moveX * speed}px, ${moveY * speed}px)`;
                });
                
                document.querySelectorAll('.deco-element').forEach(el => {
                    const speed = 2;
                    el.style.transform = `translate(${moveX * speed}px, ${moveY * speed}px) rotate(${moveX * 10}deg)`;
                });
            });

            // Scroll Progress Bar
            window.addEventListener('scroll', () => {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                document.getElementById("scrollProgress").style.width = scrolled + "%";
            });

            // Header transparency on scroll
            const header = document.getElementById('header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.style.background = 'rgba(255, 255, 255, 0.9)';
                    header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.05)';
                } else {
                    header.style.background = 'rgba(255, 255, 255, 0.95)';
                    header.style.boxShadow = 'none';
                }
            });
        });

        // Mobile Menu Toggle
        const menuBtn = document.getElementById('mobileMenuBtn');
        const menuClose = document.getElementById('mobileMenuClose');
        const menuOverlay = document.getElementById('mobileMenuOverlay');

        menuBtn.addEventListener('click', () => menuOverlay.classList.add('show'));
        menuClose.addEventListener('click', () => menuOverlay.classList.remove('show'));
        menuOverlay.addEventListener('click', (e) => {
            if (e.target === menuOverlay) menuOverlay.classList.remove('show');
        });
    </script>
</body>
</html>

