<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<section class="home">
    <div class="hero">
        <p class="hero-label">LAB ACCESS, MADE SIMPLE</p>
        <h2>Your next great idea starts at a desk.</h2>
        <p class="hero-description">Find your place in the lab. See live computer availability, reserve the time you need, and get straight to focused work.</p>
        <div class="hero-actions">
            <a class="button button-primary" href="<?= htmlspecialchars($baseUrl . '/login.php', ENT_QUOTES, 'UTF-8') ?>">Reserve a computer <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <a class="button button-secondary" href="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>">Explore availability</a>
        </div>
        <div class="hero-stats" aria-label="Reservation details">
            <div class="hero-stat"><strong data-count-up="28">28</strong><span>daily time slots</span></div>
            <div class="hero-stat"><strong><span data-count-up="30">30</span> min</strong><span>focused sessions</span></div>
            <div class="hero-stat"><strong><span data-count-up="4">4</span> days</strong><span>booking window</span></div>
        </div>
        <div class="hero-art" aria-hidden="true"><i class="fa-solid fa-desktop"></i></div>
    </div>

    <section class="features" id="features" data-reveal>
        <div class="section-intro">
            <p class="eyebrow">MADE FOR YOUR WORKFLOW</p>
            <h2>Everything in its right place.</h2>
            <p>Less time searching for a seat. More time doing your best work.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card">
                <span class="feature-icon" aria-hidden="true"><i class="fa-solid fa-display"></i></span>
                <h3>Know before you go</h3>
                <p>See available, reserved, and temporarily held computers in one clear live view.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon" aria-hidden="true"><i class="fa-regular fa-calendar-check"></i></span>
                <h3>Book on your terms</h3>
                <p>Choose a computer and reserve one or several consecutive lab sessions together.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon" aria-hidden="true"><i class="fa-solid fa-arrow-rotate-left"></i></span>
                <h3>Stay in control</h3>
                <p>Review upcoming reservations and cancel plans when your schedule changes.</p>
            </article>
        </div>
    </section>

    <section class="how-it-works" id="how-it-works" data-reveal>
        <div class="how-copy">
            <p class="eyebrow">THREE SIMPLE STEPS</p>
            <h2>From plan to workstation.</h2>
            <p>Reservations stay simple from first check to final confirmation.</p>
            <a class="button button-secondary" href="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>">Browse availability <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <ol class="steps-list">
            <li>Check the live lab schedule for a computer that fits your day.</li>
            <li>Choose your date and one or more available time slots.</li>
            <li>Review your temporary hold and confirm your reservation.</li>
        </ol>
    </section>

    <section class="home-cta" data-reveal>
        <div>
            <h2>Ready to make room for good work?</h2>
            <p>Sign in to find your next open slot.</p>
        </div>
        <a class="button button-primary" href="<?= htmlspecialchars($baseUrl . '/login.php', ENT_QUOTES, 'UTF-8') ?>">Get started <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </section>
</section>
