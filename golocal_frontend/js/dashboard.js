// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Require authentication
    AuthManager.requireAuth();

    const user = AuthManager.getUser();
    if (!user) {
        AuthManager.logout();
        return;
    }

    // Elements
    const tripsGrid = document.getElementById('tripsGrid');
    const emptyState = document.getElementById('emptyState');
    const loadingState = document.getElementById('loadingState');
    const createTripBtn = document.getElementById('createTripBtn');
    const createTripModal = document.getElementById('createTripModal');
    const createTripForm = document.getElementById('createTripForm');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    const logoutBtn = document.getElementById('logoutBtn');
    const modalClose = document.querySelector('#createTripModal .modal-close');

    // Set user info
    document.getElementById('dropdownUsername').textContent = user.username;
    document.getElementById('dropdownEmail').textContent = user.email;

    // User menu toggle
    userMenuBtn.addEventListener('click', () => {
        userDropdown.classList.toggle('show');
    });

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

    // Logout
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        AuthManager.logout();
    });

    // Modal controls
    createTripBtn.addEventListener('click', () => {
        createTripModal.style.display = 'flex';
    });

    modalClose.addEventListener('click', () => {
        createTripModal.style.display = 'none';
        createTripForm.reset();
    });

    createTripModal.addEventListener('click', (e) => {
        if (e.target === createTripModal) {
            createTripModal.style.display = 'none';
            createTripForm.reset();
        }
    });

    // Filter tabs
    const filterTabs = document.querySelectorAll('.tab-btn');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const filter = tab.dataset.filter;
            filterTrips(filter);
        });
    });

    // Load trips
    let allTrips = [];

    async function loadTrips() {
        try {
            loadingState.style.display = 'block';
            tripsGrid.style.display = 'none';
            emptyState.style.display = 'none';

            const response = await API.getTrips(user.id);
            
            if (response.trips && response.trips.length > 0) {
                allTrips = response.trips;
                displayTrips(allTrips);
                updateStats(allTrips);
                loadingState.style.display = 'none';
            } else {
                loadingState.style.display = 'none';
                emptyState.style.display = 'block';
                updateStats([]);
            }
        } catch (error) {
            console.error('Error loading trips:', error);
            loadingState.style.display = 'none';
            emptyState.style.display = 'block';
            updateStats([]);
        }
    }

    // Display trips
    function displayTrips(trips) {
        tripsGrid.innerHTML = '';
        tripsGrid.style.display = 'grid';

        trips.forEach(trip => {
            const tripCard = createTripCard(trip);
            tripsGrid.appendChild(tripCard);
        });
    }

    // Create trip card
    function createTripCard(trip) {
        const card = document.createElement('div');
        card.className = 'trip-card';
        card.onclick = () => {
            window.location.href = `trip-detail.html?id=${trip.trip_id}`;
        };

        const status = Utils.getTripStatus(trip.start_datetime, trip.end_datetime);
        const statusClass = status === 'upcoming' ? 'upcoming' : status === 'past' ? 'past' : 'ongoing';

        card.innerHTML = `
            <div class="trip-card-image">
                <i class="fas fa-map-marked-alt"></i>
                <span class="trip-status-badge">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
            </div>
            <div class="trip-card-content">
                <h3 class="trip-card-title">${trip.trip_name}</h3>
                <div class="trip-card-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${trip.location}</span>
                </div>
                <div class="trip-card-dates">
                    <i class="fas fa-calendar"></i>
                    <span>${Utils.formatDate(trip.start_datetime)} - ${Utils.formatDate(trip.end_datetime)}</span>
                </div>
                <div class="trip-card-footer">
                    <div class="trip-participants">
                        <i class="fas fa-users"></i>
                        <span>${trip.participant_count || 0} participants</span>
                    </div>
                    <div class="trip-cost">
                        ${trip.estimated_cost ? Utils.formatCurrency(trip.estimated_cost) : 'N/A'}
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    // Filter trips
    function filterTrips(filter) {
        let filteredTrips = allTrips;

        if (filter === 'upcoming') {
            filteredTrips = allTrips.filter(trip => 
                Utils.getTripStatus(trip.start_datetime, trip.end_datetime) === 'upcoming'
            );
        } else if (filter === 'past') {
            filteredTrips = allTrips.filter(trip => 
                Utils.getTripStatus(trip.start_datetime, trip.end_datetime) === 'past'
            );
        }

        if (filteredTrips.length > 0) {
            displayTrips(filteredTrips);
            emptyState.style.display = 'none';
        } else {
            tripsGrid.style.display = 'none';
            emptyState.style.display = 'block';
        }
    }

    // Update stats
    function updateStats(trips) {
        const totalTrips = trips.length;
        const upcomingTrips = trips.filter(trip => 
            Utils.getTripStatus(trip.start_datetime, trip.end_datetime) === 'upcoming'
        ).length;
        
        // Calculate total participants (would need API call for accurate count)
        const totalParticipants = trips.reduce((sum, trip) => sum + (parseInt(trip.participant_count) || 0), 0);

        document.getElementById('totalTrips').textContent = totalTrips;
        document.getElementById('upcomingTrips').textContent = upcomingTrips;
        document.getElementById('totalParticipants').textContent = totalParticipants;
        document.getElementById('totalPhotos').textContent = '0'; // Would need separate API call
    }

    // Create trip form submission
    createTripForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const tripName = document.getElementById('tripName').value;
        const location = document.getElementById('location').value;
        const description = document.getElementById('description').value;
        const startDatetime = document.getElementById('startDatetime').value;
        const endDatetime = document.getElementById('endDatetime').value;
        const estimatedCost = document.getElementById('estimatedCost').value;

        const submitBtn = createTripForm.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        // Show loading
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-block';
        submitBtn.disabled = true;

        try {
            const tripData = {
                user_id: user.id,
                trip_name: tripName,
                location: location,
                description: description,
                start_datetime: startDatetime,
                end_datetime: endDatetime,
                estimated_cost: estimatedCost || null
            };

            const response = await API.createTrip(tripData);
            
            if (response.message) {
                Utils.showAlert('createTripAlert', 'Trip created successfully!', 'success');
                
                // Reload trips after short delay
                setTimeout(() => {
                    createTripModal.style.display = 'none';
                    createTripForm.reset();
                    loadTrips();
                }, 1500);
            }
        } catch (error) {
            Utils.showAlert('createTripAlert', error.message || 'Failed to create trip. Please try again.', 'error');
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    // Initial load
    loadTrips();
});
