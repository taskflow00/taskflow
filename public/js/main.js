
// =========================================
// TASKFLOW - MAIN JAVASCRIPT
// =========================================

// Show and hide password
document.querySelectorAll('.password-toggle').forEach(button => {

    button.addEventListener('click', function () {

        const input = document.getElementById(this.dataset.target);

        if (!input) return;

        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        this.textContent = isHidden ? 'Hide' : 'Show';

    });

});
