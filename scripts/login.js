document.addEventListener('DOMContentLoaded', function() {
    const signUpButton = document.getElementById('signUpButton');
    const signInButton = document.getElementById('signInButton');
    const signInForm = document.getElementById('signIn');
    const signUpForm = document.getElementById('signUp');

    function switchToForm(showForm, hideForm) {
        // Fade out the current form
        hideForm.style.opacity = '0';
        hideForm.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            // Hide the current form
            hideForm.style.display = 'none';
            
            // Show and fade in the new form
            showForm.style.display = 'block';
            showForm.style.opacity = '0';
            showForm.style.transform = 'translateY(10px)';
            
            // Trigger the fade in animation
            setTimeout(() => {
                showForm.style.opacity = '1';
                showForm.style.transform = 'translateY(0)';
            }, 10);
        }, 300);
    }

    if (signUpButton) {
        signUpButton.addEventListener('click', function(e) {
            e.preventDefault();
            switchToForm(signUpForm, signInForm);
        });
    }

    if (signInButton) {
        signInButton.addEventListener('click', function(e) {
            e.preventDefault();
            switchToForm(signInForm, signUpForm);
        });
    }
});
