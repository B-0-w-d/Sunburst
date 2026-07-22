document.addEventListener('alpine:init', () => {
    Alpine.data('notificationDropdown', () => ({
        open: false,
        unreadCount: 0,
        notifications: [],

        init() {
            this.fetchData();
        },

        toggleDropdown() {
            this.open = !this.open;
            if (this.open) {
                this.fetchData();
            }
        },

        fetchData() {
            fetch('/api/notifications', {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('access_token')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                }
            });
        },

        markAsRead(id) {
            fetch(`/api/notifications/${id}/read`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('access_token')
                }
            }).then(() => this.fetchData());
        },

        markAllAsRead() {
            fetch('/api/notifications/read-all', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('access_token')
                }
            }).then(() => this.fetchData());
        }
    }));
});
