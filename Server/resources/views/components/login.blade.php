{{-- resources/views/login_portal.blade.php --}}
<x-navbar title="Authentication Required">

    <!-- The backdrop is forced open via CSS since they HAVE to sign in here -->
    <div class="modal-backdrop open" id="authLoginModal">
        <div class="modal-window" style="max-width: 420px; margin-top: 10vh;">
            <div class="modal-header">
                <h4 class="modal-title">Management Console Sign In</h4>
            </div>

            <form id="authLoginModalForm" onsubmit="handleFormLogin(event)">
                @csrf
                <div class="modal-body">
                    <div id="login-error-alert" style="display: none; background-color: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 500;"></div>

                    <div class="form-group" style="margin-bottom: 16px; text-align: left;">
                        <label class="form-label" for="login-email" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Account Email</label>
                        <input type="email" id="login-email" name="email" class="form-input" required placeholder="name@company.com" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 8px; text-align: left;">
                        <label class="form-label" for="login-password" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Security Password</label>
                        <input type="password" id="login-password" name="password" class="form-input" required placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-save" style="background-color: #4f46e5; color: white; border: none; padding: 10px 18px; border-radius: 6px; width: 100%; cursor: pointer; font-weight: 600;">Verify Identity</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function handleFormLogin(event) {
            event.preventDefault();
            const errorAlert = document.getElementById('login-error-alert');
            errorAlert.style.display = 'none';

            fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: document.getElementById('login-email').value,
                    password: document.getElementById('login-password').value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // SUCCESS: Route them directly to the protected home root page!
                    window.location.href = '/';
                } else {
                    errorAlert.textContent = data.message || 'Invalid credentials.';
                    errorAlert.style.display = 'block';
                }
            })
            .catch(err => {
                errorAlert.textContent = 'Server connectivity fault.';
                errorAlert.style.display = 'block';
            });
        }
    </script>
</x-navbar>
