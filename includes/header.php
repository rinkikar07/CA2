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
    
    <style>
    /* ===== Language Top Bar ===== */
    .lang-topbar {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1100;
        height: 36px;
    }
    .lang-topbar .lang-label {
        color: rgba(255,255,255,0.9);
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .lang-topbar select {
        background: rgba(255,255,255,0.18);
        color: white;
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: 20px;
        padding: 3px 28px 3px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='white'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 9px center;
        font-family: inherit;
        transition: background 0.2s;
    }
    .lang-topbar select:hover { background-color: rgba(255,255,255,0.28); }
    .lang-topbar select option { background: #2B1E38; color: white; }

    /* Push header below the top bar */
    .header { top: 36px !important; }
    body { padding-top: 36px; }

    /* ===== Completely hide ALL Google Translate UI ===== */
    .goog-te-banner-frame,
    .goog-te-balloon-frame,
    .goog-te-spinner-pos,
    .goog-tooltip,
    .goog-tooltip-content,
    #goog-gt-tt,
    .skiptranslate { display: none !important; visibility: hidden !important; }
    body { top: 0 !important; }
    /* ===== Theme Swatch Grid ===== */
    .theme-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
        gap: 14px;
        justify-items: center;
    }
    .theme-swatch {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        background: none;
        border: 2px solid transparent;
        border-radius: 16px;
        padding: 10px 8px 8px;
        cursor: pointer;
        transition: transform 0.25s cubic-bezier(0.175,0.885,0.32,1.275), border-color 0.2s, box-shadow 0.2s;
        width: 100%;
    }
    .theme-swatch:hover {
        transform: translateY(-4px) scale(1.06);
        border-color: var(--color-primary);
        box-shadow: 0 8px 20px rgba(0,0,0,0.10);
    }
    .theme-swatch.active {
        border-color: var(--color-primary);
        background: var(--color-primary-light);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .swatch-preview {
        display: block;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: box-shadow 0.2s;
    }
    .theme-swatch:hover .swatch-preview {
        box-shadow: 0 8px 24px rgba(0,0,0,0.22);
    }
    .swatch-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
        letter-spacing: 0.02em;
        text-align: center;
    }
    .swatch-check {
        display: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--color-primary);
        color: white;
        font-size: 11px;
        align-items: center;
        justify-content: center;
    }
    .theme-swatch.active .swatch-check { display: flex; }
    /* ===== Theme Section Label ===== */
    .theme-section-label {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 10px;
        padding-left: 4px;
    }

    /* ===== Header Quick Theme Panel ===== */
    .theme-panel-wrapper { position: relative; }
    .theme-panel-btn {
        width: 42px; height: 42px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        background: none; border: none; cursor: pointer;
        color: var(--text-secondary);
        transition: background 0.2s, transform 0.2s;
        position: relative;
    }
    .theme-panel-btn:hover {
        background: var(--color-primary-light);
        color: var(--color-primary);
        transform: rotate(20deg) scale(1.1);
    }
    .theme-panel-btn .tp-dot {
        position: absolute; bottom: 5px; right: 5px;
        width: 10px; height: 10px; border-radius: 50%;
        background: var(--color-primary);
        border: 2px solid white;
        transition: background 0.3s;
    }
    .theme-panel-dropdown {
        display: none;
        position: absolute; top: calc(100% + 10px); right: 0;
        width: 280px;
        background: var(--bg-card, white);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.18);
        border: 1px solid var(--border-light);
        padding: 16px;
        z-index: 2000;
        animation: tpSlideIn 0.2s cubic-bezier(0.175,0.885,0.32,1.275);
    }
    .theme-panel-dropdown.open { display: block; }
    @keyframes tpSlideIn {
        from { opacity:0; transform: translateY(-8px) scale(0.95); }
        to   { opacity:1; transform: translateY(0) scale(1); }
    }
    .tp-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 12px;
    }
    .tp-header h4 {
        font-size: 13px; font-weight: 800;
        color: var(--text-primary);
        display: flex; align-items: center; gap: 6px;
    }
    .tp-header a {
        font-size: 11px; color: var(--color-primary);
        font-weight: 600; text-decoration: none;
    }
    .tp-header a:hover { text-decoration: underline; }
    .tp-section-label {
        font-size: 10px; font-weight: 800;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--text-muted); margin: 8px 0 6px 2px;
    }
    .tp-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        margin-bottom: 4px;
    }
    .tp-swatch {
        display: flex; flex-direction: column;
        align-items: center; gap: 4px;
        background: none; border: 2px solid transparent;
        border-radius: 10px; padding: 5px 2px;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.175,0.885,0.32,1.275), border-color 0.15s;
    }
    .tp-swatch:hover { transform: translateY(-3px) scale(1.08); border-color: var(--color-primary); }
    .tp-swatch.active { border-color: var(--color-primary); background: var(--color-primary-light); }
    .tp-circle {
        width: 30px; height: 30px; border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .tp-name {
        font-size: 9px; font-weight: 700;
        color: var(--text-secondary);
        text-align: center; line-height: 1.2;
    }

    /* ===== Header Call Button ===== */
    .call-header-btn {
        width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; cursor: pointer;
        background: none; border: none;
        color: var(--text-secondary);
        transition: background 0.2s, color 0.2s, transform 0.2s;
        position: relative;
    }
    .call-header-btn:hover {
        background: var(--color-primary-light);
        color: var(--color-primary);
        transform: scale(1.1);
    }
    .call-header-btn.ringing {
        background: #22c55e;
        color: white;
        animation: callPulse 1s infinite;
    }
    @keyframes callPulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.5); }
        50%      { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
    }

    /* ===== Call Overlay ===== */
    .call-overlay {
        display: none;
        position: fixed; inset: 0; z-index: 99999;
        background: linear-gradient(160deg, #1A0A2E 0%, #0D0A20 60%, #160820 100%);
        flex-direction: column;
        align-items: center; justify-content: center;
        font-family: var(--font-body);
    }
    .call-overlay.open { display: flex; animation: callFadeIn 0.4s ease; }
    @keyframes callFadeIn {
        from { opacity:0; transform: scale(0.96); }
        to   { opacity:1; transform: scale(1); }
    }

    /* ripple rings */
    .call-ripples {
        position: absolute;
        width: 180px; height: 180px;
        border-radius: 50%;
        top: 50%; left: 50%;
        transform: translate(-50%, -60%);
    }
    .call-ripples span {
        position: absolute; inset: 0;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.15);
        animation: rippleOut 2.4s ease-out infinite;
    }
    .call-ripples span:nth-child(2) { animation-delay: 0.6s; }
    .call-ripples span:nth-child(3) { animation-delay: 1.2s; }
    .call-ripples span:nth-child(4) { animation-delay: 1.8s; }
    @keyframes rippleOut {
        0%   { transform: scale(1);   opacity: 1; }
        100% { transform: scale(3.5); opacity: 0; }
    }

    .call-avatar {
        width: 110px; height: 110px; border-radius: 50%;
        background: linear-gradient(135deg, var(--color-primary,#FF7096), var(--color-secondary,#B19CD9));
        display: flex; align-items: center; justify-content: center;
        font-size: 48px; margin-bottom: 24px;
        box-shadow: 0 0 0 6px rgba(255,255,255,0.1);
        position: relative; z-index: 1;
    }
    .call-avatar.speaking {
        animation: speakPulse 0.8s ease-in-out infinite alternate;
    }
    @keyframes speakPulse {
        from { box-shadow: 0 0 0 6px rgba(255,112,150,0.2); }
        to   { box-shadow: 0 0 0 22px rgba(255,112,150,0); }
    }
    .call-name {
        font-size: 28px; font-weight: 800;
        color: white; margin-bottom: 6px;
        position: relative; z-index: 1;
    }
    .call-subtitle {
        font-size: 15px; color: rgba(255,255,255,0.55);
        margin-bottom: 10px; position: relative; z-index: 1;
    }
    .call-status {
        font-size: 14px; font-weight: 600;
        color: rgba(255,255,255,0.7);
        letter-spacing: 0.04em;
        margin-bottom: 56px;
        position: relative; z-index: 1;
        min-height: 20px;
    }
    .call-timer {
        font-size: 13px; color: #22c55e;
        font-weight: 700; letter-spacing: 0.08em;
        display: none; position: relative; z-index: 1;
        margin-bottom: 56px;
    }

    /* Controls row */
    .call-controls {
        display: flex; gap: 28px;
        align-items: center; justify-content: center;
        position: relative; z-index: 1;
    }
    .call-ctrl-btn {
        width: 58px; height: 58px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; cursor: pointer; border: none;
        background: rgba(255,255,255,0.1); color: white;
        transition: background 0.2s, transform 0.15s;
    }
    .call-ctrl-btn:hover { background: rgba(255,255,255,0.2); transform: scale(1.08); }
    .call-ctrl-btn.active { background: rgba(255,255,255,0.85); color: #1A0A2E; }
    .call-ctrl-btn.muted  { background: rgba(255,80,80,0.3); }

    /* End call (red big) */
    .call-end-btn {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; cursor: pointer; border: none;
        background: #EF4444; color: white;
        box-shadow: 0 8px 24px rgba(239,68,68,0.5);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .call-end-btn:hover { transform: scale(1.08); box-shadow: 0 12px 32px rgba(239,68,68,0.6); }

    /* Accept call (green, shows before connecting) */
    .call-accept-btn {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; cursor: pointer; border: none;
        background: #22c55e; color: white;
        box-shadow: 0 8px 24px rgba(34,197,94,0.5);
        transition: transform 0.15s;
        animation: callPulse 1s infinite;
    }
    .call-accept-btn:hover { transform: scale(1.1); }
    </style>
</head>
<body class="<?= $isAuth ? 'authenticated' : 'guest' ?>">
<!-- Early theme restore — runs before paint to avoid flash -->
<script>
(function(){
    var T={
        pink:{'--color-primary':'#FF7096','--color-primary-dark':'#E8567F','--color-primary-light':'#FFF0F5','--color-secondary':'#B19CD9','--color-secondary-dark':'#9B8EC0','--color-secondary-light':'#F8F4FF','--border-light':'rgba(255,112,150,0.15)','--shadow-sm':'0 4px 12px rgba(255,112,150,0.08)','--shadow-md':'0 8px 24px rgba(255,112,150,0.12)','--shadow-lg':'0 16px 48px rgba(177,156,217,0.18)','--shadow-glow':'0 12px 36px rgba(255,112,150,0.3)','--bg-body':'#FDF9FB','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'},
        pookie:{'--color-primary':'#FFB3C6','--color-primary-dark':'#FF85A1','--color-primary-light':'#FFF0F8','--color-secondary':'#FFC8DD','--color-secondary-dark':'#FFB3C6','--color-secondary-light':'#FFF5FA','--border-light':'rgba(255,179,198,0.25)','--shadow-sm':'0 4px 12px rgba(255,179,198,0.15)','--shadow-md':'0 8px 24px rgba(255,179,198,0.2)','--shadow-lg':'0 16px 48px rgba(255,200,221,0.25)','--shadow-glow':'0 12px 36px rgba(255,179,198,0.4)','--bg-body':'#FFF8FA','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#3D1F35','--text-secondary':'#6B4260','--text-muted':'#B590AA'},
        cottoncandy:{'--color-primary':'#FF85A1','--color-primary-dark':'#FF6B8E','--color-primary-light':'#FFF0F5','--color-secondary':'#85C1FF','--color-secondary-dark':'#5BABFF','--color-secondary-light':'#EBF5FF','--border-light':'rgba(255,133,161,0.2)','--shadow-sm':'0 4px 12px rgba(255,133,161,0.1)','--shadow-md':'0 8px 24px rgba(133,193,255,0.12)','--shadow-lg':'0 16px 48px rgba(255,133,161,0.18)','--shadow-glow':'0 12px 36px rgba(255,133,161,0.35)','--bg-body':'#FFF8FB','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2A1830','--text-secondary':'#574060','--text-muted':'#A080A8'},
        peach:{'--color-primary':'#FFAD8A','--color-primary-dark':'#FF8C63','--color-primary-light':'#FFF5F0','--color-secondary':'#FFCBA4','--color-secondary-dark':'#FFAD8A','--color-secondary-light':'#FFF8F2','--border-light':'rgba(255,173,138,0.2)','--shadow-sm':'0 4px 12px rgba(255,173,138,0.1)','--shadow-md':'0 8px 24px rgba(255,173,138,0.15)','--shadow-lg':'0 16px 48px rgba(255,203,164,0.2)','--shadow-glow':'0 12px 36px rgba(255,173,138,0.35)','--bg-body':'#FFFAF7','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#3A1F10','--text-secondary':'#6B4030','--text-muted':'#B08070'},
        lavender:{'--color-primary':'#9B72CF','--color-primary-dark':'#7C52B0','--color-primary-light':'#F3EEFF','--color-secondary':'#C9A8E8','--color-secondary-dark':'#A880CC','--color-secondary-light':'#FAF5FF','--border-light':'rgba(155,114,207,0.15)','--shadow-sm':'0 4px 12px rgba(155,114,207,0.08)','--shadow-md':'0 8px 24px rgba(155,114,207,0.12)','--shadow-lg':'0 16px 48px rgba(201,168,232,0.18)','--shadow-glow':'0 12px 36px rgba(155,114,207,0.3)','--bg-body':'#FAF8FE','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'},
        rosegold:{'--color-primary':'#C67C9E','--color-primary-dark':'#A85D82','--color-primary-light':'#FDF0F6','--color-secondary':'#E8A0BF','--color-secondary-dark':'#C67C9E','--color-secondary-light':'#FDEDF6','--border-light':'rgba(198,124,158,0.15)','--shadow-sm':'0 4px 12px rgba(198,124,158,0.08)','--shadow-md':'0 8px 24px rgba(198,124,158,0.12)','--shadow-lg':'0 16px 48px rgba(232,160,191,0.18)','--shadow-glow':'0 12px 36px rgba(198,124,158,0.3)','--bg-body':'#FEF9FC','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'},
        mint:{'--color-primary':'#10B981','--color-primary-dark':'#059669','--color-primary-light':'#ECFDF5','--color-secondary':'#6EE7B7','--color-secondary-dark':'#34D399','--color-secondary-light':'#D1FAE5','--border-light':'rgba(16,185,129,0.15)','--shadow-sm':'0 4px 12px rgba(16,185,129,0.08)','--shadow-md':'0 8px 24px rgba(16,185,129,0.12)','--shadow-lg':'0 16px 48px rgba(110,231,183,0.18)','--shadow-glow':'0 12px 36px rgba(16,185,129,0.3)','--bg-body':'#F5FEFA','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#0A2A1E','--text-secondary':'#1E5040','--text-muted':'#70A890'},
        ocean:{'--color-primary':'#0EA5E9','--color-primary-dark':'#0284C7','--color-primary-light':'#F0F9FF','--color-secondary':'#38BDF8','--color-secondary-dark':'#0EA5E9','--color-secondary-light':'#E0F4FF','--border-light':'rgba(14,165,233,0.15)','--shadow-sm':'0 4px 12px rgba(14,165,233,0.08)','--shadow-md':'0 8px 24px rgba(14,165,233,0.12)','--shadow-lg':'0 16px 48px rgba(56,189,248,0.18)','--shadow-glow':'0 12px 36px rgba(14,165,233,0.3)','--bg-body':'#F8FCFF','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#0A1F30','--text-secondary':'#1A4060','--text-muted':'#6090A8'},
        sunset:{'--color-primary':'#F97316','--color-primary-dark':'#EA6504','--color-primary-light':'#FFF7ED','--color-secondary':'#FBBF24','--color-secondary-dark':'#F59E0B','--color-secondary-light':'#FFFBEB','--border-light':'rgba(249,115,22,0.15)','--shadow-sm':'0 4px 12px rgba(249,115,22,0.08)','--shadow-md':'0 8px 24px rgba(249,115,22,0.12)','--shadow-lg':'0 16px 48px rgba(251,191,36,0.18)','--shadow-glow':'0 12px 36px rgba(249,115,22,0.3)','--bg-body':'#FFFAF5','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2A1505','--text-secondary':'#5A3010','--text-muted':'#A07040'},
        cherry:{'--color-primary':'#EF4444','--color-primary-dark':'#DC2626','--color-primary-light':'#FEF2F2','--color-secondary':'#FB7185','--color-secondary-dark':'#F43F5E','--color-secondary-light':'#FFF1F2','--border-light':'rgba(239,68,68,0.15)','--shadow-sm':'0 4px 12px rgba(239,68,68,0.08)','--shadow-md':'0 8px 24px rgba(239,68,68,0.12)','--shadow-lg':'0 16px 48px rgba(251,113,133,0.18)','--shadow-glow':'0 12px 36px rgba(239,68,68,0.3)','--bg-body':'#FFF8F8','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2A0808','--text-secondary':'#5A1818','--text-muted':'#A06060'},
        darknite:{'--color-primary':'#FF7096','--color-primary-dark':'#E8567F','--color-primary-light':'#2A1525','--color-secondary':'#B19CD9','--color-secondary-dark':'#9B8EC0','--color-secondary-light':'#1E1530','--border-light':'rgba(255,112,150,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(255,112,150,0.4)','--bg-body':'#0D0A14','--bg-card':'rgba(28,20,42,0.97)','--text-primary':'#F5F0FF','--text-secondary':'#E2D5F3','--text-muted':'#A799B7'},
        darkviolet:{'--color-primary':'#A78BFA','--color-primary-dark':'#7C3AED','--color-primary-light':'#1A1030','--color-secondary':'#C084FC','--color-secondary-dark':'#A855F7','--color-secondary-light':'#14082A','--border-light':'rgba(167,139,250,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(167,139,250,0.4)','--bg-body':'#0A0514','--bg-card':'rgba(20,10,35,0.97)','--text-primary':'#F0EAFF','--text-secondary':'#DCD0F8','--text-muted':'#9D8DBF'},
        darkocean:{'--color-primary':'#38BDF8','--color-primary-dark':'#0EA5E9','--color-primary-light':'#0A2030','--color-secondary':'#67E8F9','--color-secondary-dark':'#22D3EE','--color-secondary-light':'#082028','--border-light':'rgba(56,189,248,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(56,189,248,0.4)','--bg-body':'#030D18','--bg-card':'rgba(8,20,38,0.97)','--text-primary':'#E8F5FF','--text-secondary':'#C0D8F0','--text-muted':'#80A0C0'},
        darkrose:{'--color-primary':'#FB7185','--color-primary-dark':'#F43F5E','--color-primary-light':'#280A14','--color-secondary':'#FDA4AF','--color-secondary-dark':'#FB7185','--color-secondary-light':'#200810','--border-light':'rgba(251,113,133,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(251,113,133,0.4)','--bg-body':'#130508','--bg-card':'rgba(30,8,16,0.97)','--text-primary':'#FFF0F3','--text-secondary':'#F0C0C8','--text-muted':'#B08090'},
        darkforest:{'--color-primary':'#34D399','--color-primary-dark':'#10B981','--color-primary-light':'#092418','--color-secondary':'#6EE7B7','--color-secondary-dark':'#34D399','--color-secondary-light':'#071A10','--border-light':'rgba(52,211,153,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(52,211,153,0.4)','--bg-body':'#030E08','--bg-card':'rgba(7,22,14,0.97)','--text-primary':'#E8FFF5','--text-secondary':'#B0E0C8','--text-muted':'#709080'},
        midnight:{'--color-primary':'#4F46E5','--color-primary-dark':'#3730A3','--color-primary-light':'#EEF2FF','--color-secondary':'#7C3AED','--color-secondary-dark':'#6D28D9','--color-secondary-light':'#F5F3FF','--border-light':'rgba(79,70,229,0.15)','--shadow-sm':'0 4px 12px rgba(79,70,229,0.08)','--shadow-md':'0 8px 24px rgba(79,70,229,0.12)','--shadow-lg':'0 16px 48px rgba(124,58,237,0.18)','--shadow-glow':'0 12px 36px rgba(79,70,229,0.3)','--bg-body':'#F8F8FF','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'}
    };
    var n=localStorage.getItem('him_theme')||'pink';
    var v=T[n]||T['pink'];
    var r=document.documentElement;
    Object.keys(v).forEach(function(k){r.style.setProperty(k,v[k]);});
})();
</script>

<!-- ===== LANGUAGE TOP BAR ===== -->
<div class="lang-topbar" id="langTopbar">
    <span class="lang-label"><i class="fa-solid fa-language"></i> Language:</span>
    <select id="indianLangSelect" onchange="changeLanguage(this.value)" aria-label="Select Indian language">
        <option value="en">🇮🇳 English</option>
        <option value="hi">हिन्दी (Hindi)</option>
        <option value="bn">বাংলা (Bengali)</option>
        <option value="te">తెలుగు (Telugu)</option>
        <option value="mr">मराठी (Marathi)</option>
        <option value="ta">தமிழ் (Tamil)</option>
        <option value="gu">ગુજરાતી (Gujarati)</option>
        <option value="kn">ಕನ್ನಡ (Kannada)</option>
        <option value="ml">മലയാളം (Malayalam)</option>
        <option value="pa">ਪੰਜਾਬੀ (Punjabi)</option>
        <option value="or">ଓଡ଼ିଆ (Odia)</option>
        <option value="ur">اردو (Urdu)</option>
        <option value="as">অসমীয়া (Assamese)</option>
        <option value="mai">मैथिली (Maithili)</option>
        <option value="ne">नेपाली (Nepali)</option>
        <option value="sd">سنڌي (Sindhi)</option>
        <option value="ks">کشمیری (Kashmiri)</option>
        <option value="sa">संस्कृतम् (Sanskrit)</option>
        <option value="gom">कोंकणी (Konkani)</option>
        <option value="mni">মৈতৈলোন্ (Manipuri)</option>
        <option value="brx">बड़ो (Bodo)</option>
        <option value="doi">डोगरी (Dogri)</option>
        <option value="sat">ᱥᱟᱱᱛᱟᱲᱤ (Santali)</option>
    </select>
</div>

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
                <span class="logo-icon"><i class="fa-solid fa-heart"></i></span>
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
            <a href="audiobooks.php" class="nav-link <?= $currentPage === 'audiobooks' ? 'active' : '' ?>">
                <i class="fa-solid fa-headphones"></i> <span>Audiobooks</span>
            </a>
            <a href="insights.php" class="nav-link <?= $currentPage === 'insights' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i> <span>Insights</span>
            </a>
        </nav>
        
        <div class="header-right">
            <!-- Voice Call Button -->
            <button class="call-header-btn" id="callHeaderBtn" aria-label="Call HIM" title="Call HIM">
                <i class="fa-solid fa-phone"></i>
            </button>

            <!-- Quick Theme Panel -->
            <div class="theme-panel-wrapper" id="themePanelWrapper">
                <button class="theme-panel-btn" id="themePanelBtn" aria-label="Change theme" title="Quick Theme">
                    <i class="fa-solid fa-palette"></i>
                    <span class="tp-dot" id="tpDot"></span>
                </button>
                <div class="theme-panel-dropdown" id="themePanelDropdown">
                    <div class="tp-header">
                        <h4><i class="fa-solid fa-palette"></i> App Theme</h4>
                        <a href="profile.php#theme-section">All themes &rarr;</a>
                    </div>

                    <div class="tp-section-label">✨ Light &amp; Pastel</div>
                    <div class="tp-grid">
                        <button class="tp-swatch" data-tp="pink" onclick="applyHeaderTheme('pink')" title="Blossom">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#FF7096,#B19CD9);"></span>
                            <span class="tp-name">Blossom</span>
                        </button>
                        <button class="tp-swatch" data-tp="pookie" onclick="applyHeaderTheme('pookie')" title="Pookie">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#FFB3C6,#FFC8DD);"></span>
                            <span class="tp-name">Pookie</span>
                        </button>
                        <button class="tp-swatch" data-tp="cottoncandy" onclick="applyHeaderTheme('cottoncandy')" title="Cotton Candy">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#FF85A1,#85C1FF);"></span>
                            <span class="tp-name">Cotton</span>
                        </button>
                        <button class="tp-swatch" data-tp="peach" onclick="applyHeaderTheme('peach')" title="Peachy">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#FFAD8A,#FFCBA4);"></span>
                            <span class="tp-name">Peach</span>
                        </button>
                        <button class="tp-swatch" data-tp="lavender" onclick="applyHeaderTheme('lavender')" title="Lavender">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#9B72CF,#C9A8E8);"></span>
                            <span class="tp-name">Lavender</span>
                        </button>
                        <button class="tp-swatch" data-tp="rosegold" onclick="applyHeaderTheme('rosegold')" title="Rose Gold">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#E8A0BF,#C67C9E);"></span>
                            <span class="tp-name">Rose Gold</span>
                        </button>
                        <button class="tp-swatch" data-tp="mint" onclick="applyHeaderTheme('mint')" title="Mint">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#10B981,#6EE7B7);"></span>
                            <span class="tp-name">Mint</span>
                        </button>
                        <button class="tp-swatch" data-tp="ocean" onclick="applyHeaderTheme('ocean')" title="Ocean">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#0EA5E9,#38BDF8);"></span>
                            <span class="tp-name">Ocean</span>
                        </button>
                        <button class="tp-swatch" data-tp="sunset" onclick="applyHeaderTheme('sunset')" title="Sunset">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#F97316,#FBBF24);"></span>
                            <span class="tp-name">Sunset</span>
                        </button>
                        <button class="tp-swatch" data-tp="cherry" onclick="applyHeaderTheme('cherry')" title="Cherry">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#EF4444,#FB7185);"></span>
                            <span class="tp-name">Cherry</span>
                        </button>
                    </div>

                    <div class="tp-section-label">🌙 Dark Modes</div>
                    <div class="tp-grid">
                        <button class="tp-swatch" data-tp="darknite" onclick="applyHeaderTheme('darknite')" title="Dark Night">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#1A1030,#FF7096);"></span>
                            <span class="tp-name">Dark Night</span>
                        </button>
                        <button class="tp-swatch" data-tp="darkviolet" onclick="applyHeaderTheme('darkviolet')" title="Dark Violet">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#0D0820,#7C3AED);"></span>
                            <span class="tp-name">Dark Violet</span>
                        </button>
                        <button class="tp-swatch" data-tp="darkocean" onclick="applyHeaderTheme('darkocean')" title="Dark Ocean">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#050F1A,#0EA5E9);"></span>
                            <span class="tp-name">Dark Ocean</span>
                        </button>
                        <button class="tp-swatch" data-tp="darkrose" onclick="applyHeaderTheme('darkrose')" title="Dark Rose">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#180810,#FB7185);"></span>
                            <span class="tp-name">Dark Rose</span>
                        </button>
                        <button class="tp-swatch" data-tp="darkforest" onclick="applyHeaderTheme('darkforest')" title="Dark Forest">
                            <span class="tp-circle" style="background:linear-gradient(135deg,#071A0E,#10B981);"></span>
                            <span class="tp-name">Dark Forest</span>
                        </button>
                    </div>
                </div>
            </div>

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
    <a href="audiobooks.php" class="mobile-nav-item <?= $currentPage === 'audiobooks' ? 'active' : '' ?>">
        <i class="fa-solid fa-headphones"></i><span>Audio</span>
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
            <span class="logo-icon"><i class="fa-solid fa-heart"></i></span>
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

<!-- Hidden GT container — engine runs silently in background -->
<div id="google_translate_element" style="display:none; visibility:hidden; position:absolute; left:-9999px; top:-9999px;"></div>

<script type="text/javascript">
// Load Google Translate engine silently
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'hi,bn,te,mr,ta,gu,kn,ml,pa,or,ur,as,en',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}

// Set GT cookie and reload — no GT bar ever visible
function changeLanguage(lang) {
    var host = window.location.hostname;
    if (lang === 'en') {
        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + host + ';';
        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.' + host + ';';
    } else {
        var val = '/en/' + lang;
        document.cookie = 'googtrans=' + val + '; path=/;';
        document.cookie = 'googtrans=' + val + '; path=/; domain=' + host + ';';
    }
    window.location.reload();
}

// Restore dropdown selection from active cookie
(function () {
    var match = document.cookie.match(/googtrans=\/en\/([a-z]+)/);
    var sel = document.getElementById('indianLangSelect');
    if (match && sel) sel.value = match[1];
})();
</script>
<script>
/* ===== Quick Theme Panel (header palette button) ===== */
(function() {
    var btn  = document.getElementById('themePanelBtn');
    var drop = document.getElementById('themePanelDropdown');
    if (!btn || !drop) return;

    // Toggle panel open/close
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        drop.classList.toggle('open');
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!document.getElementById('themePanelWrapper').contains(e.target)) {
            drop.classList.remove('open');
        }
    });

    // Mark active swatch on load
    var saved = localStorage.getItem('him_theme') || 'pink';
    highlightTpSwatch(saved);
})();

/* Theme data — mirrors the full THEMES map */
var _TP_THEMES = {
    pink:{'--color-primary':'#FF7096','--color-primary-dark':'#E8567F','--color-primary-light':'#FFF0F5','--color-secondary':'#B19CD9','--color-secondary-dark':'#9B8EC0','--color-secondary-light':'#F8F4FF','--border-light':'rgba(255,112,150,0.15)','--shadow-sm':'0 4px 12px rgba(255,112,150,0.08)','--shadow-md':'0 8px 24px rgba(255,112,150,0.12)','--shadow-lg':'0 16px 48px rgba(177,156,217,0.18)','--shadow-glow':'0 12px 36px rgba(255,112,150,0.3)','--bg-body':'#FDF9FB','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'},
    pookie:{'--color-primary':'#FFB3C6','--color-primary-dark':'#FF85A1','--color-primary-light':'#FFF0F8','--color-secondary':'#FFC8DD','--color-secondary-dark':'#FFB3C6','--color-secondary-light':'#FFF5FA','--border-light':'rgba(255,179,198,0.25)','--shadow-sm':'0 4px 12px rgba(255,179,198,0.15)','--shadow-md':'0 8px 24px rgba(255,179,198,0.2)','--shadow-lg':'0 16px 48px rgba(255,200,221,0.25)','--shadow-glow':'0 12px 36px rgba(255,179,198,0.4)','--bg-body':'#FFF8FA','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#3D1F35','--text-secondary':'#6B4260','--text-muted':'#B590AA'},
    cottoncandy:{'--color-primary':'#FF85A1','--color-primary-dark':'#FF6B8E','--color-primary-light':'#FFF0F5','--color-secondary':'#85C1FF','--color-secondary-dark':'#5BABFF','--color-secondary-light':'#EBF5FF','--border-light':'rgba(255,133,161,0.2)','--shadow-sm':'0 4px 12px rgba(255,133,161,0.1)','--shadow-md':'0 8px 24px rgba(133,193,255,0.12)','--shadow-lg':'0 16px 48px rgba(255,133,161,0.18)','--shadow-glow':'0 12px 36px rgba(255,133,161,0.35)','--bg-body':'#FFF8FB','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2A1830','--text-secondary':'#574060','--text-muted':'#A080A8'},
    peach:{'--color-primary':'#FFAD8A','--color-primary-dark':'#FF8C63','--color-primary-light':'#FFF5F0','--color-secondary':'#FFCBA4','--color-secondary-dark':'#FFAD8A','--color-secondary-light':'#FFF8F2','--border-light':'rgba(255,173,138,0.2)','--shadow-sm':'0 4px 12px rgba(255,173,138,0.1)','--shadow-md':'0 8px 24px rgba(255,173,138,0.15)','--shadow-lg':'0 16px 48px rgba(255,203,164,0.2)','--shadow-glow':'0 12px 36px rgba(255,173,138,0.35)','--bg-body':'#FFFAF7','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#3A1F10','--text-secondary':'#6B4030','--text-muted':'#B08070'},
    lavender:{'--color-primary':'#9B72CF','--color-primary-dark':'#7C52B0','--color-primary-light':'#F3EEFF','--color-secondary':'#C9A8E8','--color-secondary-dark':'#A880CC','--color-secondary-light':'#FAF5FF','--border-light':'rgba(155,114,207,0.15)','--shadow-sm':'0 4px 12px rgba(155,114,207,0.08)','--shadow-md':'0 8px 24px rgba(155,114,207,0.12)','--shadow-lg':'0 16px 48px rgba(201,168,232,0.18)','--shadow-glow':'0 12px 36px rgba(155,114,207,0.3)','--bg-body':'#FAF8FE','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'},
    rosegold:{'--color-primary':'#C67C9E','--color-primary-dark':'#A85D82','--color-primary-light':'#FDF0F6','--color-secondary':'#E8A0BF','--color-secondary-dark':'#C67C9E','--color-secondary-light':'#FDEDF6','--border-light':'rgba(198,124,158,0.15)','--shadow-sm':'0 4px 12px rgba(198,124,158,0.08)','--shadow-md':'0 8px 24px rgba(198,124,158,0.12)','--shadow-lg':'0 16px 48px rgba(232,160,191,0.18)','--shadow-glow':'0 12px 36px rgba(198,124,158,0.3)','--bg-body':'#FEF9FC','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE'},
    mint:{'--color-primary':'#10B981','--color-primary-dark':'#059669','--color-primary-light':'#ECFDF5','--color-secondary':'#6EE7B7','--color-secondary-dark':'#34D399','--color-secondary-light':'#D1FAE5','--border-light':'rgba(16,185,129,0.15)','--shadow-sm':'0 4px 12px rgba(16,185,129,0.08)','--shadow-md':'0 8px 24px rgba(16,185,129,0.12)','--shadow-lg':'0 16px 48px rgba(110,231,183,0.18)','--shadow-glow':'0 12px 36px rgba(16,185,129,0.3)','--bg-body':'#F5FEFA','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#0A2A1E','--text-secondary':'#1E5040','--text-muted':'#70A890'},
    ocean:{'--color-primary':'#0EA5E9','--color-primary-dark':'#0284C7','--color-primary-light':'#F0F9FF','--color-secondary':'#38BDF8','--color-secondary-dark':'#0EA5E9','--color-secondary-light':'#E0F4FF','--border-light':'rgba(14,165,233,0.15)','--shadow-sm':'0 4px 12px rgba(14,165,233,0.08)','--shadow-md':'0 8px 24px rgba(14,165,233,0.12)','--shadow-lg':'0 16px 48px rgba(56,189,248,0.18)','--shadow-glow':'0 12px 36px rgba(14,165,233,0.3)','--bg-body':'#F8FCFF','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#0A1F30','--text-secondary':'#1A4060','--text-muted':'#6090A8'},
    sunset:{'--color-primary':'#F97316','--color-primary-dark':'#EA6504','--color-primary-light':'#FFF7ED','--color-secondary':'#FBBF24','--color-secondary-dark':'#F59E0B','--color-secondary-light':'#FFFBEB','--border-light':'rgba(249,115,22,0.15)','--shadow-sm':'0 4px 12px rgba(249,115,22,0.08)','--shadow-md':'0 8px 24px rgba(249,115,22,0.12)','--shadow-lg':'0 16px 48px rgba(251,191,36,0.18)','--shadow-glow':'0 12px 36px rgba(249,115,22,0.3)','--bg-body':'#FFFAF5','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2A1505','--text-secondary':'#5A3010','--text-muted':'#A07040'},
    cherry:{'--color-primary':'#EF4444','--color-primary-dark':'#DC2626','--color-primary-light':'#FEF2F2','--color-secondary':'#FB7185','--color-secondary-dark':'#F43F5E','--color-secondary-light':'#FFF1F2','--border-light':'rgba(239,68,68,0.15)','--shadow-sm':'0 4px 12px rgba(239,68,68,0.08)','--shadow-md':'0 8px 24px rgba(239,68,68,0.12)','--shadow-lg':'0 16px 48px rgba(251,113,133,0.18)','--shadow-glow':'0 12px 36px rgba(239,68,68,0.3)','--bg-body':'#FFF8F8','--bg-card':'rgba(255,255,255,0.95)','--text-primary':'#2A0808','--text-secondary':'#5A1818','--text-muted':'#A06060'},
    darknite:{'--color-primary':'#FF7096','--color-primary-dark':'#E8567F','--color-primary-light':'#2A1525','--color-secondary':'#B19CD9','--color-secondary-dark':'#9B8EC0','--color-secondary-light':'#1E1530','--border-light':'rgba(255,112,150,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(255,112,150,0.4)','--bg-body':'#0D0A14','--bg-card':'rgba(28,20,42,0.97)','--text-primary':'#F5F0FF','--text-secondary':'#E2D5F3','--text-muted':'#A799B7'},
    darkviolet:{'--color-primary':'#A78BFA','--color-primary-dark':'#7C3AED','--color-primary-light':'#1A1030','--color-secondary':'#C084FC','--color-secondary-dark':'#A855F7','--color-secondary-light':'#14082A','--border-light':'rgba(167,139,250,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(167,139,250,0.4)','--bg-body':'#0A0514','--bg-card':'rgba(20,10,35,0.97)','--text-primary':'#F0EAFF','--text-secondary':'#DCD0F8','--text-muted':'#9D8DBF'},
    darkocean:{'--color-primary':'#38BDF8','--color-primary-dark':'#0EA5E9','--color-primary-light':'#0A2030','--color-secondary':'#67E8F9','--color-secondary-dark':'#22D3EE','--color-secondary-light':'#082028','--border-light':'rgba(56,189,248,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(56,189,248,0.4)','--bg-body':'#030D18','--bg-card':'rgba(8,20,38,0.97)','--text-primary':'#E8F5FF','--text-secondary':'#C0D8F0','--text-muted':'#80A0C0'},
    darkrose:{'--color-primary':'#FB7185','--color-primary-dark':'#F43F5E','--color-primary-light':'#280A14','--color-secondary':'#FDA4AF','--color-secondary-dark':'#FB7185','--color-secondary-light':'#200810','--border-light':'rgba(251,113,133,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(251,113,133,0.4)','--bg-body':'#130508','--bg-card':'rgba(30,8,16,0.97)','--text-primary':'#FFF0F3','--text-secondary':'#F0C0C8','--text-muted':'#B08090'},
    darkforest:{'--color-primary':'#34D399','--color-primary-dark':'#10B981','--color-primary-light':'#092418','--color-secondary':'#6EE7B7','--color-secondary-dark':'#34D399','--color-secondary-light':'#071A10','--border-light':'rgba(52,211,153,0.2)','--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)','--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(52,211,153,0.4)','--bg-body':'#030E08','--bg-card':'rgba(7,22,14,0.97)','--text-primary':'#E8FFF5','--text-secondary':'#B0E0C8','--text-muted':'#709080'}
};

function applyHeaderTheme(name) {
    var vars = _TP_THEMES[name];
    if (!vars) return;
    var r = document.documentElement;
    Object.keys(vars).forEach(function(k) { r.style.setProperty(k, vars[k]); });
    localStorage.setItem('him_theme', name);
    highlightTpSwatch(name);
    // Also sync profile page swatches if present
    if (typeof applyTheme === 'function') applyTheme(name);
    // Close panel
    var drop = document.getElementById('themePanelDropdown');
    if (drop) setTimeout(function(){ drop.classList.remove('open'); }, 300);
}

function highlightTpSwatch(name) {
    document.querySelectorAll('.tp-swatch').forEach(function(s) {
        s.classList.toggle('active', s.dataset.tp === name);
    });
    // Also sync profile-page swatches if present
    document.querySelectorAll('.theme-swatch').forEach(function(s) {
        s.classList.toggle('active', s.dataset.theme === name);
    });
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<!-- ===== CALL OVERLAY ===== -->
<div class="call-overlay" id="callOverlay">
    <div class="call-ripples"><span></span><span></span><span></span><span></span></div>
    <div class="call-avatar" id="callAvatar">💕</div>
    <div class="call-name">HIM</div>
    <div class="call-subtitle">Her Intelligent Mate</div>
    <div class="call-status" id="callStatus">Calling...</div>
    <div class="call-timer" id="callTimer">00:00</div>
    <div class="call-controls">
        <button class="call-ctrl-btn" id="muteBtn" title="Mute" onclick="toggleMute()">
            <i class="fa-solid fa-microphone" id="muteIcon"></i>
        </button>
        <button class="call-ctrl-btn" id="speakerBtn" title="Speaker" onclick="toggleSpeaker()">
            <i class="fa-solid fa-volume-up" id="speakerIcon"></i>
        </button>
        <button class="call-end-btn" title="End call" onclick="endCall()">
            <i class="fa-solid fa-phone-slash"></i>
        </button>
    </div>
</div>

<script type="module">
import { Conversation } from "https://cdn.jsdelivr.net/npm/@11labs/client/+esm";
const AGENT_ID = 'agent_9101kp14nztefgw986jp2kkyg07f';
let _conv=null, _muted=false, _timerInt=null, _seconds=0;

const callBtn   = document.getElementById('callHeaderBtn');
const overlay   = document.getElementById('callOverlay');
const avatar    = document.getElementById('callAvatar');
const statusEl  = document.getElementById('callStatus');
const timerEl   = document.getElementById('callTimer');
const muteIcon  = document.getElementById('muteIcon');

if (callBtn) callBtn.addEventListener('click', openCall);

function openCall() {
    if (overlay.classList.contains('open')) return;
    overlay.classList.add('open');
    callBtn.classList.add('ringing');
    statusEl.textContent = 'Calling...';
    timerEl.style.display = 'none';
    avatar.classList.remove('speaking');
    startConversation();
}

async function startConversation() {
    try {
        statusEl.textContent = 'Connecting...';
        _conv = await Conversation.startSession({
            agentId: AGENT_ID,
            onConnect: () => {
                statusEl.textContent = '';
                timerEl.style.display = 'block';
                callBtn.classList.remove('ringing');
                callBtn.style.background = '#22c55e';
                callBtn.style.color = 'white';
                startTimer();
            },
            onDisconnect: () => closeCallUI(),
            onError: (err) => {
                console.error('Call error:', err);
                statusEl.textContent = 'Could not connect. Press \u274c to close.';
                callBtn.classList.remove('ringing');
            },
            onModeChange: (mode) => {
                if (mode.mode === 'speaking') {
                    statusEl.textContent = 'HIM is speaking...';
                    avatar.classList.add('speaking');
                } else {
                    statusEl.textContent = 'Listening...';
                    avatar.classList.remove('speaking');
                }
            }
        });
    } catch(err) {
        console.error(err);
        statusEl.textContent = 'Connection failed. Try again.';
        callBtn.classList.remove('ringing');
    }
}

function startTimer() {
    _seconds = 0;
    _timerInt = setInterval(() => {
        _seconds++;
        timerEl.textContent =
            String(Math.floor(_seconds/60)).padStart(2,'0') + ':' +
            String(_seconds%60).padStart(2,'0');
    }, 1000);
}

window.endCall = async function() {
    if (_conv) { try { await _conv.endSession(); } catch(e){} }
    closeCallUI();
};
window.toggleMute = function() {
    _muted = !_muted;
    if (_conv) _conv.setMuted(_muted);
    muteIcon.className = _muted ? 'fa-solid fa-microphone-slash' : 'fa-solid fa-microphone';
    document.getElementById('muteBtn').classList.toggle('muted', _muted);
};
window.toggleSpeaker = function() {
    const btn = document.getElementById('speakerBtn');
    btn.classList.toggle('active');
    document.getElementById('speakerIcon').className =
        btn.classList.contains('active') ? 'fa-solid fa-volume-high' : 'fa-solid fa-volume-up';
};

function closeCallUI() {
    clearInterval(_timerInt);
    _conv=null; _muted=false;
    muteIcon.className = 'fa-solid fa-microphone';
    document.getElementById('speakerIcon').className = 'fa-solid fa-volume-up';
    document.getElementById('muteBtn').classList.remove('muted');
    document.getElementById('speakerBtn').classList.remove('active');
    avatar.classList.remove('speaking');
    timerEl.style.display = 'none';
    timerEl.textContent = '00:00';
    statusEl.textContent = 'Calling...';
    callBtn.classList.remove('ringing');
    callBtn.style.background = '';
    callBtn.style.color = '';
    overlay.classList.remove('open');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && overlay.classList.contains('open')) window.endCall();
});
</script>

