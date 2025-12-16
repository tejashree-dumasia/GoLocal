// API Configuration
const API_CONFIG = {
    BASE_URL: 'http://127.0.0.1:8000/api',
    ENDPOINTS: {
        // User endpoints
        REGISTER: '/users/register',
        LOGIN: '/users/login',
        GET_USER: '/users/read_single',
        UPDATE_USER: '/users/update',
        DELETE_USER: '/users/delete',
        UPLOAD_PROFILE: '/users/upload_profile',
        
        // Trip endpoints
        CREATE_TRIP: '/trips/create',
        GET_TRIPS: '/trips/read',
        GET_TRIP: '/trips/read_single',
        DELETE_TRIP: '/trips/delete',
        INVITE_USER: '/trips/invite',
        GET_PARTICIPANTS: '/trips/read_participants',
        DELETE_PARTICIPANT: '/trips/delete_participant',
        SET_COADMIN: '/trips/set_coadmin',
        
        // Checklist endpoints
        CREATE_CHECKLIST: '/trips/checklist_create',
        GET_CHECKLIST: '/trips/checklist_read',
        UPDATE_CHECKLIST: '/trips/checklist_update',
        DELETE_CHECKLIST: '/trips/checklist_delete',
        
        // Photo endpoints
        UPLOAD_PHOTO: '/photos/upload'
    }
};

// Helper function to get full API URL
function getApiUrl(endpoint) {
    return API_CONFIG.BASE_URL + endpoint;
}

// Export for use in other files
window.API_CONFIG = API_CONFIG;
window.getApiUrl = getApiUrl;
