<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunburst</title>

    @vite([
        'resources/shared/global.css',
        'resources/css/pages/index.css',
        'resources/css/pages/home.css',
        'resources/js/app.js'
    ])
</head>
<body>

    @yield('content')

</body>
</html>