// Like button functionality
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    // Like button functionality with animation and ARIA
    const likeButtons = document.querySelectorAll('.like-button');

    likeButtons.forEach(button => {
        button.setAttribute('role', 'button');
        button.tabIndex = 0;

        const likesCountEl = button.querySelector('.likes-count');

        function updateCount(newCount, action) {
            if (!likesCountEl) return;
            const safeCount = Number.isFinite(newCount) ? Math.max(0, newCount) : parseInt(likesCountEl.textContent) || 0;
            likesCountEl.textContent = safeCount;
            button.setAttribute('aria-pressed', action === 'liked' ? 'true' : 'false');
        }

        function animateLike() {
            button.classList.add('animating');
            window.setTimeout(() => button.classList.remove('animating'), 400);
        }

        async function toggleLike() {
            const postId = button.dataset.postId;
            if (!postId) return;

            try {
                const res = await fetch('includes/like_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${encodeURIComponent(postId)}&csrf_token=${encodeURIComponent(csrfToken)}`
                });
                const data = await res.json();
                if (data.success) {
                    if (data.action === 'liked') {
                        button.classList.add('liked');
                        updateCount(parseInt(data.likes), 'liked');
                    } else {
                        button.classList.remove('liked');
                        updateCount(parseInt(data.likes), 'unliked');
                    }
                    animateLike();
                }
            } catch (err) {
                console.error('Like error', err);
            }
        }

        button.addEventListener('click', toggleLike);
        button.addEventListener('keydown', function(e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleLike(); } });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (!target) return; // allow normal behavior if no target
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Simple client-side form validation (adds 'error' class)
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let blocked = false;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    blocked = true;
                    field.classList.add('error');
                    field.addEventListener('input', () => field.classList.remove('error'), { once: true });
                }
            });
            if (blocked) e.preventDefault();
        });
    });

    // Mobile menu toggle with outside-click and Escape handling
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const navLinks = document.querySelector('.nav-links');

    if (mobileMenuButton && navLinks) {
        mobileMenuButton.setAttribute('aria-expanded', 'false');
        mobileMenuButton.addEventListener('click', () => {
            const isActive = navLinks.classList.toggle('active');
            mobileMenuButton.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            if (isActive) document.body.classList.add('nav-open'); else document.body.classList.remove('nav-open');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navLinks.classList.contains('active')) return;
            if (navLinks.contains(e.target) || mobileMenuButton.contains(e.target)) return;
            navLinks.classList.remove('active');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('nav-open');
        });

        // Close on Escape
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { navLinks.classList.remove('active'); mobileMenuButton.setAttribute('aria-expanded', 'false'); document.body.classList.remove('nav-open'); } });

        // Close when a link is clicked (helpful for single-page anchors)
        navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
            navLinks.classList.remove('active');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('nav-open');
        }));
    }

    // Theme toggle (persist in localStorage)
    const themeToggleButtons = document.querySelectorAll('.theme-toggle');
    function setToggleIcon(btn, isDark){
        try{
            // prefer pseudo-element in CSS, but set an accessible label/icon too
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            btn.textContent = isDark ? '🌙' : '☀';
        } catch(e){}
    }

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.documentElement.classList.add('dark-theme');
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.documentElement.classList.remove('dark-theme');
        }
        try { localStorage.setItem('theme', theme); } catch (e) {}
        // update button state/icon
        themeToggleButtons.forEach(btn => {
            btn.classList.toggle('is-dark', theme === 'dark');
            setToggleIcon(btn, theme === 'dark');
        });
    }

    // init from localStorage or prefers-color-scheme
    let saved = null;
    try { saved = localStorage.getItem('theme'); } catch(e){}
    if (!saved) {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) saved = 'dark';
        else saved = 'light';
    }
    applyTheme(saved || 'light');

    themeToggleButtons.forEach(btn => btn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
    }));
});