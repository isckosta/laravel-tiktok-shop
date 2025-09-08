<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado por conectar sua loja - TikTok Shop</title>
    <meta name="description"
          content="Autorização TikTok Shop concluída com sucesso. Sua loja está conectada e pronta para vender.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'success': '#16a34a',
                        'success-foreground': '#fef7f7',
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
        .gradient-success {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .shadow-success {
            box-shadow: 0 10px 25px -5px rgba(22, 163, 74, 0.2);
        }

        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-bounce-slow {
            animation: bounce 2s infinite;
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
                        class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-success">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                  d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 text-3xl animate-bounce-slow">
                        🎉
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h1 class="text-3xl font-bold text-gray-900">
                    Obrigado por conectar sua loja!
                </h1>
                <p class="text-lg text-gray-600 leading-relaxed max-w-md mx-auto">
                    A autorização foi concluída com sucesso.
                    @if(isset($storeName) && $storeName)
                        {{ $storeName }}
                    @else
                        Sua loja
                    @endif
                    está agora conectada ao TikTok Shop e pronta para começar a vender.
                </p>
            </div>

            @if(isset($storeName) && $storeName && $storeName !== 'Sua loja')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center justify-center gap-2 text-green-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="font-medium text-lg">{{ $storeName }}</span>
                    </div>
                </div>
            @endif

            <div class="pt-6">
                <button
                    onclick="window.close()"
                    class="inline-flex items-center justify-center px-12 py-3 text-lg font-medium text-white gradient-success rounded-lg shadow-success hover:shadow-lg transition-smooth focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
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
</div>
</body>
</html>
