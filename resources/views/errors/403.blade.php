<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md text-center">
            <div class="text-6xl text-red-500 mb-4">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Acceso Denegado</h1>
            <p class="text-gray-600 mb-6">{{ $exception->getMessage() ?? 'No tienes permiso para acceder a esta página.' }}</p>
            <a href="{{ url()->previous() }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Volver
            </a>
        </div>
    </div>
</body>
</html>