<?php
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Partner Sharing Mode";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
        <div class="badge bg-soft-primary text-primary mb-2">Beta Feature</div>
        <h2 class="display-5 fw-bold">Partner Sharing Mode</h2>
        <p class="text-muted mx-auto" style="max-width: 600px;">
            Sync your cycle with a partner to foster understanding, improve communication, and ensure you have the support you need, exactly when you need it.
        </p>
    </div>

    <div class="row g-4">
        <!-- Setup Card -->
        <div class="col-md-6" data-aos="fade-right">
            <div class="card h-100 border-0 shadow-sm p-4" style="border-radius: 24px; background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon-box bg-primary text-white rounded-4 p-3 me-3">
                        <i class="fa-solid fa-link-slash"></i>
                    </div>
                    <h4 class="mb-0">Connect a Partner</h4>
                </div>
                <p class="text-secondary mb-4">Invite your partner to view your cycle status and receive phase-specific tips on how to support you.</p>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Partner's Email</label>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="partner@example.com" style="border-radius: 12px 0 0 12px;">
                        <button class="btn btn-primary px-4" style="border-radius: 0 12px 12px 0;">Send Invite</button>
                    </div>
                </div>
                <div class="alert alert-info border-0 rounded-4" style="font-size: 14px;">
                    <i class="fa-solid fa-shield-check me-2"></i> Your partner will <strong>only</strong> see your current phase name and wellness tips. Detailed logs remain private.
                </div>
            </div>
        </div>

        <!-- Benefits/Features Card -->
        <div class="col-md-6" data-aos="fade-left">
            <div class="card h-100 border-0 shadow-sm p-4" style="border-radius: 24px;">
                <h4 class="mb-4">Why use Partner Mode?</h4>
                <div class="d-flex mb-3">
                    <div class="text-primary me-3"><i class="fa-solid fa-check-circle"></i></div>
                    <div>
                        <h6 class="mb-1">Phase Transparency</h6>
                        <p class="small text-muted">Helps partners understand mood shifts and energy levels.</p>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="text-primary me-3"><i class="fa-solid fa-check-circle"></i></div>
                    <div>
                        <h6 class="mb-1">Support Tips</h6>
                        <p class="small text-muted">Gives partners actionable advice on how to help (e.g., "bring her favorite snacks today").</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="text-primary me-3"><i class="fa-solid fa-check-circle"></i></div>
                    <div>
                        <h6 class="mb-1">Shared Calendar</h6>
                        <p class="small text-muted">Plan activities, trips, or events around your cycle more easily.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(0, 122, 255, 0.1); }
    .icon-box { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
</style>

<?php include 'includes/footer.php'; ?>
