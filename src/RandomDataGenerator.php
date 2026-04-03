<?php

namespace Oluokunkabiru\AutoSeeder;

use Faker\Factory;
use Faker\Generator;

class RandomDataGenerator
{
    private Generator $faker;

    /**
     * Per-column format overrides.
     * Key: column name (exact or partial match, lowercase).
     * Value: array of options, e.g.:
     *   ['domain' => 'example.com']       — for email columns
     *   ['country_code' => '+234']         — for phone columns
     *
     * @var array<string, array<string, mixed>>
     */
    private array $columnOptions = [];

    public function __construct(string $locale = 'en_US')
    {
        $this->faker = Factory::create($locale);
    }

    /**
     * Set per-column generation options.
     *
     * @param  array<string, array<string, mixed>> $options
     * @return $this
     */
    public function setColumnOptions(array $options): static
    {
        foreach ($options as $col => $cfg) {
            $this->columnOptions[strtolower($col)] = $cfg;
        }
        return $this;
    }

    /**
     * Retrieve the configured options for a specific column name.
     */
    private function optionsFor(string $columnName): array
    {
        if (isset($this->columnOptions[$columnName])) {
            return $this->columnOptions[$columnName];
        }
        foreach ($this->columnOptions as $key => $cfg) {
            if (str_contains($columnName, $key)) {
                return $cfg;
            }
        }
        return [];
    }

    /**
     * Generate a random value for a given column definition.
     *
     * @param  array $column  { name, type, nullable, length, precision, scale, values, unsigned, key, extra }
     * @return mixed
     */
    public function generate(array $column)
    {
        // If the column is nullable AND has absolutely no SQL default value,
        // leave it as null natively as requested by the user,
        // UNLESS it's a standard framework timestamp which we always want seeded.
        if ($column['nullable'] && ($column['default'] ?? null) === null) {
            if (!in_array(strtolower($column['name']), ['created_at', 'updated_at'])) {
                return null;
            }
        }

        // Return null for nullable columns that DO have a default ~5% of the time
        if ($column['nullable'] && $this->faker->boolean(5)) {
            return null;
        }

        $name   = strtolower($column['name']);
        $type   = strtolower($column['type']);
        $length = $column['length'] ?? null;

        // ---------------------------------------------------------------
        // Strict Type Guards: Do not apply string name-heuristics to
        // numeric or date columns to avoid SQL insertion crashes.
        // ---------------------------------------------------------------
        if (str_contains($type, 'time') || str_contains($type, 'date') || $type === 'year') {
            return $this->generateByType($type, $column);
        }

        $numericTypes = ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'decimal', 'numeric', 'float', 'double'];
        if (in_array($type, $numericTypes, true)) {
            if ($this->nameMatches($name, ['amount', 'price', 'cost', 'salary', 'fee', 'balance', 'total'])) {
                return $this->faker->randomFloat(2, 0, 100000);
            }
            if ($this->nameMatches($name, ['age'])) {
                return $this->faker->numberBetween(1, 100);
            }
            return $this->generateByType($type, $column);
        }

        // ---------------------------------------------------------------
        // Name-based heuristics for string-like columns
        // ---------------------------------------------------------------
        if ($this->nameMatches($name, ['email'])) {
            $opts   = $this->optionsFor($name);
            $domain = $opts['domain'] ?? null;
            $email  = $domain
                ? $this->faker->unique()->userName() . "@{$domain}"
                : $this->faker->unique()->safeEmail();
            return $this->truncate($email, $length);
        }
        if ($this->nameMatches($name, ['first_name', 'firstname'])) {
            return $this->truncate($this->faker->firstName(), $length);
        }
        if ($this->nameMatches($name, ['last_name', 'lastname', 'surname'])) {
            return $this->truncate($this->faker->lastName(), $length);
        }
        if ($this->nameMatches($name, ['name', 'full_name', 'fullname'])) {
            return $this->truncate($this->faker->name(), $length);
        }
        if ($this->nameMatches($name, ['phone', 'telephone', 'mobile', 'phone_number'])) {
            $opts        = $this->optionsFor($name);
            $countryCode = $opts['country_code'] ?? null;
            $number      = $this->faker->phoneNumber();
            if ($countryCode) {
                $localNumber = ltrim(preg_replace('/[^0-9]/', '', $number), '0');
                $number = rtrim($countryCode, ' ') . $localNumber;
            }
            return $this->truncate($number, $length);
        }
        if ($this->nameMatches($name, ['address', 'street'])) {
            return $this->truncate($this->faker->streetAddress(), $length);
        }
        if ($this->nameMatches($name, ['city'])) {
            return $this->truncate($this->faker->city(), $length);
        }
        if ($this->nameMatches($name, ['state', 'province'])) {
            return $this->truncate($this->faker->state(), $length);
        }
        if ($this->nameMatches($name, ['country'])) {
            return $this->truncate($this->faker->country(), $length);
        }
        if ($this->nameMatches($name, ['zipcode', 'zip_code', 'postal_code', 'postcode'])) {
            return $this->truncate($this->faker->postcode(), $length);
        }
        if ($this->nameMatches($name, ['url', 'website', 'link'])) {
            return $this->truncate($this->faker->url(), $length);
        }
        if ($this->nameMatches($name, ['username', 'user_name'])) {
            return $this->truncate($this->faker->userName(), $length);
        }
        if ($this->nameMatches($name, ['password'])) {
            // bcrypt hash is always 60 chars
            return password_hash($this->faker->password(8, 16), PASSWORD_BCRYPT);
        }
        if ($this->nameMatches($name, ['title'])) {
            return $this->truncate($this->faker->sentence(3), $length);
        }
        if ($this->nameMatches($name, ['description', 'body', 'content', 'bio', 'summary', 'note', 'notes', 'remark', 'remarks', 'comment', 'comments'])) {
            return $this->faker->paragraph();
        }
        if ($this->nameMatches($name, ['slug'])) {
            return $this->truncate($this->faker->slug(), $length);
        }
        if ($this->nameMatches($name, ['uuid'])) {
            return $this->faker->uuid();
        }
        if ($this->nameMatches($name, ['ip', 'ip_address', 'ipaddress'])) {
            return $this->faker->ipv4();
        }
        if ($this->nameMatches($name, ['latitude', 'lat'])) {
            return $this->faker->latitude();
        }
        if ($this->nameMatches($name, ['longitude', 'lon', 'lng'])) {
            return $this->faker->longitude();
        }
        if ($this->nameMatches($name, ['color', 'colour'])) {
            return $this->truncate($this->faker->hexColor(), $length);
        }
        if ($this->nameMatches($name, ['amount', 'price', 'cost', 'salary', 'fee', 'balance', 'total'])) {
            return $this->faker->randomFloat(2, 0, 100000);
        }
        if ($this->nameMatches($name, ['age'])) {
            return $this->faker->numberBetween(1, 100);
        }
        if ($this->nameMatches($name, ['gender', 'sex'])) {
            return $this->truncate($this->faker->randomElement(['male', 'female']), $length);
        }
        if ($this->nameMatches($name, ['image', 'photo', 'avatar', 'picture', 'thumbnail'])) {
            return $this->truncate($this->faker->imageUrl(640, 480), $length);
        }
        if ($this->nameMatches($name, ['token', 'api_key', 'secret'])) {
            $hex = bin2hex(random_bytes(32));
            return $length ? substr($hex, 0, $length) : $hex;
        }
        if ($this->nameMatches($name, ['status'])) {
            return $this->faker->randomElement(['active', 'inactive', 'pending']);
        }
        if ($this->nameMatches($name, ['type'])) {
            return $this->faker->randomElement(['basic', 'premium', 'enterprise']);
        }

        // ---------------------------------------------------------------
        // Type-based generation (range/length/precision aware)
        // ---------------------------------------------------------------
        return $this->generateByType($type, $column);
    }

    /**
     * Generate data purely based on DB column type, respecting constraints.
     *
     * Constraints respected:
     *   - Integer: exact signed/unsigned min-max per MySQL type
     *   - decimal/numeric: precision + scale from column definition
     *   - char: exactly N characters
     *   - varchar: at most N characters (realistic text, truncated)
     */
    private function generateByType(string $type, array $column)
    {
        $length   = $column['length']    ?? null;
        $unsigned = $column['unsigned']  ?? false;
        $scale    = $column['scale']     ?? null;
        $prec     = $column['precision'] ?? null;

        switch (true) {

            // ── Boolean ────────────────────────────────────────────────
            case $type === 'tinyint' && ($length ?? 0) === 1:
            case in_array($type, ['boolean', 'bool']):
                return $this->faker->boolean() ? 1 : 0;

            // ── tinyint — SIGNED -128→127, UNSIGNED 0→255 ─────────────
            case $type === 'tinyint':
                return $unsigned
                    ? $this->faker->numberBetween(0, 255)
                    : $this->faker->numberBetween(-128, 127);

            // ── smallint — SIGNED -32768→32767, UNSIGNED 0→65535 ──────
            case in_array($type, ['smallint', 'int2']):
                return $unsigned
                    ? $this->faker->numberBetween(0, 65535)
                    : $this->faker->numberBetween(-32768, 32767);

            // ── mediumint — SIGNED -8388608→8388607, UNSIGNED 0→16777215
            case $type === 'mediumint':
                return $unsigned
                    ? $this->faker->numberBetween(0, 16777215)
                    : $this->faker->numberBetween(-8388608, 8388607);

            // ── int/integer — SIGNED ±2147483647, UNSIGNED 0→2147483647
            case in_array($type, ['int', 'integer', 'int4', 'serial']):
                return $unsigned
                    ? $this->faker->numberBetween(0, 2147483647)
                    : $this->faker->numberBetween(-2147483648, 2147483647);

            // ── bigint — full PHP safe range ───────────────────────────
            case in_array($type, ['bigint', 'int8', 'bigserial']):
                return $unsigned
                    ? $this->faker->numberBetween(0, PHP_INT_MAX)
                    : $this->faker->numberBetween(PHP_INT_MIN, PHP_INT_MAX);

            // ── decimal/numeric — uses precision + scale ───────────────
            // e.g. decimal(10,2): max integer part = 10-2=8 digits → 99999999
            case in_array($type, ['decimal', 'numeric']):
                $s         = $scale ?? 2;
                $p         = $prec  ?? 10;
                $intDigits = max(1, $p - $s);
                $max       = (int) (10 ** $intDigits) - 1;
                return $this->faker->randomFloat($s, 0, $max);

            // ── float/double — high precision, large range ─────────────
            case in_array($type, ['float', 'double', 'real', 'money', 'double precision']):
                return $this->faker->randomFloat(4, 0, 9999999);

            // ── char(N) — generate EXACTLY N characters ────────────────
            case in_array($type, ['char', 'nchar']):
                $n = max(1, $length ?? 1);
                return substr($this->faker->lexify(str_repeat('?', $n)), 0, $n);

            // ── varchar(N) — realistic text truncated to N chars ───────
            case in_array($type, ['varchar', 'character varying', 'nvarchar']):
                $max  = $length ?? 255;
                $text = $this->faker->text(max(10, $max));
                return substr($text, 0, $max);

            // ── text — unlimited, realistic paragraph ──────────────────
            case in_array($type, ['text', 'mediumtext', 'longtext', 'tinytext', 'clob']):
                return $this->faker->paragraph();

            // ── date ───────────────────────────────────────────────────
            case $type === 'date':
                return $this->faker->date('Y-m-d');

            // ── datetime / timestamp ───────────────────────────────────
            case in_array($type, ['datetime', 'timestamp', 'timestamp without time zone', 'timestamp with time zone']):
                return $this->faker->dateTime()->format('Y-m-d H:i:s');

            // ── time ───────────────────────────────────────────────────
            case $type === 'time':
                return $this->faker->time('H:i:s');

            // ── year ───────────────────────────────────────────────────
            case $type === 'year':
                return $this->faker->year();

            // ── enum / set — random pick from extracted values ─────────
            case in_array($type, ['enum', 'set']):
                return !empty($column['values'])
                    ? $this->faker->randomElement($column['values'])
                    : $this->faker->word();

            // ── json / jsonb ───────────────────────────────────────────
            case in_array($type, ['json', 'jsonb']):
                return json_encode([
                    'key'   => $this->faker->word(),
                    'value' => $this->faker->sentence(),
                ]);

            // ── binary / blob ──────────────────────────────────────────
            case in_array($type, ['blob', 'mediumblob', 'longblob', 'tinyblob', 'binary', 'varbinary', 'bytea']):
                return bin2hex(random_bytes(8));

            // ── uuid ───────────────────────────────────────────────────
            case $type === 'uuid':
                return $this->faker->uuid();

            // ── fallback ───────────────────────────────────────────────
            default:
                $word = $this->faker->word();
                return $length ? substr($word, 0, $length) : $word;
        }
    }

    /**
     * Truncate a string to the column's max length (if defined).
     */
    private function truncate(?string $value, ?int $maxLength): ?string
    {
        if ($value === null || $maxLength === null || $maxLength <= 0) {
            return $value;
        }
        return substr($value, 0, $maxLength);
    }

    /**
     * Check if the column name equals or contains one of the keywords.
     */
    private function nameMatches(string $name, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if ($name === $keyword || str_contains($name, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
