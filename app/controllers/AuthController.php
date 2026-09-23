<?php
/**
 * Auth Controller
 *
 * Controller untuk autentikasi: login dan logout
 * Compatible dengan PHP 7.3+
 */

class AuthController
{
    /** @var User */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Tampilkan form login (GET) atau proses login (POST)
     *
     * @return void
     */
    public function login(): void
    {
        // Kalau sudah login, redirect ke dashboard sesuai role
        if (isLoggedIn()) {
            redirectToRoleDashboard();
            return;
        }

        // Proses form login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        // Tampilkan form login
        $this->showLoginForm();
    }

    /**
     * Tampilkan form login
     *
     * @param string|null $error
     * @param array|null $oldInput
     * @return void
     */
    private function showLoginForm($error = null, $oldInput = null): void
    {
        $flash = getFlashMessage();

        include VIEW_PATH . '/auth/login.php';
    }

    /**
     * Proses login
     *
     * @return void
     */
    private function processLogin(): void
    {
        // Ambil input
        $noHp = trim($_POST['no_hp'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input ada
        if (empty($noHp) || empty($password)) {
            $this->showLoginForm(
                'No HP dan kata sandi harus diisi.',
                ['no_hp' => $noHp]
            );
            return;
        }

        // Cari user berdasarkan no HP
        $user = $this->userModel->findByNoHp($noHp);

        // Verifikasi password
        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Jangan bocorkan apakah no HP atau password yang salah
            $this->showLoginForm(
                'No HP atau kata sandi salah.',
                ['no_hp' => $noHp]
            );
            return;
        }

        // Cek apakah user aktif
        if (!User::isActive($user)) {
            $this->showLoginForm(
                'Akun Anda tidak aktif. Hubungi administrator.',
                ['no_hp' => $noHp]
            );
            return;
        }

        // Set session user
        setUserSession($user);

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Redirect ke dashboard sesuai role
        redirectToRoleDashboard();
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logout(): void
    {
        // Clear session user tapi pertahankan flash message
        $flash = $_SESSION['flash'] ?? null;
        destroySession();
        if ($flash) {
            $_SESSION['flash'] = $flash;
        }

        // Set pesan logout
        setFlashMessage('success', 'Anda telah logout. Sampai jumpa kembali!');

        // Redirect ke login
        redirect('login');
    }
}
