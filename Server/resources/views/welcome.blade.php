<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title class="text-red">Sunburst Server</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen font-sans">

    <div class="text-center p-8 max-w-md w-full mx-4 bg-slate-800 rounded-2xl shadow-xl border border-slate-700">
        <!-- Pulse Status Indicator -->
        <div class="flex items-center justify-center space-x-2 mb-4">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Online</span>
        </div>

        <!-- Main Status Message -->
        <h1 class="text-3xl font-bold tracking-tight mb-2 text-white">
            Sunburst Server
        </h1>
        <p class="text-slate-400 mb-6 text-sm">
            The API environment is up and running successfully.
        </p>

        <!-- Dynamic Environment Specs -->
        <div class="grid grid-cols-2 gap-2 text-left text-xs font-mono text-slate-500 border-t border-slate-700/50 pt-4 mb-4">
            <div>Laravel: v{{ app()->version() }}</div>
            <div class="text-right">PHP: v{{ PHP_VERSION }}</div>
            <div>Env: {{ app()->environment() }}</div>
            <div class="text-right">Status: 200 OK</div>
        </div>

        <!-- Built By Footer -->
        <div class="pt-4 border-t border-slate-700/50 text-xs text-slate-400">
            Built by <a href="#" class="font-medium text-amber-400 hover:underline">Made by Tin Phan, Gia Bao, Tuan Tran</a>
        </div>
    </div>

</body>
</html>
