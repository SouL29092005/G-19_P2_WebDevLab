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
                    <div class="password-input">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            class="password-toggle"
                            id="password-toggle"
                            type="button"
                            aria-controls="password"
                            aria-label="Show password"
                            aria-pressed="false"
                            disabled
                        >Show</button>
                    </div>
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

<script>
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');

    passwordInput.addEventListener('input', () => {
        passwordToggle.disabled = passwordInput.value.length === 0;

        if (passwordToggle.disabled && passwordInput.type === 'text') {
            passwordInput.type = 'password';
            passwordToggle.textContent = 'Show';
            passwordToggle.setAttribute('aria-label', 'Show password');
            passwordToggle.setAttribute('aria-pressed', 'false');
        }
    });

    passwordToggle.addEventListener('click', () => {
        const shouldShow = passwordInput.type === 'password';
        passwordInput.type = shouldShow ? 'text' : 'password';
        passwordToggle.textContent = shouldShow ? 'Hide' : 'Show';
        passwordToggle.setAttribute(
            'aria-label',
            shouldShow ? 'Hide password' : 'Show password'
        );
        passwordToggle.setAttribute('aria-pressed', String(shouldShow));
        passwordInput.focus();
    });
</script>
