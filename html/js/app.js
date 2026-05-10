// Toggles the sidebar on mobile screens
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
    document.querySelector('.main-wrapper').classList.toggle('active');
}

// Simple simulation of logout
function logout() {
    if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = 'login.html';
    }
}

// Logic for Login form (simulation)
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        window.location.href = 'dashboard.html';
    });
}

// Logic for Register form (simulation)
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Tài khoản đã được tạo thành công! Quay lại đăng nhập.');
        window.location.href = 'login.html';
    });
}
