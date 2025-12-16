// Main Landing Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Redirect if already authenticated
    AuthManager.redirectIfAuthenticated();

    // Elements
    const authModal = document.getElementById('authModal');
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const loginFormElement = document.getElementById('loginFormElement');
    const signupFormElement = document.getElementById('signupFormElement');
    const modalClose = document.getElementById('modalClose');
    
    // Buttons
    const loginBtn = document.getElementById('loginBtn');
    const signupBtn = document.getElementById('signupBtn');
    const getStartedBtn = document.getElementById('getStartedBtn');
    const showSignupLink = document.getElementById('showSignup');
    const showLoginLink = document.getElementById('showLogin');
    const learnMoreBtn = document.getElementById('learnMoreBtn');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');

    // Show login modal
    function showLogin() {
        authModal.style.display = 'flex';
        loginForm.style.display = 'block';
        signupForm.style.display = 'none';
        document.getElementById('loginAlert').style.display = 'none';
        document.getElementById('signupAlert').style.display = 'none';
    }

    // Show signup modal
    function showSignup() {
        authModal.style.display = 'flex';
        loginForm.style.display = 'none';
        signupForm.style.display = 'block';
        document.getElementById('loginAlert').style.display = 'none';
        document.getElementById('signupAlert').style.display = 'none';
    }

    // Close modal
    function closeModal() {
        authModal.style.display = 'none';
        loginFormElement.reset();
        signupFormElement.reset();
    }

    // Event Listeners
    loginBtn.addEventListener('click', showLogin);
    signupBtn.addEventListener('click', showSignup);
    getStartedBtn.addEventListener('click', showSignup);
    modalClose.addEventListener('click', closeModal);
    showSignupLink.addEventListener('click', (e) => {
        e.preventDefault();
        showSignup();
    });
    showLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        showLogin();
    });

    // Close modal on outside click
    authModal.addEventListener('click', (e) => {
        if (e.target === authModal) {
            closeModal();
        }
    });

    // Learn more button
    if (learnMoreBtn) {
        learnMoreBtn.addEventListener('click', () => {
            document.getElementById('features').scrollIntoView({ behavior: 'smooth' });
        });
    }

    // Mobile menu
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.classList.toggle('show');
        });
    }

    // Smooth scroll for nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.style.boxShadow = 'var(--shadow)';
        } else {
            navbar.style.boxShadow = 'var(--shadow-sm)';
        }
    });

    // Login Form Submission
    loginFormElement.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const submitBtn = loginFormElement.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        // Show loading
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-block';
        submitBtn.disabled = true;

        try {
            const response = await API.login(email, password);
            
            if (response.jwt) {
                // Save token and user data
                AuthManager.saveToken(response.jwt);
                
                // Decode JWT to get user info (simple decode, not verification)
                const payload = JSON.parse(atob(response.jwt.split('.')[1]));
                AuthManager.saveUser({
                    id: payload.data.id,
                    username: payload.data.username,
                    email: payload.data.email
                });

                // Show success message
                Utils.showAlert('loginAlert', 'Login successful! Redirecting...', 'success');

                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            } else {
                throw new Error('Login failed. Please try again.');
            }
        } catch (error) {
            Utils.showAlert('loginAlert', error.message || 'Login failed. Please check your credentials.', 'error');
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    // Signup Form Submission
    signupFormElement.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('signupUsername').value;
        const email = document.getElementById('signupEmail').value;
        const password = document.getElementById('signupPassword').value;
        const submitBtn = signupFormElement.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.btn-loader');

        // Show loading
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-block';
        submitBtn.disabled = true;

        try {
            const response = await API.register(username, email, password);
            
            if (response.message) {
                Utils.showAlert('signupAlert', 'Account created successfully! Please login.', 'success');
                
                // Switch to login form after 2 seconds
                setTimeout(() => {
                    showLogin();
                    document.getElementById('loginEmail').value = email;
                }, 2000);
            }
        } catch (error) {
            Utils.showAlert('signupAlert', error.message || 'Registration failed. Please try again.', 'error');
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    });
});
