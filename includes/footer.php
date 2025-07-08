    </main>
    
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Library Management System. All rights reserved.</p>
            <p>Designed by Francine Jace Baciller</p>
            <div id="current-time" style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;"></div>
        </div>
    </footer>
    
    <?php
    // Determine the correct path to assets based on current directory
    $current_dir = dirname($_SERVER['PHP_SELF']);
    if (strpos($current_dir, '/user') !== false || strpos($current_dir, '/admin') !== false || strpos($current_dir, '/student') !== false) {
        $script_path = '../assets/script.js';
    } else {
        $script_path = 'assets/script.js';
    }
    ?>
    <script src="<?php echo $script_path; ?>"></script>
</body>
</html>

