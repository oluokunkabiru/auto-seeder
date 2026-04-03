<!DOCTYPE html>
<html lang="en" class="h-full" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" x-init="$watch('darkMode', v => { localStorage.setItem('theme', v ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', v); }); document.documentElement.classList.toggle('dark', darkMode);" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Auto Seeder Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .toast-enter { animation: slideIn 0.3s ease; }
        @keyframes slideIn {
            from { transform: translateY(100%); opacity: 0; }
            to   { transform: translateY(0);   opacity: 1; }
        }
    </style>
</head>

<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 transition-colors duration-300"
      x-data="dashboard()">

    <!-- ═══════════════════════════════════════
         NAVBAR
    ═══════════════════════════════════════ -->
    <nav class="sticky top-0 z-50 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16M12 12v4m0 0h2m-2 0H10"/>
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-lg tracking-tight">Auto Seeder</span>
                    <span class="ml-2 text-xs font-medium px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-400">Dashboard</span>
                </div>
            </div>

            <!-- Nav tabs -->
            <div class="hidden sm:flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-xl p-1">
                <button @click="activeTab = 'models'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all"
                    :class="activeTab === 'models'
                        ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'">
                    Models
                </button>
                <button @click="activeTab = 'settings'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all"
                    :class="activeTab === 'settings'
                        ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'">
                    Settings
                </button>
            </div>

            <!-- Dark mode toggle -->
            <button @click="darkMode = !darkMode"
                class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg x-show="!darkMode" class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" x-cloak class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- ═══════════════════════════
             MODELS TAB
        ═══════════════════════════ -->
        <div x-show="activeTab === 'models'" x-transition>

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Model Seeder</h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">Pick a model, set the row count, and click <strong>Seed</strong> to generate random data instantly.</p>
            </div>

            @if(empty($models))
                <!-- Empty state -->
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300">No models found</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-500 mt-1 max-w-sm">
                        Make sure your models are in <code class="text-brand-600 dark:text-brand-400">app/Models</code> or update <code class="text-brand-600 dark:text-brand-400">config/auto-seeder.php</code>.
                    </p>
                </div>
            @else
                <!-- Model grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($models as $model)
                    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md hover:border-brand-400 dark:hover:border-brand-600 transition-all duration-200"
                         x-data="modelCard('{{ $model['fqcn'] }}', '{{ $model['name'] }}')">

                        <!-- Model icon + name -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $model['name'] }}</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $model['table'] ?? '—' }}</p>
                            </div>
                        </div>

                        <!-- Count input -->
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Rows to generate</label>
                            <div class="flex items-center gap-2">
                                <button @click="count = Math.max(1, count - 1)"
                                    class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-bold">−</button>
                                <input type="number" x-model.number="count" min="1" max="10000"
                                    class="flex-1 text-center h-8 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                                <button @click="count = Math.min(10000, count + 1)"
                                    class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-bold">+</button>
                            </div>
                        </div>

                        <!-- Status indicator -->
                        <div x-show="status" x-cloak class="mb-3 text-xs font-medium px-2.5 py-1.5 rounded-lg text-center transition-all"
                             :class="{
                                'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-400': lastSuccess,
                                'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400': !lastSuccess
                             }"
                             x-text="status"></div>

                        <!-- Seed button -->
                        <button @click="seed()"
                            :disabled="loading"
                            class="w-full h-10 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 text-white text-sm font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span x-text="loading ? 'Seeding…' : 'Seed'"></span>
                        </button>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ═══════════════════════════
             SETTINGS TAB
        ═══════════════════════════ -->
        <div x-show="activeTab === 'settings'" x-transition x-cloak>
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Settings</h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">Configure global defaults for data generation. These update your <code class="text-brand-600 dark:text-brand-400">.env</code> file.</p>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="saveSettings()" class="space-y-6">

                    <!-- Locale -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs">🌐</span>
                            General
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Faker Locale</label>
                                <input type="text" name="locale" x-model="settings.locale" placeholder="en_US"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                                <p class="text-xs text-slate-400 mt-1">e.g. en_US, fr_FR, de_DE</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Default Row Count</label>
                                <input type="number" name="default_count" x-model.number="settings.default_count" min="1" max="10000"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                                <p class="text-xs text-slate-400 mt-1">Used when count is not specified</p>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-xs">✉️</span>
                            Email Format
                        </h2>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Email Domain</label>
                            <div class="flex items-center">
                                <span class="h-10 px-3 flex items-center text-sm text-slate-400 bg-slate-100 dark:bg-slate-800 border border-r-0 border-slate-200 dark:border-slate-700 rounded-l-xl">@</span>
                                <input type="text" name="email_domain" x-model="settings.email_domain" placeholder="example.com"
                                    class="flex-1 h-10 px-3 rounded-r-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Leave blank for fully random emails</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 text-xs">📱</span>
                            Phone Format
                        </h2>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Country Code</label>
                            <input type="text" name="phone_country_code" x-model="settings.phone_country_code" placeholder="+1"
                                class="w-40 h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                            <p class="text-xs text-slate-400 mt-1">e.g. +1, +234, +44. Leave blank to skip</p>
                        </div>
                    </div>

                    <!-- Save status -->
                    <div x-show="settingsStatus" x-cloak
                         class="px-4 py-3 rounded-xl text-sm font-medium text-center transition-all"
                         :class="settingsSuccess ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'"
                         x-text="settingsStatus"></div>

                    <button type="submit" :disabled="savingSettings"
                        class="w-full h-11 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 text-white text-sm font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        <svg x-show="savingSettings" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="savingSettings ? 'Saving…' : 'Save Settings'"></span>
                    </button>
                </form>
            </div>
        </div>

    </main>

    <!-- ═════════════════════════════
         TOAST NOTIFICATIONS
    ═════════════════════════════ -->
    <div class="fixed bottom-5 right-5 z-50 space-y-2" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg toast-enter text-sm font-medium max-w-xs"
                 :class="toast.type === 'success'
                     ? 'bg-brand-600 text-white'
                     : 'bg-red-600 text-white'">
                <svg x-show="toast.type === 'success'" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <!-- ═══════════════════ SCRIPTS ═══════════════════ -->
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function dashboard() {
            return {
                activeTab: '{{ $activeTab ?? "models" }}',
                darkMode: localStorage.getItem('theme') === 'dark',
                toasts: [],
                savingSettings: false,
                settingsStatus: '',
                settingsSuccess: true,

                settings: {
                    locale:             '{{ config("auto-seeder.locale", "en_US") }}',
                    default_count:      {{ config("auto-seeder.default_count", 1) }},
                    email_domain:       '{{ config("auto-seeder.columns.email.domain", "") }}',
                    phone_country_code: '{{ config("auto-seeder.columns.phone.country_code", "") }}',
                },

                addToast(message, type = 'success') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                },

                async saveSettings() {
                    this.savingSettings = true;
                    this.settingsStatus = '';
                    try {
                        const res = await fetch('{{ route("auto-seeder.settings.save") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify(this.settings),
                        });
                        const data = await res.json();
                        this.settingsSuccess = data.success;
                        this.settingsStatus = data.message;
                        this.addToast(data.message, data.success ? 'success' : 'error');
                    } catch (e) {
                        this.settingsStatus = 'Network error. Please try again.';
                        this.settingsSuccess = false;
                        this.addToast('Network error.', 'error');
                    } finally {
                        this.savingSettings = false;
                    }
                },
            };
        }

        function modelCard(fqcn, name) {
            return {
                fqcn,
                name,
                count: 1,
                loading: false,
                status: '',
                lastSuccess: true,

                async seed() {
                    if (this.loading) return;
                    this.loading = true;
                    this.status = '';

                    try {
                        const res = await fetch('{{ route("auto-seeder.seed") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify({ model: this.fqcn, count: this.count }),
                        });
                        const data = await res.json();
                        this.lastSuccess = data.success;
                        this.status = data.message;

                        // Also show global toast
                        const dashboard = Alpine.$data(document.body);
                        dashboard?.addToast?.(data.message, data.success ? 'success' : 'error');
                    } catch (e) {
                        this.lastSuccess = false;
                        this.status = 'Network error. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</body>
</html>
