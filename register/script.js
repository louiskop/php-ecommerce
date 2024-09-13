
// add role selector functionality
document.addEventListener('DOMContentLoaded', (event) => {

    const roleContainer = document.getElementById('role-container');
    const roleInput = document.getElementById('role');

    roleContainer.addEventListener('click', (event) => {
        const roleOption = event.target.closest('.role-option');
        if (roleOption) {
            // Remove active class from all role options
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('active');
            });
            // Add active class to the clicked role option
            roleOption.classList.add('active');
            // Update the hidden input value
            roleInput.value = roleOption.dataset.role;
        }
    });
});
