<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-info">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?></p>
                <p class="text-muted">Mendukung pengelolaan sampah berbasis komunitas</p>
            </div>
            <div class="footer-links">
                <a href="#">Kebijakan Privasi</a>
                <a href="#">Syarat & Ketentuan</a>
                <a href="#">Hubungi Kami</a>
            </div>
        </div>
    </div>
</footer>

<style>
.site-header {
    background: var(--color-primary);
    color: white;
    padding: 1rem 0;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    text-decoration: none;
}

.main-nav {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.main-nav a {
    color: white;
    text-decoration: none;
    transition: opacity 0.2s;
}

.main-nav a:hover {
    opacity: 0.8;
}

.btn-small {
    background: white;
    color: var(--color-primary);
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 500;
}

.site-footer {
    background: var(--color-dark);
    color: white;
    padding: 2rem 0;
    margin-top: 3rem;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
}

.footer-links a:hover {
    color: white;
}
</style>
