<div class="container shadow p-4 mt-5 bg-white rounded">
    <header class="text-center mb-4">
        <h1 class="text-success">Login</h1>
        <p>Please log in to create Tag Denton links.</p>
    </header>

    <form method="POST" action="/login">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Login</button>
    </form>
</div>