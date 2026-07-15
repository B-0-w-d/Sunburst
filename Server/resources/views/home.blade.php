<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunburst Server</title>
    @vite(['resources/css/home.css'])
</head>
<body>

    <div class="card">
        <!-- Pulse Status Indicator -->
        <div class="status-container">
            <span class="pulse-wrapper">
                <span class="pulse-ping"></span>
                <span class="pulse-dot"></span>
            </span>
            <span class="status-text">Online</span>
        </div>

        <!-- Main Status Message -->
        <h1 class="title">Sunburst Server</h1>
        <p class="description">
            The API environment is up and running successfully.
        </p>

        <!-- Dynamic Environment Specs -->
        <div class="specs-grid">
            <div>Laravel: v{{ app()->version() }}</div>
            <div class="text-right">PHP: v{{ PHP_VERSION }}</div>
            <div>Env: {{ app()->environment() }}</div>
            <div class="text-right">Status: 200 OK</div>
        </div>

        <!-- Built By Footer -->
        <div class="footer">
            Built by <a href="#" class="footer-link">Made by Tin Phan, Gia Bao, Tuan Tran</a>
        </div>
    </div>

</body>
</html>
