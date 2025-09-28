                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <script>
        // Security: Clear sensitive data on page unload
        window.addEventListener('unload', function() {
            localStorage.removeItem('formData');
            sessionStorage.clear();
        });

        // Security: Disable browser's password autofill for non-login forms
        document.querySelectorAll('form:not([action*="login"])').forEach(form => {
            form.setAttribute('autocomplete', 'off');
        });

        // Sidebar toggle functionality with security enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const mainContent = document.querySelector('.admin-main');

            if (sidebarToggle && sidebar && mainContent) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    
                    // Store the sidebar state in localStorage
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                });

                // Check localStorage for saved sidebar state on page load
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            }
        });

        // Security: Add CSRF token to AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            document.addEventListener('submit', function(e) {
                if (e.target.tagName === 'FORM') {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'csrf_token';
                    input.value = csrfToken;
                    e.target.appendChild(input);
                }
            });
        }
    </script>

    <?php if (isset($page_scripts)): ?>
        <?= $page_scripts ?>
    <?php endif; ?>
</body>
</html>