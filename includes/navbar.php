<?php
// Navigation helper functions
function is_active_page($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page === $page ? 'active' : '';
}

function get_nav_items() {
    if (is_admin()) {
        return [
            ['url' => '/admin/dashboard.php', 'label' => 'Dashboard', 'icon' => '📊'],
            ['url' => '/admin/manage.php', 'label' => 'Manage Books', 'icon' => '📚'],
            ['url' => '/admin/add.php', 'label' => 'Add Book', 'icon' => '➕'],
            ['url' => '/admin/users.php', 'label' => 'Users', 'icon' => '👥'],
            ['url' => '/admin/archive.php', 'label' => 'Archive', 'icon' => '📦']
        ];
    } else {
        return [
            ['url' => '/student/dashboard.php', 'label' => 'Dashboard', 'icon' => '🏠'],
            ['url' => '/student/browse.php', 'label' => 'Browse Books', 'icon' => '🔍'],
            ['url' => '/student/borrow.php', 'label' => 'My Books', 'icon' => '📖'],
            ['url' => '/student/return.php', 'label' => 'Return Books', 'icon' => '↩️']
        ];
    }
}
?>

<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="/" class="brand-link">
                <span class="brand-icon">📚</span>
                <span class="brand-text">Library System</span>
            </a>
        </div>
        
        <?php if (is_logged_in()): ?>
            <div class="nav-menu">
                <?php foreach (get_nav_items() as $item): ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="nav-link <?php echo is_active_page(basename($item['url'])); ?>">
                        <span class="nav-icon"><?php echo $item['icon']; ?></span>
                        <span class="nav-label"><?php echo $item['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="nav-user">
                <div class="user-dropdown">
                    <button class="user-button" onclick="toggleUserMenu()">
                        <span class="user-avatar">👤</span>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    
                    <div class="user-menu" id="userMenu">
                        <div class="user-info">
                            <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                            <div class="user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
                        </div>
                        <hr>
                        <a href="/user/profile.php" class="menu-item">
                            <span>⚙️</span> Profile Settings
                        </a>
                        <a href="/user/logout.php" class="menu-item logout">
                            <span>🚪</span> Logout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>

<style>
.navbar {
    background: linear-gradient(135deg, var(--primary-brown) 0%, var(--dark-brown) 100%);
    box-shadow: 0 2px 10px var(--shadow);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    height: 70px;
}

.nav-brand .brand-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--white);
    font-size: 1.5rem;
    font-weight: bold;
}

.brand-icon {
    font-size: 2rem;
}

.nav-menu {
    display: flex;
    gap: 1rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: var(--white);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-link:hover,
.nav-link.active {
    background: var(--light-brown);
    transform: translateY(-2px);
}

.nav-icon {
    font-size: 1.2rem;
}

.user-dropdown {
    position: relative;
}

.user-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    border: 2px solid var(--light-brown);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-button:hover {
    background: var(--light-brown);
}

.user-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white);
    border-radius: 10px;
    box-shadow: 0 5px 20px var(--shadow);
    min-width: 200px;
    padding: 1rem;
    display: none;
    z-index: 1001;
}

.user-menu.show {
    display: block;
}

.user-info {
    text-align: center;
    margin-bottom: 0.5rem;
}

.user-role {
    font-weight: bold;
    color: var(--primary-brown);
}

.user-email {
    font-size: 0.9rem;
    color: var(--text-light);
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.menu-item:hover {
    background: var(--light-beige);
}

.menu-item.logout {
    color: var(--error);
}

@media (max-width: 768px) {
    .nav-container {
        flex-direction: column;
        height: auto;
        padding: 1rem;
        gap: 1rem;
    }
    
    .nav-menu {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .nav-link {
        flex-direction: column;
        gap: 0.25rem;
        padding: 0.5rem;
        text-align: center;
    }
    
    .nav-label {
        font-size: 0.8rem;
    }
}
</style>

<script>
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('show');
}

// Close user menu when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.user-dropdown');
    const menu = document.getElementById('userMenu');
    
    if (dropdown && !dropdown.contains(e.target)) {
        menu.classList.remove('show');
    }
});
</script>

