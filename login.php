<?php
/**
 * Sistem Login dengan Database MySQL
 * Demonstrasi authentication dengan database
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'login_system');

// Start session
session_start();

// Variabel untuk flash message
$error = '';
$success = '';

// Koneksi database
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    return $conn;
}

// Fungsi untuk sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Proses logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $conn = getDBConnection();

        // Prepared statement untuk keamanan (mencegah SQL injection)
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, email, level FROM users WHERE username = ? AND is_active = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if ($password === $user['password']) {
                // Login berhasil - Update last login
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                $updateStmt->close();

                // Simpan ke session
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['level'] = $user['level'];
                $_SESSION['login_time'] = date('Y-m-d H:i:s');

                header('Location: login.php');
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }

        $stmt->close();
        $conn->close();
    }
}

// Cek apakah sudah login
$logged_in = $_SESSION['logged_in'] ?? false;
$current_user = $_SESSION['nama_lengkap'] ?? '';
$user_level = $_SESSION['level'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Login - <?php echo $logged_in ? 'Dashboard' : 'Login'; ?></title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <?php if ($logged_in): ?>
            <!-- Tampilan Dashboard (udah login) -->
            <h1>🎉 Selamat Datang!</h1>

            <div class="dashboard-info">
                <p><strong>Nama:</strong> <?php echo $_SESSION['nama_lengkap']; ?></p>
                <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>
                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                <p><strong>Level:</strong> <span class="badge badge-<?php echo $_SESSION['level']; ?>"><?php echo ucfirst($_SESSION['level']); ?></span></p>
                <p><strong>Waktu Login:</strong> <?php echo $_SESSION['login_time']; ?></p>
            </div>

            <p class="text-center color-gray mb-20">
                Anda berhasil login ke sistem!
            </p>

            <a href="login.php?logout=1">
                <button class="logout-btn">Logout</button>
            </a>

        <?php else: ?>
            <!-- Tampilan Login -->
            <h1>🔐 Login</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="Masukkan username"
                           value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password">
                </div>

                <button type="submit">Masuk</button>
            </form>

            <div class="user-list">
                <h3>👥 Akun Demo:</h3>
                <p>admin / admin123</p>
                <p>user / user123</p>
                <p>guest / guest123</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
