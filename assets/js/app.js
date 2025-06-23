/**
 * TrackTraster - Main JavaScript Application
 */

// App initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    console.log('TrackTraster App Initialized');
    
    // Initialize form validations
    initializeFormValidation();
    
    // Initialize UI interactions
    initializeUIInteractions();
    
    // Auto-dismiss alerts
    initializeAlerts();
}

/**
 * Form validation initialization
 */
function initializeFormValidation() {
    // Real-time email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', validateEmail);
        input.addEventListener('input', clearFieldError);
    });
    
    // Real-time password validation
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', validatePassword);
    });
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);
    }
    
    // Form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmission);
    });
}

/**
 * UI interactions initialization
 */
function initializeUIInteractions() {
    // Remember me checkbox enhancement
    const rememberCheckbox = document.getElementById('remember_me');
    if (rememberCheckbox) {
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked) {
                showTooltip(this, 'Tus credenciales se recordarán por 30 días');
            }
        });
    }
    
    // Auto-focus first input
    const firstInput = document.querySelector('.auth-form input:not([type="hidden"])');
    if (firstInput) {
        firstInput.focus();
    }
    
    // Form field animations
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
}

/**
 * Alerts initialization
 */
function initializeAlerts() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentElement) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }, 5000);
    });
}

/**
 * Password visibility toggle
 */
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('aria-label', 'Ocultar contraseña');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('aria-label', 'Mostrar contraseña');
    }
}

/**
 * Email validation
 */
function validateEmail(event) {
    const input = event.target;
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    clearFieldError(event);
    
    if (email && !emailRegex.test(email)) {
        showFieldError(input, 'Por favor, ingresa un email válido');
        return false;
    }
    
    return true;
}

/**
 * Password validation
 */
function validatePassword(event) {
    const input = event.target;
    const password = input.value;
    
    clearFieldError(event);
    
    if (password.length > 0 && password.length < 6) {
        showFieldError(input, 'La contraseña debe tener al menos 6 caracteres');
        return false;
    }
    
    // Update password strength indicator if it exists
    updatePasswordStrength(password);
    
    return true;
}

/**
 * Password confirmation validation
 */
function validatePasswordConfirmation(event) {
    const confirmInput = event.target;
    const passwordInput = document.getElementById('password');
    
    clearFieldError(event);
    
    if (confirmInput.value && passwordInput.value !== confirmInput.value) {
        showFieldError(confirmInput, 'Las contraseñas no coinciden');
        return false;
    }
    
    return true;
}

/**
 * Update password strength indicator
 */
function updatePasswordStrength(password) {
    // This could be enhanced with a visual strength meter
    const strength = calculatePasswordStrength(password);
    console.log('Password strength:', strength);
}

/**
 * Calculate password strength
 */
function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength += 1;
    if (password.length >= 8) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    return strength;
}

/**
 * Show field error
 */
function showFieldError(input, message) {
    input.classList.add('is-invalid');
    
    // Remove existing error message
    const existingError = input.parentElement.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-danger mt-1';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.textContent = message;
    
    input.parentElement.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(event) {
    const input = event.target;
    input.classList.remove('is-invalid');
    
    const errorDiv = input.parentElement.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Show tooltip
 */
function showTooltip(element, message) {
    // Simple tooltip implementation
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = message;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.bottom + 8) + 'px';
    
    // Show tooltip
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);
    
    // Hide tooltip after 3 seconds
    setTimeout(() => {
        tooltip.style.opacity = '0';
        setTimeout(() => {
            if (tooltip.parentElement) {
                tooltip.remove();
            }
        }, 300);
    }, 3000);
}

/**
 * Handle form submission
 */
function handleFormSubmission(event) {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Add loading state
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('loading');
        
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        
        // Remove loading state after form submission (in case of errors)
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.classList.remove('loading');
            submitButton.innerHTML = originalText;
        }, 5000);
    }
    
    // Validate form before submission
    if (!validateForm(form)) {
        event.preventDefault();
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('loading');
            submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'Enviar';
        }
        return false;
    }
    
    return true;
}

/**
 * Validate entire form
 */
function validateForm(form) {
    let isValid = true;
    
    // Validate all required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Este campo es requerido');
            isValid = false;
        }
    });
    
    // Validate email fields
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !validateEmail({target: field})) {
            isValid = false;
        }
    });
    
    // Validate password fields
    const passwordFields = form.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        if (field.value && !validatePassword({target: field})) {
            isValid = false;
        }
    });
    
    // Validate password confirmation
    const confirmPassword = form.querySelector('#confirm_password');
    if (confirmPassword && confirmPassword.value) {
        if (!validatePasswordConfirmation({target: confirmPassword})) {
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Utility functions
 */
const Utils = {
    // Debounce function for input validation
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Format date for display
    formatDate: function(date) {
        return new Intl.DateTimeFormat('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    },
    
    // Show success message
    showSuccess: function(message) {
        this.showAlert(message, 'success');
    },
    
    // Show error message
    showError: function(message) {
        this.showAlert(message, 'danger');
    },
    
    // Show alert
    showAlert: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the form container
        const container = document.querySelector('.auth-form-container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 5000);
    }
};

// Export for global access
window.TrackTraster = {
    togglePassword,
    Utils
};
