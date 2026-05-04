<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['auth_user'])) {
    header('Location: /gym/frontend/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | GymTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-3">GymTrack Login</h4>
                    <p class="text-muted small mb-3">Use your staff account to continue.</p>

                    <form id="login-form" class="d-grid gap-3">
                        <div>
                            <label class="form-label form-label-sm">Email</label>
                            <input id="email" type="email" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label form-label-sm">Password</label>
                            <input id="password" type="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Sign in</button>
                    </form>

                    <div id="login-error" class="alert alert-danger mt-3 d-none"></div>
                    <div class="text-muted small mt-3">Seed default: <code>ozan.admin@fitsphere.local</code> / <code>Admin123!</code></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('login-form');
const err = document.getElementById('login-error');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  err.classList.add('d-none');
  err.textContent = '';

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  try {
    const res = await fetch('/gym/backend/api/auth_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password })
    });

    const payload = await res.json();
    if (!res.ok || !payload.ok) {
      throw new Error(payload.error || 'Login failed');
    }

    window.location.href = '/gym/frontend/index.php';
  } catch (error) {
    err.textContent = error.message;
    err.classList.remove('d-none');
  }
});
</script>
</body>
</html>
