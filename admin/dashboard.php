<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$activeToday = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM mood_logs WHERE log_date = CURDATE()")->fetchColumn();
$totalChats = $pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
$totalPeriods = $pdo->query("SELECT COUNT(*) FROM period_logs")->fetchColumn();

// Recent users
$recentUsers = $pdo->query("SELECT id, full_name, email, is_active, created_at FROM users ORDER BY created_at DESC LIMIT 20")->fetchAll();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle_user') {
        $targetId = (int)$_POST['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != 'admin'");
        $stmt->execute([$targetId]);
        redirect('dashboard.php', 'User status updated.', 'success');
    }
}

// Custom header for admin
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | HIM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <a href="../dashboard.php" class="logo"><span class="logo-icon">💕</span><span class="logo-text">HIM</span></a>
            <nav style="display:flex; gap:8px; align-items:center;">
                <span class="badge badge-warning">Admin Panel</span>
                <a href="../dashboard.php" class="btn btn-sm btn-ghost-nav">← Back to App</a>
                <a href="../logout.php" class="btn btn-sm btn-outline" style="color:var(--color-error); border-color:var(--color-error);">Logout</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <h2 data-aos="fade-up" style="margin-bottom:24px;">
                <i class="fa-solid fa-shield-halved" style="color:var(--color-primary);"></i> Admin Dashboard
            </h2>
            
            <!-- Stats -->
            <div class="grid grid-4 mb-3" data-aos="fade-up">
                <div class="card text-center">
                    <div style="font-size:32px; font-weight:800; color:var(--color-primary);"><?= $totalUsers ?></div>
                    <p style="font-size:13px; color:var(--text-muted);">Total Users</p>
                </div>
                <div class="card text-center">
                    <div style="font-size:32px; font-weight:800; color:var(--color-sage);"><?= $activeToday ?></div>
                    <p style="font-size:13px; color:var(--text-muted);">Active Today</p>
                </div>
                <div class="card text-center">
                    <div style="font-size:32px; font-weight:800; color:var(--color-secondary);"><?= $totalChats ?></div>
                    <p style="font-size:13px; color:var(--text-muted);">Chat Messages</p>
                </div>
                <div class="card text-center">
                    <div style="font-size:32px; font-weight:800; color:var(--color-warning);"><?= $totalPeriods ?></div>
                    <p style="font-size:13px; color:var(--text-muted);">Periods Logged</p>
                </div>
            </div>
            
            <!-- User Management -->
            <div class="card" data-aos="fade-up" data-aos-delay="100">
                <h3 class="card-title">User Management</h3>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <thead>
                            <tr style="border-bottom:2px solid var(--border-light);">
                                <th style="padding:12px; text-align:left;">ID</th>
                                <th style="padding:12px; text-align:left;">Name</th>
                                <th style="padding:12px; text-align:left;">Email</th>
                                <th style="padding:12px; text-align:left;">Status</th>
                                <th style="padding:12px; text-align:left;">Joined</th>
                                <th style="padding:12px; text-align:left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $u): ?>
                            <tr style="border-bottom:1px solid var(--border-light);">
                                <td style="padding:12px;">#<?= $u['id'] ?></td>
                                <td style="padding:12px; font-weight:600;"><?= sanitize($u['full_name']) ?></td>
                                <td style="padding:12px;"><?= sanitize($u['email']) ?></td>
                                <td style="padding:12px;">
                                    <span class="badge badge-<?= $u['is_active'] ? 'success' : 'warning' ?>">
                                        <?= $u['is_active'] ? 'Active' : 'Disabled' ?>
                                    </span>
                                </td>
                                <td style="padding:12px;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td style="padding:12px;">
                                    <form method="POST" style="display:inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_user">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm" style="font-size:12px; padding:4px 12px; background:<?= $u['is_active'] ? 'var(--color-error)' : 'var(--color-success)' ?>; color:white;">
                                            <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="../assets/js/app.js"></script>
</body>
</html>
