
<section class="home">
    <div class="hero">
        <p class="hero-label">LABORATORY MANAGEMENT</p>

        <h2>Laboratory Computer Reservation System</h2>

        <p class="hero-description">
            Find an available laboratory computer, choose a suitable
            time slot, and manage your reservations in one place.
        </p>

        <a class="button button-primary" href="<?= htmlspecialchars(
            rtrim(APP_BASE_URL, '/') . '/login.php',
            ENT_QUOTES,
            'UTF-8'
        ) ?>">
            Get Started
        </a>
    </div>

    <section class="features" id="features">
        <h2>What You Can Do</h2>

        <div class="feature-grid">
            <article class="feature-card">
                <h3>Check Availability</h3>
                <p>
                    View laboratory computers and find available
                    reservation slots.
                </p>
            </article>

            <article class="feature-card">
                <h3>Reserve a Computer</h3>
                <p>
                    Select a date and time slot to reserve a
                    laboratory computer.
                </p>
            </article>

            <article class="feature-card">
                <h3>Manage Bookings</h3>
                <p>
                    View your reservations and cancel bookings
                    when your plans change.
                </p>
            </article>
        </div>
    </section>

    <section class="how-it-works" id="how-it-works">
        <h2>How It Works</h2>

        <ol>
            <li>Check the availability of laboratory computers.</li>
            <li>Select your preferred date and time slot.</li>
            <li>Confirm your reservation.</li>
        </ol>

        <p class="coming-soon">
            Reservation functionality will be available as the
            system implementation progresses.
        </p>
    </section>
</section>
