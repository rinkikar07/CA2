/**
 * HIM - Global JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, once: true, offset: 50 });
    }
    
    // ===== Notification Dropdown =====
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            document.getElementById('profileDropdown')?.classList.remove('show');
        });
    }
    
    // ===== Profile Dropdown =====
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
            document.getElementById('notifDropdown')?.classList.remove('show');
        });
    }
    
    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.notif-dropdown, .profile-dropdown').forEach(d => d.classList.remove('show'));
    });
    
    // ===== Mobile Menu =====
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenuOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', () => {
            mobileMenuOverlay.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', (e) => {
            if (e.target === mobileMenuOverlay) {
                mobileMenuOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
    
    // ===== Flash Message Auto-dismiss =====
    const flash = document.getElementById('flashMessage');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    }
    
    // ===== Form Validation =====
    window.validateEmail = function(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };
    
    window.validatePassword = function(password) {
        return password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password);
    };
    
    // ===== CSRF Token Helper =====
    window.getCSRFToken = function() {
        const meta = document.querySelector('input[name="csrf_token"]');
        return meta ? meta.value : '';
    };
    
    // ===== Fetch Helper =====
    window.apiCall = async function(url, data = {}) {
        try {
            const formData = new FormData();
            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, error: 'Network error. Please try again.' };
        }
    };
    
    // ===== SweetAlert Helpers =====
    window.showSuccess = function(title, text) {
        return Swal.fire({ icon: 'success', title, text, confirmButtonColor: '#E8567F' });
    };
    window.showError = function(title, text) {
        return Swal.fire({ icon: 'error', title, text, confirmButtonColor: '#E8567F' });
    };
    window.showConfirm = function(title, text) {
        return Swal.fire({
            icon: 'warning', title, text,
            showCancelButton: true,
            confirmButtonColor: '#E8567F',
            cancelButtonColor: '#8E7FA0',
            confirmButtonText: 'Yes, do it!'
        });
    };
    
    // ===== Mark Notifications Read =====
    const markAllRead = document.getElementById('markAllRead');
    if (markAllRead) {
        markAllRead.addEventListener('click', async (e) => {
            e.preventDefault();
            await apiCall('api/mood_handler.php', { action: 'mark_notifications_read' });
            const badge = document.querySelector('.notif-badge');
            if (badge) badge.remove();
        });
    }
});
