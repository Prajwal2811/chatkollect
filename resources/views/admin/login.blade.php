<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        body {
            margin: 0;
            height: 100vh;
            background: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d') no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
        }

        .login-card {
            max-width: 420px;
            width: 100%;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>

    <div class="overlay"></div>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 position-relative">

        <div class="login-card shadow-lg p-4 p-md-5">

            <!-- TITLE -->
            <h3 class="text-center fw-bold">Admin Login</h3>
            <p class="text-center text-muted small mb-4">Sign in to continue</p>

            <!-- ERROR -->
            @if(session('error'))
                <div class="alert alert-danger py-2 text-center small">
                    {{ session('error') }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('admin.auth') }}">
                @csrf

                <!-- EMAIL -->
                <div class="mb-3">
                    <label class="form-label small">Email</label>
                    <input type="email" name="email" class="form-control"
                        placeholder="admin@example.com" required>
                </div>

                <!-- PASSWORD -->
                <div class="mb-3" x-data="{ show: false }">
                    <label class="form-label small">Password</label>

                    <div class="input-group">
                        <input :type="show ? 'text' : 'password'"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required>

                        <!-- ICON BUTTON -->
                        <button type="button"
                            class="btn btn-outline-secondary"
                            @click="show = !show">

                            <!-- Eye Open -->
                            <i class="bi bi-eye" x-show="!show"></i>

                            <!-- Eye Slash -->
                            <i class="bi bi-eye-slash" x-show="show"></i>

                        </button>
                    </div>
                </div>

                <!-- FORGOT -->
                <div class="text-end mb-3">
                    <a href="#" class="text-decoration-none small">
                        Forgot password?
                    </a>
                </div>

                <!-- BUTTON -->
                <button type="submit" class="btn btn-primary w-100">
                    Sign In
                </button>
            </form>

            <!-- FOOTER -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    © {{ date('Y') }} Admin Panel
                </small>
            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>