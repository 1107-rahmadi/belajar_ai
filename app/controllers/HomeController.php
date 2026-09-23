<?php
/**
 * Home Controller
 *
 * Controller untuk halaman landing page
 */
class HomeController
{
    public function index(): void
    {
        // Jika sudah login, redirect ke dashboard
        if (isLoggedIn()) {
            redirectToRoleDashboard();
            return;
        }

        // Landing page untuk guest
        include VIEW_PATH . '/home.php';
    }

    public function about(): void
    {
        include VIEW_PATH . '/about.php';
    }
}
