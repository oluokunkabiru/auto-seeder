<?php

namespace Oluokunkabiru\AutoSeeder\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Oluokunkabiru\AutoSeeder\AutoSeeder;
use Oluokunkabiru\AutoSeeder\ModelDiscovery;
use Throwable;

#[Layout('auto-seeder::dashboard-layout')]
class Dashboard extends Component
{
    public array $models = [];
    public array $counts = [];
    public array $dbCounts = [];
    
    public string $activeTab = 'models';
    public array $settings = [
        'locale' => 'en_US',
        'default_count' => 1,
        'email_domain' => '',
        'phone_country_code' => '',
    ];

    public function mount()
    {
        if (request()->has('tab') && request()->query('tab') === 'settings') {
            $this->activeTab = 'settings';
        }

        $config  = config('auto-seeder', []);
        $modelsDir = !empty($config['models_path']) ? $config['models_path'] : app_path('Models');
        $namespace = !empty($config['models_namespace']) ? $config['models_namespace'] : 'App\\Models';

        $this->models = ModelDiscovery::discover($modelsDir, $namespace);
        $defaultCount = (int) config('auto-seeder.default_count', 1);

        foreach ($this->models as $model) {
            $this->counts[$model['fqcn']] = $defaultCount;
            // Get actual DB count
            try {
                $fqcn = $model['fqcn'];
                $classObj = new $fqcn();
                $this->dbCounts[$fqcn] = method_exists($classObj, 'count') ? $classObj->count() : 0;
            } catch (\Throwable $e) {
                $this->dbCounts[$model['fqcn']] = '?';
            }
        }

        $this->settings['locale'] = config('auto-seeder.locale', 'en_US');
        $this->settings['default_count'] = $defaultCount;
        $this->settings['email_domain'] = config('auto-seeder.columns.email.domain', '');
        $this->settings['phone_country_code'] = config('auto-seeder.columns.phone.country_code', '');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function seedModel(string $fqcn)
    {
        $count = max(1, (int) ($this->counts[$fqcn] ?? 1));

        if (!class_exists($fqcn)) {
            $this->dispatch('auto-seeder:toast', type: 'error', message: "Model class not found: {$fqcn}");
            return;
        }

        try {
            $inserted = AutoSeeder::fromModel(new $fqcn())->seed($count);
            $this->dispatch('auto-seeder:toast', type: 'success', message: "Successfully inserted {$inserted} row(s).");
        } catch (Throwable $e) {
            $this->dispatch('auto-seeder:toast', type: 'error', message: $e->getMessage());
        }
    }

    public function saveSettings()
    {
        $map = [
            'AUTO_SEEDER_LOCALE'             => $this->settings['locale'] ?: 'en_US',
            'AUTO_SEEDER_DEFAULT_COUNT'      => $this->settings['default_count'] ?: 1,
            'AUTO_SEEDER_EMAIL_DOMAIN'       => $this->settings['email_domain'],
            'AUTO_SEEDER_PHONE_COUNTRY_CODE' => $this->settings['phone_country_code'],
        ];

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->dispatch('auto-seeder:toast', type: 'error', message: '.env file not found.');
            return;
        }

        $env = file_get_contents($envPath);

        foreach ($map as $key => $value) {
            $value   = (string) $value;
            $escaped = str_contains($value, ' ') ? "\"{$value}\"" : $value;
            $line    = "{$key}={$escaped}";

            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", $line, $env);
            } else {
                $env .= "\n{$line}";
            }
        }

        file_put_contents($envPath, $env);

        if (function_exists('artisan')) {
            \Artisan::call('config:clear');
        }

        $this->dispatch('auto-seeder:toast', type: 'success', message: 'Settings saved successfully.');
    }

    public function render()
    {
        return view('auto-seeder::livewire.dashboard');
    }
}
