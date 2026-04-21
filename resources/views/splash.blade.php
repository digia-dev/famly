<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Famly — Modern Wealth Orchestrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes pulse-custom {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(0.98); }
        }
        .animate-brand { animation: pulse-custom 3s ease-in-out infinite; }
    </style>
</head>
<body class="bg-[#00AA13] h-screen flex flex-col items-center justify-center overflow-hidden">
    
    <div class="relative flex flex-col items-center animate-brand">
        <!-- Brand Icon -->
        <div class="w-20 h-20 bg-white rounded-[2rem] flex items-center justify-center shadow-2xl mb-8">
            <span class="text-[#00AA13] font-black text-4xl tracking-tighter">F</span>
        </div>

        <!-- Typography -->
        <h1 class="text-white text-4xl font-black tracking-tighter">Famly.</h1>
    </div>

    <!-- Loading Indicator -->
    <div class="absolute bottom-16">
        <div class="flex gap-2">
            <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
            <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
        </div>
    </div>

    <script>
        // Redirect to dashboard or login after 2 seconds
        setTimeout(() => {
            window.location.href = "{{ Auth::check() ? route('admin.dashboard') : route('login') }}";
        }, 2200);
    </script>
</body>
</html>
