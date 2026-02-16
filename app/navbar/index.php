<nav class="navbar local-bg" id="navbar">
    <div class="navbar-left">
        <?php include __DIR__ . '/logo.php'; ?>
        <?php include __DIR__ . '/pagination.php'; ?>
    </div>
    <div class="navbar-center" id="navbar-content" data-current="local">
        <?php include __DIR__ . '/local.php'; ?>
    </div>
    <div class="navbar-right">
        <?php include __DIR__ . '/user.php'; ?>
        <?php include __DIR__ . '/dropdown.php'; ?>
    </div>
</nav>
<link rel="stylesheet" href="/base.css">
<script>
function setActiveGlow(view) {
	const navbar = document.getElementById('navbar');
	// Background transition
	if (view === 'local') {
		navbar.classList.remove('global-bg');
		navbar.classList.add('local-bg');
	} else {
		navbar.classList.remove('local-bg');
		navbar.classList.add('global-bg');
	}

	// Glow effect on active toggle
	document.querySelectorAll('.nav-option').forEach(btn => btn.classList.remove('active'));
	document.querySelector(`.nav-option[href="?view=${view}"]`).classList.add('active');

	// Glow + underline effect on active link inside navbar
	document.querySelectorAll('#navbar-content .nav-links a').forEach(link => {
		if (link.href === window.location.href) {
			link.classList.add('active-link');
		} else {
			link.classList.remove('active-link');
		}
	});
}

document.querySelectorAll('.nav-option').forEach(option => {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        const view = this.getAttribute('href').split('=')[1]; // local or globle
        const content = document.getElementById('navbar-content');
        const currentView = content.getAttribute('data-current');

        // Decide direction based on current vs target
        let direction = 'left';
        if (currentView === 'globle' && view === 'local') {
            direction = 'right';
        }

        // Slide out current content
        content.classList.add('slide-out-' + direction);

        setTimeout(() => {
            fetch('/app/navbar/' + view + '.php')
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                    content.setAttribute('data-current', view);

                    // Reset classes and slide in
                    content.classList.remove('slide-out-' + direction);
                    content.classList.add('slide-in-' + direction);

                    // Apply glow effects
                    setActiveGlow(view);

                    setTimeout(() => {
                        content.classList.remove('slide-in-' + direction);
                    }, 300);
                })
                .catch(err => console.error('Error loading navbar view:', err));
        }, 300); // match CSS transition
    });
});

// Initial glow setup
setActiveGlow('local');
</script>
