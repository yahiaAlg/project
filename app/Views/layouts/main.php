<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container container">
            <div class="navbar-brand">
                <a href="/">
                    <i class="material-icons mr-2">menu_book</i>
                    <?= APP_NAME ?>
                </a>
            </div>
            
            <button id="sidebar-toggle" class="btn btn-sm d-lg-none">
                <i class="material-icons">menu</i>
            </button>
            
            <ul class="navbar-nav d-none d-lg-flex">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="/books" class="nav-link">Books</a>
                    </li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a href="/members" class="nav-link">Members</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="/loans" class="nav-link">Loans</a>
                    </li>
                    <li class="nav-item">
                        <a href="/logout" class="nav-link">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/login" class="nav-link">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="/register" class="nav-link">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <?php if (is_logged_in()): ?>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>Menu</h3>
            </div>
            
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="/dashboard" class="sidebar-nav-link">
                        <i class="material-icons">dashboard</i>
                        Dashboard
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="/books" class="sidebar-nav-link">
                        <i class="material-icons">book</i>
                        Books
                    </a>
                </li>
                
                <?php if (is_admin()): ?>
                    <li class="sidebar-nav-item">
                        <a href="/members" class="sidebar-nav-link">
                            <i class="material-icons">people</i>
                            Members
                        </a>
                    </li>
                    
                    <li class="sidebar-nav-item">
                        <a href="/audit" class="sidebar-nav-link">
                            <i class="material-icons">history</i>
                            Audit Logs
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="sidebar-nav-item">
                    <a href="/loans" class="sidebar-nav-link">
                        <i class="material-icons">assignment</i>
                        Loans
                    </a>
                </li>
                
                <?php if (!is_admin()): ?>
                    <li class="sidebar-nav-item">
                        <a href="/loans/history" class="sidebar-nav-link">
                            <i class="material-icons">history</i>
                            Loan History
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="sidebar-nav-item">
                    <a href="/members/profile" class="sidebar-nav-link">
                        <i class="material-icons">account_circle</i>
                        My Profile
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="/logout" class="sidebar-nav-link">
                        <i class="material-icons">exit_to_app</i>
                        Logout
                    </a>
                </li>
            </ul>
        </aside>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content <?= !is_logged_in() ? 'ml-0' : '' ?>">
        <div class="container">
            <!-- Flash Messages -->
            <?php $flash = get_flash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> mb-4">
                    <?= $flash['message'] ?>
                </div>
            <?php endif; ?>
            
            <!-- Page Content -->
            <?= $content ?>
        </div>
    </main>
    
    <!-- Scripts -->
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
            }
            
            // Close alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 1s';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 1000);
                }, 5000);
            });
        });
    </script>
    
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>