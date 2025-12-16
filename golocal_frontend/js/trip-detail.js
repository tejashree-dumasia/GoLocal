// Trip Detail JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Require authentication
    AuthManager.requireAuth();

    const user = AuthManager.getUser();
    if (!user) {
        AuthManager.logout();
        return;
    }

    // Get trip ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const tripId = urlParams.get('id');

    if (!tripId) {
        window.location.href = 'dashboard.html';
        return;
    }

    // Elements
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    const logoutBtn = document.getElementById('logoutBtn');
    const deleteTripBtn = document.getElementById('deleteTripBtn');
    const inviteBtn = document.getElementById('inviteBtn');

    // User menu
    userMenuBtn.addEventListener('click', () => {
        userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        AuthManager.logout();
    });

    // Tab switching
    const tripTabs = document.querySelectorAll('.trip-tab');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tripTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.dataset.tab;
            
            tripTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
                if (panel.id === targetTab) {
                    panel.classList.add('active');
                }
            });
        });
    });

    // Load trip details
    async function loadTripDetails() {
        try {
            const response = await API.getTrip(tripId);
            
            if (response.trip) {
                const trip = response.trip;
                displayTripInfo(trip);
                await loadParticipants();
                await loadChecklist();
            }
        } catch (error) {
            console.error('Error loading trip:', error);
            alert('Failed to load trip details');
            window.location.href = 'dashboard.html';
        }
    }

    // Display trip information
    function displayTripInfo(trip) {
        document.getElementById('tripName').textContent = trip.trip_name;
        document.getElementById('tripLocation').textContent = trip.location;
        document.getElementById('tripDates').textContent = 
            `${Utils.formatDate(trip.start_datetime)} - ${Utils.formatDate(trip.end_datetime)}`;
        document.getElementById('tripCost').textContent = 
            trip.estimated_cost ? Utils.formatCurrency(trip.estimated_cost) : 'Not specified';
        document.getElementById('tripDescription').textContent = trip.description || 'No description provided';
        
        const status = Utils.getTripStatus(trip.start_datetime, trip.end_datetime);
        const statusBadge = document.getElementById('tripStatus');
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusBadge.className = `status-badge ${status}`;
        
        document.getElementById('tripCreator').textContent = trip.username || 'Unknown';
        document.getElementById('tripCreatedDate').textContent = Utils.formatDate(trip.created_at);
        document.getElementById('tripDuration').textContent = 
            Utils.calculateDuration(trip.start_datetime, trip.end_datetime);
    }

    // Load participants
    async function loadParticipants() {
        try {
            const response = await API.getParticipants(tripId);
            
            if (response.participants && response.participants.length > 0) {
                displayParticipants(response.participants);
                document.getElementById('participantsBadge').textContent = response.participants.length;
                document.getElementById('statsParticipants').textContent = response.participants.length;
                document.getElementById('participantsEmpty').style.display = 'none';
            } else {
                document.getElementById('participantsList').innerHTML = '';
                document.getElementById('participantsEmpty').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading participants:', error);
        }
    }

    // Display participants
    function displayParticipants(participants) {
        const participantsList = document.getElementById('participantsList');
        participantsList.innerHTML = '';

        participants.forEach(participant => {
            const card = document.createElement('div');
            card.className = 'participant-card';
            
            const isAdmin = parseInt(participant.is_admin) === 1 || parseInt(participant.is_co_admin) === 1;
            const roleClass = isAdmin ? 'admin' : '';
            const roleText = parseInt(participant.is_admin) === 1 ? 'Admin' : 
                            parseInt(participant.is_co_admin) === 1 ? 'Co-Admin' : 'Member';

            card.innerHTML = `
                <div class="participant-avatar">
                    ${Utils.getInitials(participant.username)}
                </div>
                <div class="participant-info">
                    <div class="participant-name">${participant.username}</div>
                    <div class="participant-email">${participant.email}</div>
                </div>
                <span class="participant-role ${roleClass}">${roleText}</span>
            `;

            participantsList.appendChild(card);
        });
    }

    // Load checklist
    async function loadChecklist() {
        try {
            const response = await API.getChecklist(tripId);
            
            if (response.checklist && response.checklist.length > 0) {
                displayChecklist(response.checklist);
                document.getElementById('checklistBadge').textContent = response.checklist.length;
                document.getElementById('statsChecklist').textContent = response.checklist.length;
                document.getElementById('checklistEmpty').style.display = 'none';
            } else {
                document.getElementById('checklistList').innerHTML = '';
                document.getElementById('checklistEmpty').style.display = 'block';
                document.getElementById('checklistBadge').textContent = '0';
            }
        } catch (error) {
            console.error('Error loading checklist:', error);
        }
    }

    // Display checklist
    function displayChecklist(items) {
        const checklistList = document.getElementById('checklistList');
        checklistList.innerHTML = '';

        items.forEach(item => {
            const isCompleted = parseInt(item.is_completed) === 1;
            const itemDiv = document.createElement('div');
            itemDiv.className = `checklist-item ${isCompleted ? 'completed' : ''}`;
            
            itemDiv.innerHTML = `
                <div class="checklist-checkbox ${isCompleted ? 'checked' : ''}" data-id="${item.checklist_id}">
                    ${isCompleted ? '<i class="fas fa-check"></i>' : ''}
                </div>
                <div class="checklist-text">${item.item_name}</div>
                <button class="checklist-delete" data-id="${item.checklist_id}">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            // Checkbox toggle
            const checkbox = itemDiv.querySelector('.checklist-checkbox');
            checkbox.addEventListener('click', async () => {
                const newStatus = !isCompleted;
                try {
                    await API.updateChecklistItem(item.checklist_id, newStatus);
                    loadChecklist();
                } catch (error) {
                    console.error('Error updating checklist:', error);
                    alert('Failed to update checklist item');
                }
            });

            // Delete button
            const deleteBtn = itemDiv.querySelector('.checklist-delete');
            deleteBtn.addEventListener('click', async () => {
                if (confirm('Delete this checklist item?')) {
                    try {
                        await API.deleteChecklistItem(item.checklist_id);
                        loadChecklist();
                    } catch (error) {
                        console.error('Error deleting checklist:', error);
                        alert('Failed to delete checklist item');
                    }
                }
            });

            checklistList.appendChild(itemDiv);
        });
    }

    // Invite modal
    const inviteModal = document.getElementById('inviteModal');
    const inviteForm = document.getElementById('inviteForm');

    inviteBtn.addEventListener('click', () => {
        inviteModal.style.display = 'flex';
    });

    inviteModal.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            inviteModal.style.display = 'none';
            inviteForm.reset();
        });
    });

    inviteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('inviteEmail').value;
        const isCoAdmin = document.getElementById('isCoAdmin').checked;

        try {
            await API.inviteUser(tripId, email, isCoAdmin);
            Utils.showAlert('inviteAlert', 'Invitation sent successfully!', 'success');
            setTimeout(() => {
                inviteModal.style.display = 'none';
                inviteForm.reset();
                loadParticipants();
            }, 1500);
        } catch (error) {
            Utils.showAlert('inviteAlert', error.message || 'Failed to send invitation', 'error');
        }
    });

    // Add checklist modal
    const checklistModal = document.getElementById('checklistModal');
    const checklistForm = document.getElementById('checklistForm');
    const addChecklistBtn = document.getElementById('addChecklistBtn');

    addChecklistBtn.addEventListener('click', () => {
        checklistModal.style.display = 'flex';
    });

    checklistModal.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            checklistModal.style.display = 'none';
            checklistForm.reset();
        });
    });

    checklistForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const itemName = document.getElementById('checklistItem').value;

        try {
            await API.createChecklistItem(tripId, itemName);
            Utils.showAlert('checklistAlert', 'Item added successfully!', 'success');
            setTimeout(() => {
                checklistModal.style.display = 'none';
                checklistForm.reset();
                loadChecklist();
            }, 1000);
        } catch (error) {
            Utils.showAlert('checklistAlert', error.message || 'Failed to add item', 'error');
        }
    });

    // Upload photo modal
    const uploadPhotoModal = document.getElementById('uploadPhotoModal');
    const uploadPhotoForm = document.getElementById('uploadPhotoForm');
    const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
    const photoFile = document.getElementById('photoFile');
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');

    uploadPhotoBtn.addEventListener('click', () => {
        uploadPhotoModal.style.display = 'flex';
    });

    uploadPhotoModal.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            uploadPhotoModal.style.display = 'none';
            uploadPhotoForm.reset();
            photoPreview.style.display = 'none';
        });
    });

    photoFile.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    uploadPhotoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const file = photoFile.files[0];
        if (!file) {
            alert('Please select a photo');
            return;
        }

        const submitBtn = uploadPhotoForm.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-block';
        submitBtn.disabled = true;

        try {
            await API.uploadPhoto(tripId, file);
            Utils.showAlert('uploadAlert', 'Photo uploaded successfully!', 'success');
            setTimeout(() => {
                uploadPhotoModal.style.display = 'none';
                uploadPhotoForm.reset();
                photoPreview.style.display = 'none';
            }, 1500);
        } catch (error) {
            Utils.showAlert('uploadAlert', error.message || 'Failed to upload photo', 'error');
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    // Delete trip
    deleteTripBtn.addEventListener('click', async () => {
        if (confirm('Are you sure you want to delete this trip? This action cannot be undone.')) {
            try {
                await API.deleteTrip(tripId);
                alert('Trip deleted successfully');
                window.location.href = 'dashboard.html';
            } catch (error) {
                alert('Failed to delete trip');
            }
        }
    });

    // Initial load
    loadTripDetails();
});
