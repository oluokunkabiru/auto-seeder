<?php

namespace Oluokunkabiru\AutoSeeder\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Oluokunkabiru\AutoSeeder\AutoSeeder;
use Oluokunkabiru\AutoSeeder\ModelDiscovery;
use Throwable;

class AutoSeederController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index()
    {
        $config  = config('auto-seeder', []);
        $modelsDir = $config['models_path'] ?? app_path('Models');
        $namespace = $config['models_namespace'] ?? 'App\\Models';

        $models = ModelDiscovery::discover($modelsDir, $namespace);

        return view('auto-seeder::dashboard', compact('models', 'config'));
    }

    /**
     * Trigger seeding for a model (AJAX JSON).
     */
    public function seed(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'count' => 'nullable|integer|min:1|max:10000',
        ]);

        $fqcn  = $request->input('model');
        $count = max(1, (int) ($request->input('count', 1)));

        if (!class_exists($fqcn)) {
            return response()->json(['success' => false, 'message' => "Model class not found: {$fqcn}"], 422);
        }

        try {
            $inserted = AutoSeeder::fromModel(new $fqcn())->seed($count);

            return response()->json([
                'success' => true,
                'message' => "Successfully inserted {$inserted} row(s).",
                'count'   => $inserted,
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show settings form.
     */
    public function settingsShow()
    {
        $config = config('auto-seeder', []);
        return view('auto-seeder::dashboard', ['models' => [], 'config' => $config, 'activeTab' => 'settings']);
    }

    /**
     * Save settings to .env file.
     */
    public function settingsSave(Request $request)
    {
        $request->validate([
            'locale'             => 'nullable|string|max:10',
            'default_count'      => 'nullable|integer|min:1',
            'email_domain'       => 'nullable|string|max:100',
            'phone_country_code' => 'nullable|string|max:10',
        ]);

        $map = [
            'AUTO_SEEDER_LOCALE'             => $request->input('locale', 'en_US'),
            'AUTO_SEEDER_DEFAULT_COUNT'      => $request->input('default_count', 1),
            'AUTO_SEEDER_EMAIL_DOMAIN'       => $request->input('email_domain', ''),
            'AUTO_SEEDER_PHONE_COUNTRY_CODE' => $request->input('phone_country_code', ''),
        ];

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return response()->json(['success' => false, 'message' => '.env file not found.'], 500);
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

        // Clear Laravel config cache
        if (function_exists('artisan')) {
            \Artisan::call('config:clear');
        }

        return response()->json(['success' => true, 'message' => 'Settings saved successfully.']);
    }
}
