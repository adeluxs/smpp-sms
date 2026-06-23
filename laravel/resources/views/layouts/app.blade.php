<!DOCTYPE html>
<html>
<head>
    <title>SMPP Platform - Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow mb-4">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between">
                <h1 class="text-xl font-bold">SMPP Platform Admin</h1>
                <div>
                    <a href="/admin/clients" class="px-3 py-1 text-sm">Clients</a>
                    <a href="/admin/routes" class="px-3 py-1 text-sm">Routes</a>
                    <a href="/admin/messages" class="px-3 py-1 text-sm">Messages</a>
                    <a href="/admin/reports" class="px-3 py-1 text-sm">Reports</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4">@yield('content')</main>
</body>
</html>