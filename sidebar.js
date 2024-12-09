// sidebar.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.querySelector('.sidebar-toggle');
    const mainContent = document.querySelector('.main-content');
    
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('sidebar-active');
    }
    
    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', toggleSidebar);
    }
    
    // إغلاق الشريط الجانبي عند النقر خارجه في الشاشات الصغيرة
    document.addEventListener('click', function(event) {
        const isClickInside = sidebar.contains(event.target) || 
                            toggleButton.contains(event.target);
        
        if (!isClickInside && window.innerWidth <= 768 && 
            sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
});