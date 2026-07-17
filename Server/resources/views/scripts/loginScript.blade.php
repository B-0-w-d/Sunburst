<script>
    function handleFormLogin(event) {
        event.preventDefault();
        const errorAlert = document.getElementById('login-error-alert');
        errorAlert.style.display = 'none';

        fetch('/login', {
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
                window.location.href = '/';
            } else {
                errorAlert.textContent = data.message || 'Invalid credentials.';
                errorAlert.style.display = 'block';
            }
        })
        .catch(() => {
            errorAlert.textContent = 'Server connectivity fault.';
            errorAlert.style.display = 'block';
        });
    }
</script>
