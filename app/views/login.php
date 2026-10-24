<section class="login-page" aria-labelledby="login-title">
    <div class="login-panel">
        <div class="login-brand" aria-hidden="true">
            <span class="brand-mark">LCR</span>
            <p>Lab Computer Reservation</p>
        </div>

        <div class="login-content">
            <p class="login-label">Account access</p>
            <h2 id="login-title">Welcome back</h2>
            <p class="login-description">Sign in to reserve a laboratory computer.</p>

            <?php if ($error !== null): ?>
                <div class="login-error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="post" action="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/login.php', ENT_QUOTES, 'UTF-8') ?>" novalidate>
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="login-field">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="login-submit" type="submit">Sign in</button>
            </form>

            <p class="login-register">
                Need an account?
                <a href="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/register.php', ENT_QUOTES, 'UTF-8') ?>">Register</a>
            </p>
        </div>
    </div>
</section>
