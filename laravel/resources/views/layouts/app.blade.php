<!DOCTYPE html>
<html>
<head>
    <title>SMPP Platform - Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: '#2563eb',
            }
          }
        }
      }
    </script>
    <style>
      body { font-family: system-ui, -apple-system, sans-serif; }
      .btn { @apply px-4 py-2 rounded font-medium; }
      .btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    @auth
    <nav class="bg-white shadow mb-6">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">SMPP Platform</h1>
                <div class="space-x-4">
                    <a href="/admin/clients" class="text-gray-600 hover:text-gray-900">Clients</a>
                    <a href="/admin/providers" class="text-gray-600 hover:text-gray-900">Providers</a>
                    <a href="/admin/routes" class="text-gray-600 hover:text-gray-900">Routes</a>
                    <a href="/admin/messages" class="text-gray-600 hover:text-gray-900">Messages</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth
    <main class="max-w-7xl mx-auto px-4">@yield('content')</main>
</body>
</html>