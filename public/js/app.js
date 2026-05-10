// Toggles the sidebar on mobile screens
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const wrapper = document.querySelector('.main-wrapper');
    if (sidebar) sidebar.classList.toggle('active');
    if (wrapper) wrapper.classList.toggle('active');
}

// Global confirm delete helper (optional but cleaner)
function confirmDelete(id, message = 'Bạn có chắc chắn muốn xóa?') {
    if (confirm(message)) {
        const form = document.getElementById('delete-form-' + id);
        if (form) form.submit();
    }
}
