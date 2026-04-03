<div>
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
                <button wire:click="switchTab('models')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all"
                    @class([
                        'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' => $activeTab === 'models',
                        'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' => $activeTab !== 'models'
                    ])>
                    Models
                </button>
                <button wire:click="switchTab('settings')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all"
                    @class([
                        'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' => $activeTab === 'settings',
                        'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' => $activeTab !== 'settings'
                    ])>
                    Settings
                </button>
            </div>

            <!-- Dark mode toggle handled by Alpine in layout -->
            <div x-data>
                <button @click="$store.darkMode = !$store.darkMode; document.documentElement.classList.toggle('dark')"
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg x-show="!document.documentElement.classList.contains('dark')" class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="document.documentElement.classList.contains('dark')" x-cloak class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- ═══════════════════════════
             MODELS TAB
        ═══════════════════════════ -->
        @if($activeTab === 'models')
        <div>
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
                    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md hover:border-brand-400 dark:hover:border-brand-600 transition-all duration-200">
                        
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
                                <button type="button" onclick="let i = document.getElementById('count-{{ md5($model['fqcn']) }}'); i.value = Math.max(1, parseInt(i.value) - 1); i.dispatchEvent(new Event('input'))"
                                    class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-bold">−</button>
                                
                                <input type="number" wire:model="counts.{{ $model['fqcn'] }}" id="count-{{ md5($model['fqcn']) }}" min="1" max="10000"
                                    class="flex-1 text-center h-8 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                                
                                <button type="button" onclick="let i = document.getElementById('count-{{ md5($model['fqcn']) }}'); i.value = Math.min(10000, parseInt(i.value) + 1); i.dispatchEvent(new Event('input'))"
                                    class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-bold">+</button>
                            </div>
                        </div>

                        <!-- Seed button -->
                        <button wire:click="seedModel('{{ addslashes($model['fqcn']) }}')"
                            wire:loading.attr="disabled"
                            wire:target="seedModel('{{ addslashes($model['fqcn']) }}')"
                            class="w-full h-10 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 text-white text-sm font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            
                            <svg wire:loading wire:target="seedModel('{{ addslashes($model['fqcn']) }}')" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            
                            <svg wire:loading.remove wire:target="seedModel('{{ addslashes($model['fqcn']) }}')" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            
                            <span wire:loading.remove wire:target="seedModel('{{ addslashes($model['fqcn']) }}')">Seed</span>
                            <span wire:loading wire:target="seedModel('{{ addslashes($model['fqcn']) }}')">Seeding…</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        <!-- ═══════════════════════════
             SETTINGS TAB
        ═══════════════════════════ -->
        @if($activeTab === 'settings')
        <div>
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Settings</h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">Configure global defaults for data generation. These update your <code class="text-brand-600 dark:text-brand-400">.env</code> file.</p>
            </div>

            <div class="max-w-2xl">
                <form wire:submit.prevent="saveSettings" class="space-y-6">

                    <!-- Locale -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs">🌐</span>
                            General
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Faker Locale</label>
                                <input type="text" wire:model="settings.locale" placeholder="en_US"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Default Row Count</label>
                                <input type="number" wire:model="settings.default_count" min="1" max="10000"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
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
                                <input type="text" wire:model="settings.email_domain" placeholder="example.com"
                                    class="flex-1 h-10 px-3 rounded-r-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                            </div>
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
                            <input type="text" wire:model="settings.phone_country_code" placeholder="+1"
                                class="w-40 h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full h-11 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 text-white text-sm font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed transition-all hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        
                        <svg wire:loading class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        
                        <span wire:loading.remove>Save Settings</span>
                        <span wire:loading>Saving…</span>
                    </button>
                </form>
            </div>
        </div>
        @endif

    </main>
</div>
