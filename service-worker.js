// Service Worker for Push Notifications

self.addEventListener('push', function(event) {
    let data = {};
    
    if (event.data) {
        data = event.data.json();
    }
    
    const options = {
        body: data.message || 'Yeni duyuru var!',
        icon: '/icon.png',
        badge: '/badge.png',
        vibrate: [200, 100, 200],
        tag: 'announcement',
        requireInteraction: true
    };
    
    event.waitUntil(
        self.registration.showNotification('Duyuru', options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/announcements_view.php')
    );
});


