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
                redirect('profile.php', 'Profile updated!', 'success');
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
    <h2 data-aos="zoom-in-up" style="margin-bottom:24px;">
        <i class="fa-solid fa-user" style="color:var(--color-secondary);"></i> Profile Settings
    </h2>
    
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="margin:0 0 16px;">
            <i class="fa-solid fa-exclamation-circle"></i>
            <span><?= sanitize($errors[0]) ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Profile Info -->
    <div class="card mb-3" data-aos="zoom-in-up">
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
    <div class="card mb-3" data-aos="zoom-in-up" data-aos-delay="100">
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
    
    <!-- Theme Color Changer -->
    <div class="card mb-3" data-aos="zoom-in-up" data-aos-delay="150" id="theme-section">
        <h3 class="card-title">
            <i class="fa-solid fa-palette" style="color:var(--color-secondary);"></i>
            &nbsp;App Theme &amp; Color
        </h3>
        <p style="font-size:14px; color:var(--text-secondary); margin-bottom:20px;">
            Personalise your experience by choosing a colour palette that feels like <em>you</em>.
        </p>

        <!-- ✨ Light & Pastel -->
        <div class="theme-section-label">Light &amp; Pastel</div>
        <div class="theme-grid" id="themeGrid">
            <button class="theme-swatch" data-theme="pink" title="Blossom Pink" onclick="applyTheme('pink')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#FF7096,#B19CD9);"></span>
                <span class="swatch-label">Blossom</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="pookie" title="Pookie Pink" onclick="applyTheme('pookie')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#FFB3C6,#FFC8DD);"></span>
                <span class="swatch-label">Pookie</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="cottoncandy" title="Cotton Candy" onclick="applyTheme('cottoncandy')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#FF85A1,#85C1FF);"></span>
                <span class="swatch-label">Cotton Candy</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="peach" title="Peachy" onclick="applyTheme('peach')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#FFAD8A,#FFCBA4);"></span>
                <span class="swatch-label">Peachy</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="lavender" title="Lavender Dream" onclick="applyTheme('lavender')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#9B72CF,#C9A8E8);"></span>
                <span class="swatch-label">Lavender</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="rosegold" title="Rose Gold" onclick="applyTheme('rosegold')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#E8A0BF,#C67C9E);"></span>
                <span class="swatch-label">Rose Gold</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="mint" title="Mint Fresh" onclick="applyTheme('mint')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#10B981,#6EE7B7);"></span>
                <span class="swatch-label">Mint</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="ocean" title="Ocean Blue" onclick="applyTheme('ocean')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#0EA5E9,#38BDF8);"></span>
                <span class="swatch-label">Ocean</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="sunset" title="Sunset Glow" onclick="applyTheme('sunset')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#F97316,#FBBF24);"></span>
                <span class="swatch-label">Sunset</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="cherry" title="Cherry Red" onclick="applyTheme('cherry')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#EF4444,#FB7185);"></span>
                <span class="swatch-label">Cherry</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
        </div>

        <!-- 🌙 Dark Modes -->
        <div class="theme-section-label" style="margin-top:22px;">Dark Modes</div>
        <div class="theme-grid">
            <button class="theme-swatch" data-theme="darknite" title="Dark Night" onclick="applyTheme('darknite')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#1A1030,#FF7096);"></span>
                <span class="swatch-label">Dark Night</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="darkviolet" title="Dark Violet" onclick="applyTheme('darkviolet')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#0D0820,#7C3AED);"></span>
                <span class="swatch-label">Dark Violet</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="darkocean" title="Dark Ocean" onclick="applyTheme('darkocean')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#050F1A,#0EA5E9);"></span>
                <span class="swatch-label">Dark Ocean</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="darkrose" title="Dark Rose" onclick="applyTheme('darkrose')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#180810,#FB7185);"></span>
                <span class="swatch-label">Dark Rose</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
            <button class="theme-swatch" data-theme="darkforest" title="Dark Forest" onclick="applyTheme('darkforest')">
                <span class="swatch-preview" style="background:linear-gradient(135deg,#071A0E,#10B981);"></span>
                <span class="swatch-label">Dark Forest</span>
                <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
        </div>

        <p id="theme-saved-msg" style="display:none; margin-top:14px; font-size:13px; color:var(--color-success); font-weight:600;">
            <i class="fa-solid fa-circle-check"></i> Theme saved!
        </p>
    </div>

    <!-- Danger Zone -->
    <div class="card" data-aos="zoom-in-up" data-aos-delay="200" style="border-color:var(--color-error);">
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
/* ===== Theme definitions ===== */
const THEMES = {
    // ── Light & Pastel ──────────────────────────────
    pink:{
        '--color-primary':'#FF7096','--color-primary-dark':'#E8567F','--color-primary-light':'#FFF0F5',
        '--color-secondary':'#B19CD9','--color-secondary-dark':'#9B8EC0','--color-secondary-light':'#F8F4FF',
        '--border-light':'rgba(255,112,150,0.15)',
        '--shadow-sm':'0 4px 12px rgba(255,112,150,0.08)','--shadow-md':'0 8px 24px rgba(255,112,150,0.12)',
        '--shadow-lg':'0 16px 48px rgba(177,156,217,0.18)','--shadow-glow':'0 12px 36px rgba(255,112,150,0.3)',
        '--bg-body':'#FDF9FB','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE',
    },
    pookie:{
        '--color-primary':'#FFB3C6','--color-primary-dark':'#FF85A1','--color-primary-light':'#FFF0F8',
        '--color-secondary':'#FFC8DD','--color-secondary-dark':'#FFB3C6','--color-secondary-light':'#FFF5FA',
        '--border-light':'rgba(255,179,198,0.25)',
        '--shadow-sm':'0 4px 12px rgba(255,179,198,0.15)','--shadow-md':'0 8px 24px rgba(255,179,198,0.2)',
        '--shadow-lg':'0 16px 48px rgba(255,200,221,0.25)','--shadow-glow':'0 12px 36px rgba(255,179,198,0.4)',
        '--bg-body':'#FFF8FA','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#3D1F35','--text-secondary':'#6B4260','--text-muted':'#B590AA',
    },
    cottoncandy:{
        '--color-primary':'#FF85A1','--color-primary-dark':'#FF6B8E','--color-primary-light':'#FFF0F5',
        '--color-secondary':'#85C1FF','--color-secondary-dark':'#5BABFF','--color-secondary-light':'#EBF5FF',
        '--border-light':'rgba(255,133,161,0.2)',
        '--shadow-sm':'0 4px 12px rgba(255,133,161,0.1)','--shadow-md':'0 8px 24px rgba(133,193,255,0.12)',
        '--shadow-lg':'0 16px 48px rgba(255,133,161,0.18)','--shadow-glow':'0 12px 36px rgba(255,133,161,0.35)',
        '--bg-body':'#FFF8FB','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2A1830','--text-secondary':'#574060','--text-muted':'#A080A8',
    },
    peach:{
        '--color-primary':'#FFAD8A','--color-primary-dark':'#FF8C63','--color-primary-light':'#FFF5F0',
        '--color-secondary':'#FFCBA4','--color-secondary-dark':'#FFAD8A','--color-secondary-light':'#FFF8F2',
        '--border-light':'rgba(255,173,138,0.2)',
        '--shadow-sm':'0 4px 12px rgba(255,173,138,0.1)','--shadow-md':'0 8px 24px rgba(255,173,138,0.15)',
        '--shadow-lg':'0 16px 48px rgba(255,203,164,0.2)','--shadow-glow':'0 12px 36px rgba(255,173,138,0.35)',
        '--bg-body':'#FFFAF7','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#3A1F10','--text-secondary':'#6B4030','--text-muted':'#B08070',
    },
    lavender:{
        '--color-primary':'#9B72CF','--color-primary-dark':'#7C52B0','--color-primary-light':'#F3EEFF',
        '--color-secondary':'#C9A8E8','--color-secondary-dark':'#A880CC','--color-secondary-light':'#FAF5FF',
        '--border-light':'rgba(155,114,207,0.15)',
        '--shadow-sm':'0 4px 12px rgba(155,114,207,0.08)','--shadow-md':'0 8px 24px rgba(155,114,207,0.12)',
        '--shadow-lg':'0 16px 48px rgba(201,168,232,0.18)','--shadow-glow':'0 12px 36px rgba(155,114,207,0.3)',
        '--bg-body':'#FAF8FE','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE',
    },
    rosegold:{
        '--color-primary':'#C67C9E','--color-primary-dark':'#A85D82','--color-primary-light':'#FDF0F6',
        '--color-secondary':'#E8A0BF','--color-secondary-dark':'#C67C9E','--color-secondary-light':'#FDEDF6',
        '--border-light':'rgba(198,124,158,0.15)',
        '--shadow-sm':'0 4px 12px rgba(198,124,158,0.08)','--shadow-md':'0 8px 24px rgba(198,124,158,0.12)',
        '--shadow-lg':'0 16px 48px rgba(232,160,191,0.18)','--shadow-glow':'0 12px 36px rgba(198,124,158,0.3)',
        '--bg-body':'#FEF9FC','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE',
    },
    mint:{
        '--color-primary':'#10B981','--color-primary-dark':'#059669','--color-primary-light':'#ECFDF5',
        '--color-secondary':'#6EE7B7','--color-secondary-dark':'#34D399','--color-secondary-light':'#D1FAE5',
        '--border-light':'rgba(16,185,129,0.15)',
        '--shadow-sm':'0 4px 12px rgba(16,185,129,0.08)','--shadow-md':'0 8px 24px rgba(16,185,129,0.12)',
        '--shadow-lg':'0 16px 48px rgba(110,231,183,0.18)','--shadow-glow':'0 12px 36px rgba(16,185,129,0.3)',
        '--bg-body':'#F5FEFA','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#0A2A1E','--text-secondary':'#1E5040','--text-muted':'#70A890',
    },
    ocean:{
        '--color-primary':'#0EA5E9','--color-primary-dark':'#0284C7','--color-primary-light':'#F0F9FF',
        '--color-secondary':'#38BDF8','--color-secondary-dark':'#0EA5E9','--color-secondary-light':'#E0F4FF',
        '--border-light':'rgba(14,165,233,0.15)',
        '--shadow-sm':'0 4px 12px rgba(14,165,233,0.08)','--shadow-md':'0 8px 24px rgba(14,165,233,0.12)',
        '--shadow-lg':'0 16px 48px rgba(56,189,248,0.18)','--shadow-glow':'0 12px 36px rgba(14,165,233,0.3)',
        '--bg-body':'#F8FCFF','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#0A1F30','--text-secondary':'#1A4060','--text-muted':'#6090A8',
    },
    sunset:{
        '--color-primary':'#F97316','--color-primary-dark':'#EA6504','--color-primary-light':'#FFF7ED',
        '--color-secondary':'#FBBF24','--color-secondary-dark':'#F59E0B','--color-secondary-light':'#FFFBEB',
        '--border-light':'rgba(249,115,22,0.15)',
        '--shadow-sm':'0 4px 12px rgba(249,115,22,0.08)','--shadow-md':'0 8px 24px rgba(249,115,22,0.12)',
        '--shadow-lg':'0 16px 48px rgba(251,191,36,0.18)','--shadow-glow':'0 12px 36px rgba(249,115,22,0.3)',
        '--bg-body':'#FFFAF5','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2A1505','--text-secondary':'#5A3010','--text-muted':'#A07040',
    },
    cherry:{
        '--color-primary':'#EF4444','--color-primary-dark':'#DC2626','--color-primary-light':'#FEF2F2',
        '--color-secondary':'#FB7185','--color-secondary-dark':'#F43F5E','--color-secondary-light':'#FFF1F2',
        '--border-light':'rgba(239,68,68,0.15)',
        '--shadow-sm':'0 4px 12px rgba(239,68,68,0.08)','--shadow-md':'0 8px 24px rgba(239,68,68,0.12)',
        '--shadow-lg':'0 16px 48px rgba(251,113,133,0.18)','--shadow-glow':'0 12px 36px rgba(239,68,68,0.3)',
        '--bg-body':'#FFF8F8','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2A0808','--text-secondary':'#5A1818','--text-muted':'#A06060',
    },
    // ── Dark Modes ───────────────────────────────────
    darknite:{
        '--color-primary':'#FF7096','--color-primary-dark':'#E8567F','--color-primary-light':'#2A1525',
        '--color-secondary':'#B19CD9','--color-secondary-dark':'#9B8EC0','--color-secondary-light':'#1E1530',
        '--border-light':'rgba(255,112,150,0.2)',
        '--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)',
        '--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(255,112,150,0.4)',
        '--bg-body':'#0D0A14','--bg-card':'rgba(28,20,42,0.97)',
        '--text-primary':'#F5F0FF','--text-secondary':'#E2D5F3','--text-muted':'#A799B7',
    },
    darkviolet:{
        '--color-primary':'#A78BFA','--color-primary-dark':'#7C3AED','--color-primary-light':'#1A1030',
        '--color-secondary':'#C084FC','--color-secondary-dark':'#A855F7','--color-secondary-light':'#14082A',
        '--border-light':'rgba(167,139,250,0.2)',
        '--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)',
        '--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(167,139,250,0.4)',
        '--bg-body':'#0A0514','--bg-card':'rgba(20,10,35,0.97)',
        '--text-primary':'#F0EAFF','--text-secondary':'#DCD0F8','--text-muted':'#9D8DBF',
    },
    darkocean:{
        '--color-primary':'#38BDF8','--color-primary-dark':'#0EA5E9','--color-primary-light':'#0A2030',
        '--color-secondary':'#67E8F9','--color-secondary-dark':'#22D3EE','--color-secondary-light':'#082028',
        '--border-light':'rgba(56,189,248,0.2)',
        '--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)',
        '--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(56,189,248,0.4)',
        '--bg-body':'#030D18','--bg-card':'rgba(8,20,38,0.97)',
        '--text-primary':'#E8F5FF','--text-secondary':'#C0D8F0','--text-muted':'#80A0C0',
    },
    darkrose:{
        '--color-primary':'#FB7185','--color-primary-dark':'#F43F5E','--color-primary-light':'#280A14',
        '--color-secondary':'#FDA4AF','--color-secondary-dark':'#FB7185','--color-secondary-light':'#200810',
        '--border-light':'rgba(251,113,133,0.2)',
        '--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)',
        '--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(251,113,133,0.4)',
        '--bg-body':'#130508','--bg-card':'rgba(30,8,16,0.97)',
        '--text-primary':'#FFF0F3','--text-secondary':'#F0C0C8','--text-muted':'#B08090',
    },
    darkforest:{
        '--color-primary':'#34D399','--color-primary-dark':'#10B981','--color-primary-light':'#092418',
        '--color-secondary':'#6EE7B7','--color-secondary-dark':'#34D399','--color-secondary-light':'#071A10',
        '--border-light':'rgba(52,211,153,0.2)',
        '--shadow-sm':'0 4px 12px rgba(0,0,0,0.4)','--shadow-md':'0 8px 24px rgba(0,0,0,0.5)',
        '--shadow-lg':'0 16px 48px rgba(0,0,0,0.6)','--shadow-glow':'0 12px 36px rgba(52,211,153,0.4)',
        '--bg-body':'#030E08','--bg-card':'rgba(7,22,14,0.97)',
        '--text-primary':'#E8FFF5','--text-secondary':'#B0E0C8','--text-muted':'#709080',
    },
    // kept for backward compat
    midnight:{
        '--color-primary':'#4F46E5','--color-primary-dark':'#3730A3','--color-primary-light':'#EEF2FF',
        '--color-secondary':'#7C3AED','--color-secondary-dark':'#6D28D9','--color-secondary-light':'#F5F3FF',
        '--border-light':'rgba(79,70,229,0.15)',
        '--shadow-sm':'0 4px 12px rgba(79,70,229,0.08)','--shadow-md':'0 8px 24px rgba(79,70,229,0.12)',
        '--shadow-lg':'0 16px 48px rgba(124,58,237,0.18)','--shadow-glow':'0 12px 36px rgba(79,70,229,0.3)',
        '--bg-body':'#F8F8FF','--bg-card':'rgba(255,255,255,0.95)',
        '--text-primary':'#2B1E38','--text-secondary':'#574B66','--text-muted':'#9D8CAE',
    },
};

function applyTheme(name) {
    const vars = THEMES[name];
    if (!vars) return;
    const root = document.documentElement;
    Object.entries(vars).forEach(([k, v]) => root.style.setProperty(k, v));
    localStorage.setItem('him_theme', name);
    document.querySelectorAll('.theme-swatch').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.theme === name);
    });
    const msg = document.getElementById('theme-saved-msg');
    msg.style.display = 'block';
    clearTimeout(window._themeMsgTimer);
    window._themeMsgTimer = setTimeout(() => { msg.style.display = 'none'; }, 2500);
}

(function() {
    const saved = localStorage.getItem('him_theme') || 'pink';
    applyTheme(saved);
})();

function confirmDelete() {
    showConfirm('Delete Account?', 'This will permanently delete ALL your data. This action cannot be undone.')
    .then(result => {
        if (result.isConfirmed) document.getElementById('deleteForm').submit();
    });
}

</script>

<?php require_once 'includes/footer.php'; ?>
