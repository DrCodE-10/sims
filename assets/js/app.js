/**
 * SIMS - Main Application JavaScript
 * Student Information Management System
 * 
 * Core application logic and interactions
 */

// Application State
const AppState = {
    currentUser: null,
    theme: 'dark',
    notifications: [],
    isLoading: false
};

// DOM Elements
const elements = {
    body: document.body,
    sidebar: null,
    navItems: null,
    searchInput: null,
    notificationBell: null,
    userProfile: null
};

// Initialize Application
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    initializeTheme();
    initializeNavigation();
    initializeSearch();
    initializeNotifications();
    // Particles: use HTML #particles + animations.js (avoid duplicate containers)
    initializeAnimations();
});

// Initialize App
function initializeApp() {
    console.log('SIMS - Initializing...');
    
    // Cache DOM elements
    elements.sidebar = document.querySelector('.sidebar');
    elements.navItems = document.querySelectorAll('.nav-item');
    elements.searchInput = document.querySelector('.search-bar input');
    elements.notificationBell = document.querySelector('.notification-bell');
    elements.userProfile = document.querySelector('.user-profile');
    
    // Check for saved session
    checkSession();
}

// Theme Management
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    setTheme(savedTheme);
}

function setTheme(theme) {
    AppState.theme = theme;
    localStorage.setItem('theme', theme);
    
    if (theme === 'light') {
        document.body.classList.add('light-theme');
    } else {
        document.body.classList.remove('light-theme');
    }
}

function toggleTheme() {
    const newTheme = AppState.theme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
}

// Navigation — only intercept in-dashboard section tabs (not real links)
function initializeNavigation() {
    if (!elements.navItems) return;

    elements.navItems.forEach(item => {
        const link = item.querySelector('a[href]');
        if (link) {
            return;
        }

        item.addEventListener('click', (e) => {
            const section = item.dataset.section;
            if (!section) return;

            e.preventDefault();
            elements.navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            navigateToSection(section);
        });
    });
}

function navigateToSection(section) {
    // Add loading animation
    showLoading();
    
    // Simulate navigation delay
    setTimeout(() => {
        hideLoading();
        // Here you would implement actual navigation logic
        console.log(`Navigating to: ${section}`);
    }, 500);
}

// Search Functionality
function initializeSearch() {
    if (!elements.searchInput) return;
    
    let searchTimeout;
    
    elements.searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        
        const query = e.target.value.trim();
        
        if (query.length < 2) return;
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
}

function performSearch(query) {
    console.log(`Searching for: ${query}`);
    // Implement search logic here
}

// Notifications
function initializeNotifications() {
    if (!elements.notificationBell) return;
    
    elements.notificationBell.addEventListener('click', () => {
        toggleNotificationPanel();
    });
    
    // Load notifications
    loadNotifications();
}

function loadNotifications() {
    // Simulate loading notifications
    AppState.notifications = [
        { id: 1, title: 'New Student Added', message: 'John Doe has been added to the system', time: '5 min ago', read: false },
        { id: 2, title: 'Attendance Alert', message: '3 students marked absent today', time: '1 hour ago', read: false },
        { id: 3, title: 'System Update', message: 'SIMS updated to v2.1', time: '2 hours ago', read: true }
    ];
    
    updateNotificationBadge();
}

function updateNotificationBadge() {
    const unreadCount = AppState.notifications.filter(n => !n.read).length;
    const badge = document.querySelector('.notification-badge');
    
    if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'flex' : 'none';
    }
}

function toggleNotificationPanel() {
    // Implement notification panel toggle
    console.log('Toggle notification panel');
}

// Loading States
function showLoading() {
    AppState.isLoading = true;
    document.body.classList.add('loading');
    
    // Show loading spinner
    const spinner = document.createElement('div');
    spinner.className = 'loading-overlay';
    spinner.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(spinner);
}

function hideLoading() {
    AppState.isLoading = false;
    document.body.classList.remove('loading');
    
    // Remove loading spinner
    const spinner = document.querySelector('.loading-overlay');
    if (spinner) {
        spinner.remove();
    }
}

// Animations
function initializeAnimations() {
    // Add entrance animations to elements
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
}

// Session Management
function checkSession() {
    const session = localStorage.getItem('userSession');
    
    if (session) {
        AppState.currentUser = JSON.parse(session);
        console.log('User session found:', AppState.currentUser);
    }
}

function login(username, password, rememberMe = false) {
    showLoading();
    
    // Simulate API call
    setTimeout(() => {
        // Here you would make actual API call
        const user = {
            id: 1,
            username: username,
            role: 'admin',
            name: 'Administrator'
        };
        
        AppState.currentUser = user;
        
        if (rememberMe) {
            localStorage.setItem('userSession', JSON.stringify(user));
        }
        
        hideLoading();
        
        // Redirect to dashboard
        window.location.href = 'index.php';
    }, 1000);
}

function logout() {
    showLoading();
    
    // Clear session
    localStorage.removeItem('userSession');
    AppState.currentUser = null;
    
    hideLoading();
    
    // Redirect to login
    window.location.href = 'login.php';
}

// Toast Notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Remove after delay
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Modal Management
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Form Validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// API Helper
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        return null;
    }
}

// Real-time Updates
function initializeRealTimeUpdates() {
    // Simulate real-time updates
    setInterval(() => {
        // Here you would implement WebSocket or polling logic
        console.log('Checking for updates...');
    }, 30000);
}

// Export functions for use in other files
window.SIMS = {
    AppState,
    login,
    logout,
    showToast,
    showModal,
    hideModal,
    validateForm,
    apiCall,
    toggleTheme,
    showLoading,
    hideLoading
};
