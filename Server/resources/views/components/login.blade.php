<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Dashboard</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <!-- New wrapper for the background -->
    <div class="login-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div class="login-modal">
            <div class="modal-header">
                <h4 class="modal-title">Welcome to Sunburst</h4>
                <p style="color: #64748b; font-size: 14px; margin-top: 8px;">This is the management site, so only Sunburst members can sign in.</p>
            </div>

            <form id="authLoginModalForm" onsubmit="handleFormLogin(event)">
                @csrf
                <div id="login-error-alert" style="display:none; background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:20px; font-size:13px; text-align:center;"></div>

                <div class="form-group">
                    <label class="form-label" for="login-email">Your email:</label>
                    <input type="email" id="login-email" class="form-input" required placeholder="rennguyen@gmail.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="login-password">Your pass:</label>
                    <input type="password" id="login-password" class="form-input" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-save">Lez gooooo</button>
            </form>
        </div>
    </div>

    @include('scripts.loginScript')
</body>

</html>
