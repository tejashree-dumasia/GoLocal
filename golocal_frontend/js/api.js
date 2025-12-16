// API Service Functions
const API = {
    // Make authenticated request
    async request(url, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            ...AuthManager.getAuthHeader(),
            ...options.headers
        };

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // User APIs
    async register(username, email, password) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.REGISTER), {
            method: 'POST',
            body: JSON.stringify({ username, email, password })
        });
    },

    async login(email, password) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.LOGIN), {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    },

    async getUser(userId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.GET_USER) + `?user_id=${userId}`);
    },

    async updateUser(userId, userData) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.UPDATE_USER), {
            method: 'PUT',
            body: JSON.stringify({ user_id: userId, ...userData })
        });
    },

    async deleteUser(userId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.DELETE_USER), {
            method: 'DELETE',
            body: JSON.stringify({ user_id: userId })
        });
    },

    // Trip APIs
    async createTrip(tripData) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.CREATE_TRIP), {
            method: 'POST',
            body: JSON.stringify(tripData)
        });
    },

    async getTrips(userId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.GET_TRIPS) + `?user_id=${userId}`);
    },

    async getTrip(tripId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.GET_TRIP) + `?trip_id=${tripId}`);
    },

    async deleteTrip(tripId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.DELETE_TRIP), {
            method: 'DELETE',
            body: JSON.stringify({ trip_id: tripId })
        });
    },

    async inviteUser(tripId, email, isCoAdmin = false) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.INVITE_USER), {
            method: 'POST',
            body: JSON.stringify({ 
                trip_id: tripId, 
                email: email,
                is_co_admin: isCoAdmin ? 1 : 0
            })
        });
    },

    async getParticipants(tripId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.GET_PARTICIPANTS) + `?trip_id=${tripId}`);
    },

    async deleteParticipant(tripId, userId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.DELETE_PARTICIPANT), {
            method: 'DELETE',
            body: JSON.stringify({ 
                trip_id: tripId, 
                user_id: userId 
            })
        });
    },

    async setCoAdmin(tripId, userId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.SET_COADMIN), {
            method: 'POST',
            body: JSON.stringify({ 
                trip_id: tripId, 
                user_id: userId 
            })
        });
    },

    // Checklist APIs
    async createChecklistItem(tripId, itemName) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.CREATE_CHECKLIST), {
            method: 'POST',
            body: JSON.stringify({ 
                trip_id: tripId, 
                item_name: itemName 
            })
        });
    },

    async getChecklist(tripId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.GET_CHECKLIST) + `?trip_id=${tripId}`);
    },

    async updateChecklistItem(checklistId, isCompleted) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.UPDATE_CHECKLIST), {
            method: 'PUT',
            body: JSON.stringify({ 
                checklist_id: checklistId, 
                is_completed: isCompleted ? 1 : 0 
            })
        });
    },

    async deleteChecklistItem(checklistId) {
        return await this.request(getApiUrl(API_CONFIG.ENDPOINTS.DELETE_CHECKLIST), {
            method: 'DELETE',
            body: JSON.stringify({ checklist_id: checklistId })
        });
    },

    // Photo APIs
    async uploadPhoto(tripId, photoFile) {
        const formData = new FormData();
        formData.append('trip_id', tripId);
        formData.append('photo', photoFile);

        const headers = AuthManager.getAuthHeader();

        try {
            const response = await fetch(getApiUrl(API_CONFIG.ENDPOINTS.UPLOAD_PHOTO), {
                method: 'POST',
                headers: headers,
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Upload failed');
            }

            return data;
        } catch (error) {
            console.error('Photo Upload Error:', error);
            throw error;
        }
    }
};

// Utility Functions
const Utils = {
    // Format date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Format datetime
    formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Calculate duration between two dates
    calculateDuration(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 1) return '1 day';
        if (diffDays < 1) return 'Less than a day';
        return `${diffDays} days`;
    },

    // Get trip status
    getTripStatus(startDate, endDate) {
        const now = new Date();
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (now < start) return 'upcoming';
        if (now > end) return 'past';
        return 'ongoing';
    },

    // Show alert message
    showAlert(elementId, message, type = 'success') {
        const alertElement = document.getElementById(elementId);
        if (!alertElement) return;

        alertElement.className = `alert alert-${type}`;
        alertElement.textContent = message;
        alertElement.style.display = 'block';

        setTimeout(() => {
            alertElement.style.display = 'none';
        }, 5000);
    },

    // Get initials from name
    getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    },

    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR'
        }).format(amount);
    }
};

// Export for use in other files
window.API = API;
window.Utils = Utils;
