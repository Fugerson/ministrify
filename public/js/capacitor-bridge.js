/**
 * Capacitor Native Bridge
 * Handles native functionality when running inside Capacitor Android shell
 */
(function() {
    if (!window.Capacitor || !window.Capacitor.isNativePlatform || !window.Capacitor.isNativePlatform()) {
        return;
    }

    const { Capacitor, CapacitorPushNotifications, CapacitorStatusBar, CapacitorKeyboard } = window;

    // ─── Status Bar ───
    function setupStatusBar() {
        const StatusBar = Capacitor.Plugins.StatusBar;
        if (!StatusBar) return;

        const isDark = document.documentElement.classList.contains('dark');
        StatusBar.setStyle({ style: isDark ? 'DARK' : 'LIGHT' }).catch(() => {});
        StatusBar.setBackgroundColor({ color: isDark ? '#111827' : '#ffffff' }).catch(() => {});

        // Watch for dark mode changes
        const observer = new MutationObserver(() => {
            const dark = document.documentElement.classList.contains('dark');
            StatusBar.setStyle({ style: dark ? 'DARK' : 'LIGHT' }).catch(() => {});
            StatusBar.setBackgroundColor({ color: dark ? '#111827' : '#ffffff' }).catch(() => {});
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ─── Back Button ───
    function setupBackButton() {
        document.addEventListener('backbutton', (e) => {
            e.preventDefault();

            // Close any open Alpine.js modals/dropdowns first
            const openModal = document.querySelector('[x-show]:not([style*="display: none"])[role="dialog"]');
            if (openModal) {
                openModal.dispatchEvent(new Event('close'));
                return;
            }

            // Close sidebar if open on mobile
            const sidebar = document.querySelector('.sidebar-open, [x-data*="sidebarOpen"] [x-show="sidebarOpen"]:not([style*="display: none"])');
            if (sidebar) {
                window.dispatchEvent(new CustomEvent('close-sidebar'));
                return;
            }

            // Navigate back or go to dashboard
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/dashboard';
            }
        });
    }

    // ─── Push Notifications (FCM) ───
    function setupPushNotifications() {
        const PushNotifications = Capacitor.Plugins.PushNotifications;
        if (!PushNotifications) return;

        // Request permission
        PushNotifications.requestPermissions().then(result => {
            if (result.receive === 'granted') {
                PushNotifications.register();
            }
        }).catch(() => {});

        // On registration success — send token to server
        PushNotifications.addListener('registration', (token) => {
            sendTokenToServer(token.value);
        });

        // On registration error
        PushNotifications.addListener('registrationError', (error) => {
            console.warn('Push registration error:', error);
        });

        // Handle received notification (foreground)
        PushNotifications.addListener('pushNotificationReceived', (notification) => {
            // Show in-app notification toast
            showInAppNotification(notification);
        });

        // Handle notification tap (opens app)
        PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
            const data = action.notification.data;
            if (data && data.url) {
                window.location.href = data.url;
            }
        });
    }

    function sendTokenToServer(token) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) return;

        fetch('/api/pwa/device-token', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                token: token,
                platform: 'android',
                device_name: navigator.userAgent
            })
        }).catch(() => {});
    }

    function showInAppNotification(notification) {
        const container = document.createElement('div');
        container.className = 'fixed top-4 right-4 left-4 md:left-auto md:w-96 z-[9999] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 transform transition-all duration-300';
        container.style.transform = 'translateY(-100%)';
        container.style.opacity = '0';

        container.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">${notification.title || 'Ministrify'}</p>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-0.5">${notification.body || ''}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        `;

        document.body.appendChild(container);
        requestAnimationFrame(() => {
            container.style.transform = 'translateY(0)';
            container.style.opacity = '1';
        });

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            container.style.transform = 'translateY(-100%)';
            container.style.opacity = '0';
            setTimeout(() => container.remove(), 300);
        }, 5000);
    }

    // ─── Keyboard ───
    function setupKeyboard() {
        const Keyboard = Capacitor.Plugins.Keyboard;
        if (!Keyboard) return;

        Keyboard.addListener('keyboardWillShow', (info) => {
            document.body.style.paddingBottom = info.keyboardHeight + 'px';
            // Scroll active element into view
            setTimeout(() => {
                document.activeElement?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        });

        Keyboard.addListener('keyboardWillHide', () => {
            document.body.style.paddingBottom = '';
        });
    }

    // ─── Pull to Refresh ───
    function setupPullToRefresh() {
        let startY = 0;
        let pulling = false;
        const threshold = 80;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
                pulling = true;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!pulling) return;
            const diff = e.touches[0].clientY - startY;
            if (diff > threshold && window.scrollY === 0) {
                pulling = false;
                window.location.reload();
            }
        }, { passive: true });

        document.addEventListener('touchend', () => {
            pulling = false;
        }, { passive: true });
    }

    // ─── External Links ───
    function setupExternalLinks() {
        const Browser = Capacitor.Plugins.Browser;
        if (!Browser) return;

        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href) return;

            // Open external links in in-app browser
            if (href.startsWith('http') && !href.includes('ministrify.app')) {
                e.preventDefault();
                Browser.open({ url: href });
            }
        });
    }

    // ─── Hide PWA Install Banner ───
    function hidePwaInstallBanner() {
        localStorage.setItem('pwa-installed', 'true');
    }

    // ─── Initialize ───
    document.addEventListener('DOMContentLoaded', () => {
        setupStatusBar();
        setupBackButton();
        setupPushNotifications();
        setupKeyboard();
        setupPullToRefresh();
        setupExternalLinks();
        hidePwaInstallBanner();
    });
})();
