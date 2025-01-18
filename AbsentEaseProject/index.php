<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absent-Ease</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg,rgb(195, 224, 250), white);
        }

        .page-title {
            font-family: "Comic Sans MS", cursive;
            font-size: 3rem;
            color: #333;
            margin-bottom: 4rem;
        }

        .login-btn {
            padding: 12px 24px;
            font-size: 1.1rem;
            background-color: #4287f5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #2563eb;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #15172b;
            border-radius: 20px;
            padding: 20px;
            width: 320px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            cursor: pointer;
            border: none;
            background: none;
            color: #eee;
            padding: 5px;
            z-index: 1;
        }

        .form-title {
            color: #eee;
            font-family: sans-serif;
            font-size: 36px;
            font-weight: 600;
            margin-top: 30px;
        }

        .subtitle {
            color: #eee;
            font-family: sans-serif;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
        }

        .input-container {
            height: 50px;
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        .ic1 {
            margin-top: 40px;
        }

        .ic2 {
            margin-top: 30px;
        }

        .input {
            background-color: #303245;
            border-radius: 12px;
            border: 0;
            box-sizing: border-box;
            color: #eee;
            font-size: 18px;
            height: 100%;
            outline: 0;
            padding: 4px 20px 0;
            width: 100%;
        }

        .cut {
            background-color: #15172b;
            border-radius: 10px;
            height: 20px;
            left: 20px;
            position: absolute;
            top: -20px;
            transform: translateY(0);
            transition: transform 200ms;
            width: 76px;
        }

        .cut-short {
            width: 50px;
        }

        .iLabel {
            color: #65657b;
            font-family: sans-serif;
            left: 20px;
            line-height: 14px;
            pointer-events: none;
            position: absolute;
            transform-origin: 0 50%;
            transition: transform 200ms, color 200ms;
            top: 20px;
        }

        .input:focus ~ .cut {
            transform: translateY(8px);
        }

        .input:focus ~ .iLabel {
            transform: translateY(-30px) translateX(10px) scale(0.75);
        }

        .input:not(:focus) ~ .iLabel {
            color: #808097;
        }

        .input:focus ~ .iLabel {
            color: #dc2f55;
        }

        .has-content ~ .iLabel {
            transform: translateY(-30px) translateX(10px) scale(0.75);
        }

        .submit {
            background-color: #08d;
            border-radius: 12px;
            border: 0;
            box-sizing: border-box;
            color: #eee;
            cursor: pointer;
            font-size: 18px;
            height: 50px;
            margin-top: 38px;
            text-align: center;
            width: 100%;
        }

        .submit:active {
            background-color: #06b;
        }

        .input-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #808097;
            padding: 5px;
        }

        .toggle-password:hover {
            color: #dc2f55;
        }

        .eye-icon, .eye-off-icon {
            display: block;
        }

        .hidden {
            display: none;
        }

        /* Disable auto-fill background color */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0px 1000px #303245 inset !important;
            -webkit-text-fill-color: #eee !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .button-container .login-btn {
            width: 100%;
            margin: 0;
        }

        #userTypeSelection, #authTypeSelection {
            text-align: center;
        }

        .submit:disabled {
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h1 class="page-title">AbsentEase</h1>
    <button class="login-btn">Login</button>

    <div class="modal" id="loginModal">
        <div class="modal-content">
            <button class="close-btn">&times;</button>
            
            <!-- User Type Selection -->
            <div id="userTypeSelection">
                <div class="form-title">Select Your Role</div>
                <div class="button-container">
                    <button type="button" class="login-btn" id="studentBtn">Student</button>
                    <button type="button" class="login-btn" id="teacherBtn">Teacher</button>
                </div>
            </div>

            <!-- Auth Type Selection -->
            <div id="authTypeSelection" style="display: none;">
                <div class="form-title">Sign In <br>or<br> Create an Account</div>
                <div class="button-container">
                    <button type="button" class="login-btn" id="signInBtn">Sign In</button>
                    <button type="button" class="login-btn" id="signUpBtn">Sign Up</button>
                </div>
            </div>

            <!-- Login/Signup Form -->
            <div id="authForm" style="display: none;">
                <div class="form-title">Welcome</div>
                <div class="subtitle" id="authSubtitle">Let's create your account!</div>

                <form id="loginForm">
                    <div class="input-container ic1">
                        <input placeholder="" type="text" class="input" id="username">
                        <div class="cut"></div>
                        <label class="iLabel" for="username">User Name</label>
                    </div>

                    <div class="input-container ic2">
                        <input placeholder="" 
                            type="password" 
                            class="input" 
                            id="password" 
                            autocomplete="off">
                        <div class="cut"></div>
                        <label class="iLabel" for="password">Password</label>
                        <button type="button" class="toggle-password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="eye-off-icon hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>

                    <button type="submit" class="submit" id="submitButton">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script>       

        const elements = {
            modal: document.getElementById('loginModal'),
            loginBtn: document.querySelector('.login-btn'),
            closeBtn: document.querySelector('.close-btn'),
            loginForm: document.getElementById('loginForm'),
            usernameInput: document.getElementById('username'),
            passwordInput: document.getElementById('password'),
            togglePassword: document.querySelector('.toggle-password'),
            eyeIcon: document.querySelector('.eye-icon'),
            eyeOffIcon: document.querySelector('.eye-off-icon'),
            userTypeSelection: document.getElementById('userTypeSelection'),
            authTypeSelection: document.getElementById('authTypeSelection'),
            authForm: document.getElementById('authForm'),
            studentBtn: document.getElementById('studentBtn'),
            teacherBtn: document.getElementById('teacherBtn'),
            signInBtn: document.getElementById('signInBtn'),
            signUpBtn: document.getElementById('signUpBtn'),
            submitButton: document.getElementById('submitButton'),
            authSubtitle: document.getElementById('authSubtitle')
        };

        // State management
        const state = {
            currentUserType: '',
            formState: {
                username: '',
                password: ''
            }
        };

        // Form validation
        const validateForm = () => {
            const isValid = Object.values(elements)
                .filter(el => el instanceof HTMLInputElement)
                .every(input => input.value.trim().length > 0);
            
            elements.submitButton.disabled = !isValid;
            elements.submitButton.style.opacity = isValid ? '1' : '0.5';
        };

        // Input handling
        const handleInputChange = (input) => {
            input.classList.toggle('has-content', input.value.length > 0);
            validateForm();
        };

        // Reset form state
        const resetForm = () => {
            elements.userTypeSelection.style.display = 'block';
            elements.authTypeSelection.style.display = 'none';
            elements.authForm.style.display = 'none';
            
            elements.usernameInput.value = '';
            elements.passwordInput.value = '';
            elements.usernameInput.classList.remove('has-content');
            elements.passwordInput.classList.remove('has-content');
            
            elements.passwordInput.setAttribute('type', 'password');
            elements.eyeIcon.classList.remove('hidden');
            elements.eyeOffIcon.classList.add('hidden');
            
            elements.submitButton.disabled = true;
            elements.submitButton.style.opacity = '0.5';
            
            state.currentUserType = '';
        };

        // Modal handling
        const showModal = () => {
            elements.modal.style.display = 'flex';
            elements.userTypeSelection.style.display = 'block';
            elements.authTypeSelection.style.display = 'none';
            elements.authForm.style.display = 'none';
        };

        const hideModal = () => {
            elements.modal.style.display = 'none';
            resetForm();
        };

        // User type handling
        const handleUserTypeSelection = (userType) => {
            state.currentUserType = userType;
            elements.userTypeSelection.style.display = 'none';
            elements.authTypeSelection.style.display = 'block';
        };

        // Auth type handling
        const handleAuthTypeSelection = (isSignIn) => {
            elements.authTypeSelection.style.display = 'none';
            elements.authForm.style.display = 'block';
            elements.authSubtitle.textContent = isSignIn 
                ? `Welcome back, ${state.currentUserType}!`
                : `Create your ${state.currentUserType} account!`;
            elements.submitButton.textContent = isSignIn ? 'Sign In' : 'Create Account';
        };

        // Form submission
        const handleFormSubmit = async (e) => {
            e.preventDefault();            
            
            const formData = new FormData();
            formData.append('username', elements.usernameInput.value);
            formData.append('password', elements.passwordInput.value);
            formData.append('userType', state.currentUserType);
            
            
            // Determine if it's login or signup based on button text
            const isSignIn = elements.submitButton.textContent === 'Sign In';
            formData.append('action', isSignIn ? 'login' : 'signup');
            
            try {
                const response = await fetch('auth_handlers.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (isSignIn) {
                        // Redirect to dashboard on successful login
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('Account created successfully! Please sign in.');
                        hideModal();
                    }
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        };

        // Event Listeners
        elements.loginBtn.addEventListener('click', showModal);
        elements.closeBtn.addEventListener('click', hideModal);
        // Removed the window click event listener that was closing the modal when clicking outside

        elements.studentBtn.addEventListener('click', () => handleUserTypeSelection('student'));
        elements.teacherBtn.addEventListener('click', () => handleUserTypeSelection('teacher'));

        elements.signInBtn.addEventListener('click', () => handleAuthTypeSelection(true));
        elements.signUpBtn.addEventListener('click', () => handleAuthTypeSelection(false));

        elements.togglePassword.addEventListener('click', () => {
            const type = elements.passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            elements.passwordInput.setAttribute('type', type);
            elements.eyeIcon.classList.toggle('hidden');
            elements.eyeOffIcon.classList.toggle('hidden');
        });

        [elements.usernameInput, elements.passwordInput].forEach(input => {
            input.setAttribute('autocomplete', 'off');
            input.addEventListener('input', () => handleInputChange(input));
        });

        elements.loginForm.addEventListener('submit', handleFormSubmit);
    </script>
</body>
</html>