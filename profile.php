<?php
$pageTitle = 'Profile Settings';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserProfile($userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $name = sanitize($_POST['full_name'] ?? '');
            $dob = $_POST['date_of_birth'] ?? '';
            $cycleLen = (int)($_POST['avg_cycle_length'] ?? 28);
            $periodLen = (int)($_POST['avg_period_length'] ?? 5);
            $notif = isset($_POST['notification_enabled']) ? 1 : 0;
            
            if (empty($name)) $errors[] = 'Name is required.';
            
            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, date_of_birth = ?, notification_enabled = ? WHERE id = ?");
                $stmt->execute([$name, $dob, $notif, $userId]);
                
                $stmt = $pdo->prepare("UPDATE cycle_settings SET avg_cycle_length = ?, avg_period_length = ? WHERE user_id = ?");
                $stmt->execute([$cycleLen, $periodLen, $userId]);
                
                $_SESSION['user_name'] = $name;
                redirect('profile.php', 'Profile updated! 💕', 'success');
            }
        } elseif ($action === 'change_password') {
            $currentPw = $_POST['current_password'] ?? '';
            $newPw = $_POST['new_password'] ?? '';
            $confirmPw = $_POST['confirm_password'] ?? '';
            
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();
            
            if (!password_verify($currentPw, $hash)) {
                $errors[] = 'Current password is incorrect.';
            } elseif (!isStrongPassword($newPw)) {
                $errors[] = 'New password must be at least 8 characters with 1 uppercase and 1 number.';
            } elseif ($newPw !== $confirmPw) {
                $errors[] = 'Passwords do not match.';
            } else {
                $newHash = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $userId]);
                redirect('profile.php', 'Password changed successfully!', 'success');
            }
        } elseif ($action === 'delete_account') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            logoutUser();
            header('Location: login.php');
            exit();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container-sm" style="padding-top:20px; padding-bottom:40px;">
    <h2 data-aos="fade-up" style="margin-bottom:24px;">
        <i class="fa-solid fa-user" style="color:var(--color-secondary);"></i> Profile Settings
    </h2>
    
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="margin:0 0 16px;">
            <i class="fa-solid fa-exclamation-circle"></i>
            <span><?= sanitize($errors[0]) ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Profile Info -->
    <div class="card mb-3" data-aos="fade-up">
        <h3 class="card-title">Personal Information</h3>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-input" name="full_name" value="<?= sanitize($user['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" value="<?= sanitize($user['email']) ?>" disabled>
                <p class="form-hint">Email cannot be changed</p>
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" class="form-input" name="date_of_birth" value="<?= $user['date_of_birth'] ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Avg Cycle Length (days)</label>
                    <input type="number" class="form-input" name="avg_cycle_length" value="<?= $user['avg_cycle_length'] ?>" min="20" max="45">
                </div>
                <div class="form-group">
                    <label class="form-label">Avg Period Length (days)</label>
                    <input type="number" class="form-input" name="avg_period_length" value="<?= $user['avg_period_length'] ?>" min="2" max="10">
                </div>
            </div>
            <label class="form-check mb-2">
                <input type="checkbox" name="notification_enabled" <?= $user['notification_enabled'] ? 'checked' : '' ?>>
                Enable notifications
            </label>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Changes</button>
        </form>
    </div>
    
    <!-- Change Password -->
    <div class="card mb-3" data-aos="fade-up" data-aos-delay="100">
        <h3 class="card-title">Change Password</h3>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-input" name="current_password" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-input" name="new_password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-input" name="confirm_password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-key"></i> Change Password</button>
        </form>
    </div>
    
    <!-- Danger Zone -->
    <div class="card" data-aos="fade-up" data-aos-delay="200" style="border-color:var(--color-error);">
        <h3 class="card-title" style="color:var(--color-error);">Danger Zone</h3>
        <p style="font-size:14px; color:var(--text-secondary); margin-bottom:16px;">
            Deleting your account will permanently remove all your data including cycle history, mood logs, and chat history. This cannot be undone.
        </p>
        <form method="POST" id="deleteForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_account">
            <button type="button" class="btn btn-sm" style="background:var(--color-error); color:white;" onclick="confirmDelete()">
                <i class="fa-solid fa-trash"></i> Delete Account
            </button>
        </form>
    </div>
</div>

<script>
function confirmDelete() {
    showConfirm('Delete Account?', 'This will permanently delete ALL your data. This action cannot be undone.')
    .then(result => {
        if (result.isConfirmed) document.getElementById('deleteForm').submit();
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
