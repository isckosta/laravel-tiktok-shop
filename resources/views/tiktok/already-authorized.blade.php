<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja já autorizada - TikTok Shop</title>
    <meta name="description" content="Esta loja já foi autorizada anteriormente na TikTok Shop.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'warning': '#f59e0b',
                        'warning-foreground': '#fff7ed',
                        'tiktok': '#fe2c55',
                        'tiktok-foreground': '#ffffff',
                        'background': '#fefefe',
                        'foreground': '#0f172a',
                        'card': '#ffffff',
                        'card-foreground': '#0f172a',
                        'muted': '#f8fafc',
                        'muted-foreground': '#64748b'
                    }
                }
            }
        };
    </script>
    <style>
        .gradient-warning {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .shadow-warning {
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.2);
        }

        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }

        body {
            background: linear-gradient(135deg, #fefefe, #f8fafc);
        }
    </style>
</head>
<body class="min-h-screen">
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl mx-auto bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border-0">
        <div class="p-12 text-center space-y-8">
            <div class="flex justify-center">
                <div class="relative">
                    <div
                        class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-warning">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                  d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 text-3xl animate-pulse-slow">
                        ⚠️
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h1 class="text-3xl font-bold text-gray-900">
                    Loja já autorizada
                </h1>
                <p class="text-lg text-gray-600 leading-relaxed max-w-md mx-auto">
                    @if(isset($storeName) && $storeName)
                        A loja <span class="font-semibold">{{ $storeName }}</span>
                    @else
                        Sua loja
                    @endif
                    já foi vinculada anteriormente ao TikTok Shop.
                </p>
            </div>

            @if(isset($storeName) && $storeName)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-center justify-center gap-2 text-yellow-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path>
                        </svg>
                        <span class="font-medium text-lg">{{ $storeName }}</span>
                    </div>
                </div>
            @endif

            <button
                onclick="if (window.opener) { window.close(); } else { window.location.href='{{ url('/') }}'; }"
                class="inline-flex items-center justify-center px-12 py-3 text-lg font-medium text-white gradient-warning rounded-lg shadow-warning hover:shadow-lg transition-smooth focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Fechar
            </button>
        </div>
    </div>
</div>
</body>
</html>
