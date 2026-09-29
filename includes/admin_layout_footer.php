        </main>
        
        <!-- Admin Footer -->
        <footer class="admin-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <span>&copy; <?= date('Y') ?> Kalitusi Nature Resort. All rights reserved.</span>
                </div>
                <div class="footer-right">
                    <span>Version 2.0 | Last updated: <?= date('M j, Y') ?></span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script>
        // Initialize datetime pickers on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all datetime-local inputs with time picker
            document.querySelectorAll('input[type="datetime-local"]').forEach(function(input) {
                input.classList.add('form-control');
            });
            
            // Initialize Tempus Dominus for any elements with data-td-target
            document.querySelectorAll('[data-td-target]').forEach(function(el) {
                new tempusdominus.TempusDominus(el, {
                    localization: {
                        locale: 'en',
                        format: 'YYYY-MM-DD HH:mm'
                    },
                    display: {
                        icons: {
                            type: 'icons',
                            time: 'fa fa-clock',
                            date: 'fa fa-calendar',
                            up: 'fa fa-arrow-up',
                            down: 'fa fa-arrow-down',
                            previous: 'fa fa-chevron-left',
                            next: 'fa fa-chevron-right',
                            today: 'fa fa-calendar-check',
                            clear: 'fa fa-trash',
                            close: 'fa fa-times'
                        }
                    }
                });
            });
        });
    </script>
    <script>
        // Sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('adminSidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Desktop sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('sidebar-collapsed');
                    
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }
            
            // Mobile sidebar toggle
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('show');
                    document.body.classList.toggle('sidebar-open');
                });
            }
            
            // Overlay click to close mobile sidebar
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });
            }
            
            // Submenu toggles
            document.querySelectorAll('.submenu-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const navItem = this.closest('.nav-item');
                    const submenu = navItem.querySelector('.submenu');
                    const arrow = navItem.querySelector('.submenu-arrow');
                    
                    navItem.classList.toggle('submenu-open');
                    if (submenu) {
                        submenu.classList.toggle('show');
                    }
                    if (arrow) {
                        arrow.style.transform = navItem.classList.contains('submenu-open') ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                });
            });
            
            // Restore sidebar state from localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
            if (sidebarCollapsed === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
            }
            
            // Auto-close mobile sidebar on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            });
            
            // Smooth transitions
            sidebar.style.transition = 'all 0.3s ease';
            mainContent.style.transition = 'all 0.3s ease';
        });
        
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
        
        // Tooltip initialization
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
