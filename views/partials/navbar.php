<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="/">Tag Denton</a>
        <ul class="navbar-nav ms-auto">
            <?php if (Flight::session()->exist('is_logged_in')): ?>
                <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
