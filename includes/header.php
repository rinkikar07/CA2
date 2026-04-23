<?php
/**
 * HIM - Shared Header Component
 */
if (!isset($pageTitle)) $pageTitle = 'HIM';
$isAuth = isLoggedIn();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$notifCount = $isAuth ? getUnreadNotificationCount(getCurrentUserId()) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HIM - Her Intelligent Mate. Your AI-powered period companion for emotional, physical, and mental wellness.">
    <!-- FORCE NO CACHE -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= sanitize($pageTitle) ?> | HIM - Her Intelligent Mate</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css">
    
    <!-- AOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?= time() ?>">
    
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>?v=<?= time() ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= $isAuth ? 'authenticated' : 'guest' ?>">

<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
    <i class="fa-solid fa-heart loader-heart"></i>
</div>

<!-- Skip to content -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<?php if ($isAuth): ?>
<!-- ===== AUTHENTICATED HEADER ===== -->
<header class="header" id="header">
    <div class="header-inner">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="dashboard.php" class="logo">
                <span class="logo-icon">💕</span>
                <span class="logo-text">HIM</span>
            </a>
        </div>
        
        <nav class="header-nav" aria-label="Main navigation">
            <a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> <span>Dashboard</span>
            </a>
            <a href="cycle_tracker.php" class="nav-link <?= $currentPage === 'cycle_tracker' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i> <span>Tracker</span>
            </a>
            <a href="chat.php" class="nav-link <?= $currentPage === 'chat' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> <span>Chat</span>
            </a>
            <a href="mood_journal.php" class="nav-link <?= $currentPage === 'mood_journal' ? 'active' : '' ?>">
                <i class="fa-solid fa-book"></i> <span>Journal</span>
            </a>
            <a href="wellness.php" class="nav-link <?= $currentPage === 'wellness' ? 'active' : '' ?>">
                <i class="fa-solid fa-spa"></i> <span>Wellness</span>
            </a>
            <a href="games.php" class="nav-link <?= $currentPage === 'games' ? 'active' : '' ?>">
                <i class="fa-solid fa-gamepad"></i> <span>Challenges</span>
            </a>
            <a href="insights.php" class="nav-link <?= $currentPage === 'insights' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i> <span>Insights</span>
            </a>
        </nav>
        
        <div class="header-right">
            <!-- Notifications -->
            <div class="notif-wrapper" id="notifWrapper">
                <button class="notif-btn" id="notifBtn" aria-label="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="notif-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <h4>Notifications</h4>
                        <a href="#" id="markAllRead">Mark all read</a>
                    </div>
                    <div class="notif-list" id="notifList">
                        <p class="notif-empty">No new notifications</p>
                    </div>
                </div>
            </div>
            
            <!-- Profile -->
            <div class="profile-wrapper" id="profileWrapper">
                <button class="profile-btn" id="profileBtn" aria-label="Profile menu">
                    <div class="profile-avatar">
                        <?= strtoupper(substr(getCurrentUserName(), 0, 1)) ?>
                    </div>
                    <span class="profile-name"><?= sanitize(getCurrentUserName()) ?></span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
                    <?php if (getCurrentUserRole() === 'admin'): ?>
                        <a href="admin/dashboard.php"><i class="fa-solid fa-shield-halved"></i> Admin</a>
                    <?php endif; ?>
                    <hr>
                    <a href="logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Bottom Nav -->
<nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
    <a href="dashboard.php" class="mobile-nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i><span>Home</span>
    </a>
    <a href="cycle_tracker.php" class="mobile-nav-item <?= $currentPage === 'cycle_tracker' ? 'active' : '' ?>">
        <i class="fa-solid fa-calendar-days"></i><span>Tracker</span>
    </a>
    <a href="chat.php" class="mobile-nav-item <?= $currentPage === 'chat' ? 'active' : '' ?>">
        <i class="fa-solid fa-comments"></i><span>Chat</span>
    </a>
    <a href="wellness.php" class="mobile-nav-item <?= $currentPage === 'wellness' ? 'active' : '' ?>">
        <i class="fa-solid fa-spa"></i><span>Wellness</span>
    </a>
    <a href="insights.php" class="mobile-nav-item <?= $currentPage === 'insights' ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i><span>Insights</span>
    </a>
</nav>

<main id="main-content" class="main-content">

<?php else: ?>
<!-- ===== GUEST HEADER ===== -->
<header class="header header-guest" id="header">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <span class="logo-icon">💕</span>
            <span class="logo-text">HIM</span>
        </a>
        
        <nav class="header-nav-guest" aria-label="Main navigation">
            <a href="index.php#features" class="nav-link-guest">Features</a>
            <a href="index.php#about" class="nav-link-guest">About</a>
            <a href="login.php" class="btn btn-ghost-nav">Login</a>
            <a href="register.php" class="btn btn-primary-sm">Get Started</a>
        </nav>
        
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay">
    <div class="mobile-menu-content">
        <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <a href="index.php#features">Features</a>
        <a href="index.php#about">About</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="btn btn-primary">Get Started</a>
    </div>
</div>

<main id="main-content">

<?php endif; ?>

<?php
// Display flash messages
$flash = getFlashMessage();
if ($flash):
?>
<div class="flash-message flash-<?= $flash['type'] ?>" id="flashMessage">
    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : ($flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle') ?>"></i>
    <span><?= sanitize($flash['message']) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
</div>
<?php endif; ?>
