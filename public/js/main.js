
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



// =========================================
// SIDEBAR TOGGLE
// =========================================

const dashboardLayout = document.querySelector('.dashboard-layout');
const sidebarToggle = document.getElementById('sidebarToggle');

if (dashboardLayout && sidebarToggle) {

    sidebarToggle.addEventListener('click', function () {

        if (window.innerWidth <= 768) {

            // Mobile: Show or hide sidebar
            dashboardLayout.classList.toggle('mobile-sidebar-open');

        } else {

            // Desktop: Collapse or expand sidebar
            dashboardLayout.classList.toggle('sidebar-collapsed');

        }

    });

}

// Close sidebar on mobile
const sidebarClose = document.getElementById('sidebarClose');

if (sidebarClose && dashboardLayout) {

    sidebarClose.addEventListener('click', function () {
        dashboardLayout.classList.remove('mobile-sidebar-open');
    });

}