    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <a class="brand footer-brand" href="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>">
                <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-cube"></i></span>
                <span class="brand-name">labspace<span class="brand-period">.</span></span>
            </a>
            <p>Thoughtful spaces for focused work.</p>
            <span class="footer-copyright">&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </footer>

    <button class="back-to-top icon-button" id="back-to-top" type="button" aria-label="Back to top" title="Back to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>
    <div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="false"></div>
    <script defer src="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/assets/js/app.js?v=' . filemtime(PUBLIC_PATH . '/assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
