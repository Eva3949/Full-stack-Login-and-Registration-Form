// Form Validation Script
class FormValidator {
    constructor() {
        this.init();
    }

    init() {
        this.setupLoginForm();
        this.setupRegistrationForm();
    }

    // Validation helper functions
    validateRequired(value) {
        return value && value.trim() !== '';
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validatePhoneNumber(phone) {
        const phoneRegex = /^\d{10,}$/;
        return phoneRegex.test(phone.replace(/\D/g, ''));
    }

    validatePassword(password) {
        return password && password.length >= 6;
    }

    validateName(name) {
        return name && name.trim().length >= 2 && /^[a-zA-Z\s]+$/.test(name);
    }

    showError(input, message) {
        const errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('error-message')) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        } else {
            const errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            errorElement.style.color = 'red';
            errorElement.style.fontSize = '12px';
            errorElement.style.marginTop = '5px';
            errorElement.textContent = message;
            input.parentNode.insertBefore(errorElement, input.nextSibling);
        }
        input.style.borderColor = 'red';
    }

    clearError(input) {
        const errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('error-message')) {
            errorDiv.style.display = 'none';
        }
        input.style.borderColor = '#ccc';
    }

    // Login Form Validation
    setupLoginForm() {
        const loginForm = document.querySelector('.con');
        if (!loginForm) return;

        const loginBtn = document.querySelector('.login');
        const clearBtn = document.querySelector('.clear');
        const createAccountLink = document.querySelector('a[href="#"]');

        if (loginBtn) {
            loginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.validateLoginForm();
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearLoginForm();
            });
        }

        if (createAccountLink) {
            createAccountLink.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = 'registration.html';
            });
        }
    }

    validateLoginForm() {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        let isValid = true;

        // Clear previous errors
        this.clearError(usernameInput);
        this.clearError(passwordInput);

        // Validate username
        if (!this.validateRequired(usernameInput.value)) {
            this.showError(usernameInput, 'Username/Email is required');
            isValid = false;
        }

        // Validate password
        if (!this.validateRequired(passwordInput.value)) {
            this.showError(passwordInput, 'Password is required');
            isValid = false;
        }

        if (isValid) {
            this.performLogin(usernameInput.value, passwordInput.value);
        }
    }

    performLogin(username, password) {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);

        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccessMessage(data.message);
                
                // Store user data in localStorage for dashboard
                if (data.data && data.data.user) {
                    localStorage.setItem('userData', JSON.stringify(data.data.user));
                }
                
                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = data.data.redirect;
                }, 1500);
            } else {
                this.showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            this.showErrorMessage('Network error. Please try again.');
        });
    }

    clearLoginForm() {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        if (usernameInput) {
            usernameInput.value = '';
            this.clearError(usernameInput);
        }
        if (passwordInput) {
            passwordInput.value = '';
            this.clearError(passwordInput);
        }
    }

    // Registration Form Validation
    setupRegistrationForm() {
        const registerBtn = document.querySelector('.register');
        const clearBtn = document.querySelector('.clear');

        if (registerBtn) {
            registerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.validateRegistrationForm();
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearRegistrationForm();
            });
        }
    }

    validateRegistrationForm() {
        const firstNameInput = document.getElementById('firstName');
        const lastNameInput = document.getElementById('lastName');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const passwordInput = document.getElementById('password');
        const departmentSelect = document.getElementById('department');
        const genderInputs = document.querySelectorAll('input[name="Gender"]');
        const hobbiesInputs = document.querySelectorAll('input[name="Hobbies"]');
        const otherTextarea = document.querySelector('textarea');
        
        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.con input, .con select, .con textarea').forEach(input => {
            this.clearError(input);
        });

        // Validate First Name
        if (!this.validateRequired(firstNameInput.value)) {
            this.showError(firstNameInput, 'First name is required');
            isValid = false;
        } else if (!this.validateName(firstNameInput.value)) {
            this.showError(firstNameInput, 'Please enter a valid first name (letters only, min 2 characters)');
            isValid = false;
        }

        // Validate Last Name
        if (!this.validateRequired(lastNameInput.value)) {
            this.showError(lastNameInput, 'Last name is required');
            isValid = false;
        } else if (!this.validateName(lastNameInput.value)) {
            this.showError(lastNameInput, 'Please enter a valid last name (letters only, min 2 characters)');
            isValid = false;
        }

        // Validate Email
        if (!this.validateRequired(emailInput.value)) {
            this.showError(emailInput, 'Email is required');
            isValid = false;
        } else if (!this.validateEmail(emailInput.value)) {
            this.showError(emailInput, 'Please enter a valid email address');
            isValid = false;
        }

        // Validate Phone Number (10+ digits as requested)
        if (!this.validateRequired(phoneInput.value)) {
            this.showError(phoneInput, 'Phone number is required');
            isValid = false;
        } else {
            const phoneValidation = validatePhoneNumber(phoneInput.value);
            if (!phoneValidation.isValid) {
                this.showError(phoneInput, phoneValidation.message);
                isValid = false;
            }
        }

        // Validate Password
        if (!this.validateRequired(passwordInput.value)) {
            this.showError(passwordInput, 'Password is required');
            isValid = false;
        } else {
            const passwordValidation = validatePasswordStrength(passwordInput.value);
            if (!passwordValidation.isValid) {
                this.showError(passwordInput, passwordValidation.message);
                isValid = false;
            }
        }

        // Validate Department
        if (!departmentSelect.value || departmentSelect.selectedIndex === 0) {
            this.showError(departmentSelect, 'Please select a department');
            isValid = false;
        }

        // Validate Gender
        let genderSelected = false;
        genderInputs.forEach(input => {
            if (input.checked) genderSelected = true;
        });
        if (!genderSelected) {
            this.showError(genderInputs[0], 'Please select a gender');
            isValid = false;
        }

        // Validate Hobbies (at least one)
        let hobbySelected = false;
        hobbiesInputs.forEach(input => {
            if (input.checked) hobbySelected = true;
        });
        if (!hobbySelected) {
            this.showError(hobbiesInputs[0], 'Please select at least one hobby');
            isValid = false;
        }

        // Validate Other field (if filled, must be meaningful)
        if (this.validateRequired(otherTextarea.value) && otherTextarea.value.trim().length < 10) {
            this.showError(otherTextarea, 'If provided, other information must be at least 10 characters');
            isValid = false;
        }

        if (isValid) {
            this.performRegistration();
        }
    }

    performRegistration() {
        const formData = new FormData();
        formData.append('firstName', document.getElementById('firstName').value);
        formData.append('lastName', document.getElementById('lastName').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('phone', document.getElementById('phone').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('department', document.getElementById('department').value);
        
        // Get selected gender
        const genderInputs = document.querySelectorAll('input[name="Gender"]:checked');
        formData.append('gender', genderInputs.length > 0 ? genderInputs[0].value : '');
        
        // Get selected hobbies
        const hobbies = [];
        document.querySelectorAll('input[name="Hobbies"]:checked').forEach(checkbox => {
            hobbies.push(checkbox.value);
        });
        hobbies.forEach(hobby => formData.append('hobbies[]', hobby));
        
        formData.append('otherInfo', document.querySelector('textarea').value);

        fetch('register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccessMessage(data.message);
                setTimeout(() => {
                    window.location.href = 'LOGIN.html';
                }, 2000);
            } else {
                this.showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            this.showErrorMessage('Network error. Please try again.');
        });
    }

    clearRegistrationForm() {
        const inputs = document.querySelectorAll('.con input[type="text"]');
        const textarea = document.querySelector('textarea');
        const select = document.querySelector('select');
        const checkboxes = document.querySelectorAll('input[type="checkbox"], input[type="radio"]');

        inputs.forEach(input => input.value = '');
        if (textarea) textarea.value = '';
        if (select) select.selectedIndex = 0;
        checkboxes.forEach(checkbox => checkbox.checked = false);

        document.querySelectorAll('.con input, .con select, .con textarea').forEach(input => {
            this.clearError(input);
        });
    }

    showSuccessMessage(message) {
        // Remove existing success messages
        const existingSuccess = document.querySelector('.success-message');
        if (existingSuccess) {
            existingSuccess.remove();
        }

        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            font-weight: bold;
        `;
        document.body.appendChild(successDiv);

        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.remove();
            }
        }, 3000);
    }

    showErrorMessage(message) {
        // Remove existing error messages
        const existingError = document.querySelector('.error-popup');
        if (existingError) {
            existingError.remove();
        }

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-popup';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            font-weight: bold;
        `;
        document.body.appendChild(errorDiv);

        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

// Phone number validation function (as requested)
function validatePhoneNumber(phoneNumber) {
    // Remove all non-digit characters
    const cleanedPhone = phoneNumber.replace(/\D/g, '');
    
    // Check if phone number has at least 10 digits
    if (cleanedPhone.length < 10) {
        return {
            isValid: false,
            message: 'Phone number must be at least 10 digits long'
        };
    }
    
    // Check if phone number contains only digits
    if (!/^\d+$/.test(cleanedPhone)) {
        return {
            isValid: false,
            message: 'Phone number can only contain digits'
        };
    }
    
    return {
        isValid: true,
        message: 'Valid phone number'
    };
}

// Additional validation functions for specific requirements
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePasswordStrength(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (password.length < minLength) {
        return {
            isValid: false,
            message: `Password must be at least ${minLength} characters long`
        };
    }

    if (!hasUpperCase || !hasLowerCase) {
        return {
            isValid: false,
            message: 'Password must contain both uppercase and lowercase letters'
        };
    }

    if (!hasNumbers) {
        return {
            isValid: false,
            message: 'Password must contain at least one number'
        };
    }

    if (!hasSpecialChar) {
        return {
            isValid: false,
            message: 'Password must contain at least one special character'
        };
    }

    return {
        isValid: true,
        message: 'Strong password'
    };
}

// Initialize the validator when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new FormValidator();
});

// Export functions for external use if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        validatePhoneNumber,
        validateEmail,
        validatePasswordStrength,
        FormValidator
    };
}
