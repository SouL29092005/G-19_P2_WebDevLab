<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<section class="login-page" aria-labelledby="login-title">
    <div class="login-panel">
        <aside class="login-brand">
            <a class="brand" href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8') ?>" aria-label="Labspace home">
                <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-cube"></i></span>
                <span class="brand-name">labspace<span class="brand-period">.</span></span>
            </a>
            <div class="login-brand-copy">
                <p class="hero-label">YOUR LAB, YOUR TIME</p>
                <h2>Make space for what matters.</h2>
                <p>One calm place to find a workstation and get focused.</p>
            </div>
            <p class="login-brand-footer">A smarter way to work together.</p>
        </aside>

        <div class="login-content">
            <p class="login-label">ACCOUNT ACCESS</p>
            <h2 id="login-title">Welcome back</h2>
            <p class="login-description">Sign in to reserve a laboratory computer.</p>

            <?php if ($error !== null): ?>
                <div class="login-error" role="alert" data-toast="error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="post" action="<?= htmlspecialchars($baseUrl . '/login.php', ENT_QUOTES, 'UTF-8') ?>" data-validate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="login-field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" placeholder="you@school.edu" required autofocus>
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                        <button class="password-toggle" id="password-toggle" type="button" aria-controls="password" aria-label="Show password" data-password-toggle>Show</button>
                    </div>
                </div>

                <button class="button button-primary login-submit" type="submit">Sign in <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>

            <p class="login-register">Need an account? Contact your lab administrator.</p>
        </div>
    </div>
</section>
