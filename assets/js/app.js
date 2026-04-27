/**
 * HIM - Global JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Hide page loader
    window.addEventListener('load', () => {
        const loader = document.getElementById('pageLoader');
        if (loader) loader.classList.add('hidden');
    });

    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 800, easing: 'ease-out-back', once: false, offset: 50 });
    }

    // ===== Sidebar Logic =====
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebarClose   = document.getElementById('sidebarClose');
    const sidebarMenu    = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebarMenu) {
        sidebarToggle.addEventListener('click', () => {
            sidebarMenu.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        const closeSidebar = () => {
            sidebarMenu.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // ===== Parallax Logic =====
    const parallaxElements = document.querySelectorAll('.parallax');
    if (parallaxElements.length > 0) {
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            parallaxElements.forEach(el => {
                const speed = el.getAttribute('data-parallax-speed') || 0.3;
                el.style.transform = `translateY(${scrollY * speed}px)`;
            });
        }, { passive: true });
    }
    
    // ===== Text Reveal Logic =====
    const revealElements = document.querySelectorAll('.text-reveal');
    if (revealElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, { threshold: 0.1 });
        
        revealElements.forEach(el => observer.observe(el));
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
