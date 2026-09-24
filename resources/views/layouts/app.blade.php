<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siknas - @yield('title', 'Sistem Keuangan Negara')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], mono: ['SF Mono', 'ui-monospace', 'monospace'] },
                    colors: {
                        bgLight: 'var(--bg-light)', cardWhite: 'var(--card-white)',
                        textPrimary: 'var(--text-primary)', textSecondary: 'var(--text-secondary)',
                        brandBlue: '#0066CC', accentGreen: '#34C759', accentRed: '#FF3B30',
                        darkBg: '#0B0F19', darkText: '#F8FAFC'
                    },
                    boxShadow: { 'soft': '0 4px 24px rgba(0,0,0,0.04)', 'floating': '0 10px 40px rgba(0,0,0,0.08)' }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-light: #F5F5F7; --card-white: #FFFFFF;
            --text-primary: #1D1D1F; --text-secondary: #86868B;
        }
        .dark {
            --bg-light: #0B0F19; --card-white: #111827;
            --text-primary: #E2E8F0; --text-secondary: #94A3B8;
        }
        body { background-color: var(--bg-light); color: var(--text-primary); -webkit-font-smoothing: antialiased; transition: background-color 0.3s, color 0.3s; }
        .bento-card { background-color: var(--card-white); transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s, border-color 0.3s; border: 1px solid rgba(150,150,150,0.1); }
        .dark .bento-card { background-color: #111827; border-color: rgba(255,255,255,0.05); }
        .bento-card:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .dark .bento-card:hover { box-shadow: 0 10px 40px rgba(0,0,0,0.4); }
        .ticker-wrap { width: 100%; overflow: hidden; transition: background-color 0.3s; background-color: #FFFFFF; }
        .dark .ticker-wrap { background-color: #111827; }
        @keyframes ticker { 0% { transform: translate3d(0, 0, 0); } 100% { transform: translate3d(-100%, 0, 0); } }
        .ticker { display: inline-flex; white-space: nowrap; animation-iteration-count: infinite; animation-timing-function: linear; animation-name: ticker; animation-duration: 40s; }
        .chart-bar { transition: height 1s cubic-bezier(0.4, 0, 0.2, 1); }
        /* Scrollbar Styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }

        .animate-bounce-short {
            animation: bounceShort 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        @keyframes bounceShort {
            0% { transform: translate(-50%, -20px); opacity: 0; }
            100% { transform: translate(-50%, 0); opacity: 1; }
        }
        
        /* Dark Mode Map Inversion */
        .dark .leaflet-layer,
        .dark .leaflet-control-zoom-in,
        .dark .leaflet-control-zoom-out,
        .dark .leaflet-control-attribution {
            filter: invert(1) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
    </style>
    @stack('styles')
</head>
<body class="pt-24 pb-12 px-6 bg-bgLight text-textPrimary dark:bg-darkBg dark:text-darkText">
    <nav class="fixed top-0 left-0 w-full z-50 bg-bgLight/80 dark:bg-darkBg/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 flex justify-between items-center">
            <a href="/" class="flex items-center gap-2 shrink-0">
                <div class="w-8 h-8 bg-brandBlue rounded-lg flex items-center justify-center text-white"><i class="ph-bold ph-bank"></i></div>
                <span class="font-bold text-lg tracking-tight text-textPrimary hidden sm:block">SIKNAS<span class="text-brandBlue font-medium">.gov</span></span>
            </a>
            <div class="hidden lg:flex gap-4 xl:gap-6 text-sm font-medium text-textSecondary">
                <a href="/" class="hover:text-textPrimary transition-colors {{ request()->is('/') ? 'text-textPrimary font-semibold' : '' }}">Dashboard</a>
                <a href="/reports" class="hover:text-textPrimary transition-colors {{ request()->is('reports*') ? 'text-textPrimary font-semibold' : '' }}">Laporan</a>
                <a href="/geospatial" class="hover:text-textPrimary transition-colors {{ request()->is('geospatial*') ? 'text-textPrimary font-semibold' : '' }}">Geospasial</a>
                @auth
                    @if(Auth::user()->role === 'super_admin')
                    <a href="/ministries" class="hover:text-brandBlue transition-colors {{ request()->is('ministries*') ? 'text-brandBlue font-semibold' : '' }}">Kementerian</a>
                    <a href="/users" class="hover:text-brandBlue transition-colors {{ request()->is('users*') ? 'text-brandBlue font-semibold' : '' }}">Pengguna</a>
                    <a href="/approvals" class="hover:text-yellow-500 transition-colors {{ request()->is('approvals*') ? 'text-yellow-500 font-semibold' : '' }} flex items-center gap-1"><i class="ph-fill ph-stamp"></i> Otorisasi</a>
                    <a href="/api-gateway" class="hover:text-purple-500 transition-colors {{ request()->is('api-gateway*') ? 'text-purple-500 font-semibold' : '' }} flex items-center gap-1"><i class="ph-bold ph-plugs-connected"></i> API</a>
                    @endif
                    @if(Auth::user()->role === 'super_admin' || Auth::user()->role === 'auditor')
                    <a href="/audit" class="hover:text-accentRed transition-colors flex items-center gap-1 {{ request()->is('audit*') ? 'text-accentRed font-semibold' : '' }}"><i class="ph-fill ph-shield-check"></i> Audit</a>
                    @endif
                @endauth
            </div>
            <div class="flex gap-2 lg:gap-4 items-center shrink-0">
                <button id="darkModeToggle" onclick="toggleDarkMode()" class="w-9 h-9 lg:w-10 lg:h-10 rounded-full bg-gray-200 dark:bg-gray-800 text-textPrimary dark:text-darkText flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <i class="ph-fill ph-moon"></i>
                </button>
                
                @auth
                <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 mx-1 hidden xl:block"></div>
                
                <div class="hidden xl:flex items-center gap-3 mr-2">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-brandBlue to-purple-500 text-white flex items-center justify-center font-bold text-sm shadow-sm border-2 border-white dark:border-gray-800">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-textPrimary dark:text-darkText leading-none">{{ Auth::user()->name }}</span>
                        <span class="text-[10px] font-semibold text-textSecondary uppercase tracking-wider mt-0.5">
                            {{ str_replace('_', ' ', Auth::user()->role) }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" title="Logout" class="w-9 h-9 lg:w-10 lg:h-10 flex items-center justify-center bg-red-50 text-accentRed rounded-full hover:bg-red-100 transition-colors dark:bg-red-900/30">
                        <i class="ph-bold ph-power"></i>
                    </button>
                </form>
                @else
                <a href="/login" class="bg-textPrimary text-white px-5 py-2 rounded-full font-medium text-sm hover:bg-black transition-colors dark:bg-brandBlue dark:hover:bg-blue-600">
                    Login Sistem
                </a>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Flash Messages (Toasts) -->
    @if(session('success') || session('error'))
    <div id="toast-notification" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl font-medium text-sm animate-bounce-short 
        {{ session('success') ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-red-500 text-white' }}">
        @if(session('success'))
            <i class="ph-fill ph-check-circle text-xl text-green-400"></i>
            {{ session('success') }}
        @else
            <i class="ph-fill ph-warning-circle text-xl"></i>
            {{ session('error') }}
        @endif
        <button onclick="document.getElementById('toast-notification').remove()" class="ml-4 opacity-50 hover:opacity-100"><i class="ph-bold ph-x"></i></button>
    </div>
    @endif

    <!-- Global Footer -->
    <footer class="max-w-7xl mx-auto mt-12 mb-6 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-col md:flex-row justify-between items-center text-xs text-textSecondary font-mono gap-4">
        <div>
            SIKNAS Enterprise v1.0.0-beta <span class="px-2">|</span> &copy; 2026 Pusat Komando Data Nasional
        </div>
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1"><i class="ph-fill ph-check-circle text-accentGreen"></i> Sistem SPBE Aktif</span>
            <span class="flex items-center gap-1"><i class="ph-bold ph-clock"></i> <span id="server-clock"></span> WIB</span>
        </div>
    </footer>

    <!-- AI Copilot Widget -->
    <div id="ai-widget" class="fixed bottom-6 right-6 z-50">
        <!-- Chat Box (Hidden by default) -->
        <div id="ai-chatbox" class="hidden mb-4 w-[350px] bg-white dark:bg-darkBg rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 flex-col h-[450px]">
            <div class="bg-gradient-to-r from-purple-600 to-brandBlue p-4 rounded-t-2xl flex justify-between items-center text-white">
                <div class="flex items-center gap-2 font-bold">
                    <i class="ph-fill ph-robot text-2xl"></i> SIKNAS Copilot
                </div>
                <button onclick="toggleAI()" class="hover:text-gray-200"><i class="ph-bold ph-x"></i></button>
            </div>
            <div id="ai-messages" class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-900 space-y-4 text-sm">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center shrink-0"><i class="ph-fill ph-robot"></i></div>
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 dark:border-gray-700 text-textPrimary dark:text-gray-300">
                        Halo! Saya Copilot SIKNAS. Saya bisa menganalisis tren defisit, mencari anomali, atau merangkum APBN. Ada yang bisa saya bantu untuk laporan akhir Anda?
                    </div>
                </div>
            </div>
            <div class="p-3 bg-white dark:bg-darkBg border-t border-gray-100 dark:border-gray-800 rounded-b-2xl">
                <form id="ai-form" class="flex gap-2" onsubmit="event.preventDefault(); sendAIMessage();">
                    <input type="text" id="ai-input" placeholder="Tanya SIKNAS Copilot..." class="flex-1 bg-gray-100 dark:bg-gray-800 border-none rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 dark:text-white" autocomplete="off">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white w-10 h-10 rounded-xl flex items-center justify-center transition-colors"><i class="ph-bold ph-paper-plane-right"></i></button>
                </form>
            </div>
        </div>

        <!-- Floating Button -->
        <button onclick="toggleAI()" class="w-14 h-14 bg-gradient-to-r from-purple-600 to-brandBlue rounded-full shadow-lg text-white flex items-center justify-center hover:scale-110 transition-transform focus:outline-none float-right">
            <i class="ph-fill ph-sparkle text-2xl"></i>
        </button>
    </div>

    @stack('scripts')
    <script>
        function updateDarkModeIcon(isDark) {
            const btn = document.getElementById('darkModeToggle');
            if (btn) {
                btn.innerHTML = isDark ? '<i class="ph-fill ph-sun"></i>' : '<i class="ph-fill ph-moon"></i>';
            }
        }

        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateDarkModeIcon(isDark);
        }
        
        // Init icon on load
        updateDarkModeIcon(document.documentElement.classList.contains('dark'));

        // Global Clock
        setInterval(() => {
            const el = document.getElementById('server-clock');
            if(el) {
                const now = new Date();
                el.innerText = now.toLocaleTimeString('id-ID', { hour12: false });
            }
        }, 1000);

        // AI Copilot Logic
        function toggleAI() {
            const box = document.getElementById('ai-chatbox');
            box.classList.toggle('hidden');
            box.classList.toggle('flex');
            if(!box.classList.contains('hidden')) document.getElementById('ai-input').focus();
        }

        function sendAIMessage() {
            const input = document.getElementById('ai-input');
            const msg = input.value.trim();
            if(!msg) return;

            const msgs = document.getElementById('ai-messages');
            
            msgs.innerHTML += `
                <div class="flex gap-3 justify-end">
                    <div class="bg-brandBlue text-white p-3 rounded-2xl rounded-tr-none shadow-sm text-sm max-w-[80%]">${msg}</div>
                </div>
            `;
            input.value = '';
            msgs.scrollTop = msgs.scrollHeight;

            const typingId = 'typing-' + Date.now();
            msgs.innerHTML += `
                <div id="${typingId}" class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center shrink-0"><i class="ph-fill ph-robot"></i></div>
                    <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 dark:border-gray-700 text-gray-500 text-sm flex gap-1 items-center">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                    </div>
                </div>
            `;
            msgs.scrollTop = msgs.scrollHeight;

            setTimeout(async () => {
                try {
                    const csrfToken = '{{ csrf_token() }}';
                    const res = await fetch('/api/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ message: msg })
                    });
                    const data = await res.json();
                    
                    document.getElementById(typingId).remove();
                    msgs.innerHTML += `
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center shrink-0"><i class="ph-fill ph-robot"></i></div>
                            <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 dark:border-gray-700 text-textPrimary dark:text-gray-300 text-sm leading-relaxed prose prose-sm dark:prose-invert">
                                ${marked.parse(data.reply)}
                            </div>
                        </div>
                    `;
                } catch (err) {
                    document.getElementById(typingId).remove();
                    msgs.innerHTML += `<div class="text-red-500 text-xs">Error connecting to AI Server.</div>`;
                }
                msgs.scrollTop = msgs.scrollHeight;
            }, 100);
        }
    </script>
</body>
</html>
