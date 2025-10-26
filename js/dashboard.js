document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const menuLinks = document.querySelectorAll('.menu-link');

    // Menü toggle fonksiyonu (mobil için)
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        // Mobilde menü dışına tıklandığında kapat
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnMenuToggle = menuToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnMenuToggle && sidebar.classList.contains('open')) {
                // Sadece mobilde kapat
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    // Menü linkleri için aktif durum yönetimi
    if (menuLinks) {
        menuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Hash link ise preventDefault
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                }

                // Aktif sınıfı kaldır
                menuLinks.forEach(l => l.classList.remove('active'));
                
                // Tıklanan linke aktif sınıfını ekle
                this.classList.add('active');
            });
        });
    }

    // Scroll animasyonları
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Info kartlarına scroll animasyonu ekle
    const infoCards = document.querySelectorAll('.info-card');
    infoCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `all 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // Navbar scroll efekti
    let lastScroll = 0;
    const navbar = document.querySelector('.skeuomorphic-navbar');

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            navbar.style.boxShadow = 'inset 0 1px 2px rgba(255, 255, 255, 0.9), 0 4px 8px rgba(0, 0, 0, 0.15)';
        } else {
            navbar.style.boxShadow = 'inset 0 1px 2px rgba(255, 255, 255, 0.9), 0 8px 16px rgba(0, 0, 0, 0.25)';
        }
        
        lastScroll = currentScroll;
    });

    // Buton basma animasyonları
    const buttons = document.querySelectorAll('.logout-btn, .navbar-logo');
    buttons.forEach(button => {
        button.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.95)';
        });

        button.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Apply theme settings on page load
    applyThemeSettings();
});

// Apply theme settings from localStorage
function applyThemeSettings() {
    // Apply dark mode
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
    
    // Apply theme color
    const themeColor = localStorage.getItem('themeColor') || 'blue';
    const colorMap = {
        blue: { primary: '#5181b8', dark: '#3d6ba3' },
        green: { primary: '#4caf50', dark: '#3ba43f' },
        purple: { primary: '#9c27b0', dark: '#7b1fa2' },
        orange: { primary: '#ff9800', dark: '#f57c00' },
        red: { primary: '#f44336', dark: '#d32f2f' },
        teal: { primary: '#009688', dark: '#00796b' }
    };
    
    const colors = colorMap[themeColor] || colorMap.blue;
    document.documentElement.style.setProperty('--primary-color', colors.primary);
    document.documentElement.style.setProperty('--primary-dark', colors.dark);
}

