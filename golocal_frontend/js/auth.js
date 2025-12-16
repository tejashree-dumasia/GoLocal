// Authentication Manager
const AuthManager = {
    // Save JWT token
    saveToken(token) {
        localStorage.setItem('jwt_token', token);
    },

    // Get JWT token
    getToken() {
        return localStorage.getItem('jwt_token');
    },

    // Remove JWT token
    removeToken() {
        localStorage.removeItem('jwt_token');
    },

    // Save user data
    saveUser(userData) {
        localStorage.setItem('user_data', JSON.stringify(userData));
    },

    // Get user data
    getUser() {
        const userData = localStorage.getItem('user_data');
        return userData ? JSON.parse(userData) : null;
    },

    // Remove user data
    removeUser() {
        localStorage.removeItem('user_data');
    },

    // Check if user is authenticated
    isAuthenticated() {
        return !!this.getToken();
    },

    // Logout
    logout() {
        this.removeToken();
        this.removeUser();
        window.location.href = 'index.html';
    },

    // Redirect to dashboard if authenticated
    redirectIfAuthenticated() {
        if (this.isAuthenticated()) {
            window.location.href = 'dashboard.html';
        }
    },

    // Require authentication
    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = 'index.html';
        }
    },

    // Get authorization header
    getAuthHeader() {
        const token = this.getToken();
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    }
};

// Export for use in other files
window.AuthManager = AuthManager;
