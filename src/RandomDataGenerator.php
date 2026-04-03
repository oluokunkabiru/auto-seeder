<?php

namespace Oluokunkabiru\AutoSeeder;

use Faker\Factory;
use Faker\Generator;

class RandomDataGenerator
{
    private Generator $faker;

    public function __construct(string $locale = 'en_US')
    {
        $this->faker = Factory::create($locale);
    }

    /**
     * Generate a random value for a given column definition.
     *
     * @param  array $column  { name, type, nullable, length, values, unsigned, key, extra }
     * @return mixed
     */
    public function generate(array $column)
    {
        // Always return null for nullable columns ~20% of the time
        if ($column['nullable'] && $this->faker->boolean(20)) {
            return null;
        }

        $name = strtolower($column['name']);
        $type = strtolower($column['type']);

        // ---------------------------------------------------------------
        // Name-based heuristics (applied first for better realism)
        // ---------------------------------------------------------------
        if ($this->nameMatches($name, ['email'])) {
            return $this->faker->unique()->safeEmail();
        }
        if ($this->nameMatches($name, ['first_name', 'firstname'])) {
            return $this->faker->firstName();
        }
        if ($this->nameMatches($name, ['last_name', 'lastname', 'surname'])) {
            return $this->faker->lastName();
        }
        if ($this->nameMatches($name, ['name', 'full_name', 'fullname'])) {
            return $this->faker->name();
        }
        if ($this->nameMatches($name, ['phone', 'telephone', 'mobile', 'phone_number'])) {
            return $this->faker->phoneNumber();
        }
        if ($this->nameMatches($name, ['address', 'street'])) {
            return $this->faker->streetAddress();
        }
        if ($this->nameMatches($name, ['city'])) {
            return $this->faker->city();
        }
        if ($this->nameMatches($name, ['state', 'province'])) {
            return $this->faker->state();
        }
        if ($this->nameMatches($name, ['country'])) {
            return $this->faker->country();
        }
        if ($this->nameMatches($name, ['zipcode', 'zip_code', 'postal_code', 'postcode'])) {
            return $this->faker->postcode();
        }
        if ($this->nameMatches($name, ['url', 'website', 'link'])) {
            return $this->faker->url();
        }
        if ($this->nameMatches($name, ['username', 'user_name'])) {
            return $this->faker->userName();
        }
        if ($this->nameMatches($name, ['password'])) {
            return password_hash($this->faker->password(8, 16), PASSWORD_BCRYPT);
        }
        if ($this->nameMatches($name, ['title'])) {
            return $this->faker->sentence(3);
        }
        if ($this->nameMatches($name, ['description', 'body', 'content', 'bio', 'summary', 'note', 'notes', 'remark', 'remarks', 'comment', 'comments'])) {
            return $this->faker->paragraph();
        }
        if ($this->nameMatches($name, ['slug'])) {
            return $this->faker->slug();
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
            return $this->faker->hexColor();
        }
        if ($this->nameMatches($name, ['amount', 'price', 'cost', 'salary', 'fee', 'balance', 'total'])) {
            return $this->faker->randomFloat(2, 0, 100000);
        }
        if ($this->nameMatches($name, ['age'])) {
            return $this->faker->numberBetween(1, 100);
        }
        if ($this->nameMatches($name, ['gender', 'sex'])) {
            return $this->faker->randomElement(['male', 'female']);
        }
        if ($this->nameMatches($name, ['image', 'photo', 'avatar', 'picture', 'thumbnail'])) {
            return $this->faker->imageUrl(640, 480);
        }
        if ($this->nameMatches($name, ['token', 'api_key', 'secret'])) {
            return bin2hex(random_bytes(32));
        }
        if ($this->nameMatches($name, ['status'])) {
            return $this->faker->randomElement(['active', 'inactive', 'pending']);
        }
        if ($this->nameMatches($name, ['type'])) {
            return $this->faker->randomElement(['basic', 'premium', 'enterprise']);
        }

        // ---------------------------------------------------------------
        // Type-based generation
        // ---------------------------------------------------------------
        return $this->generateByType($type, $column);
    }

    /**
     * Generate data purely based on DB column type.
     */
    private function generateByType(string $type, array $column)
    {
        switch (true) {

            // Boolean
            case $type === 'tinyint' && ($column['length'] ?? 0) === 1:
            case in_array($type, ['boolean', 'bool']):
                return $this->faker->boolean();

            // Integer types
            case in_array($type, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'serial', 'int2', 'int4', 'int8']):
                $max = $column['unsigned'] ? 99999 : 9999;
                return $this->faker->numberBetween(0, $max);

            // Decimal / Float / Double
            case in_array($type, ['decimal', 'numeric', 'float', 'double', 'real', 'money', 'double precision']):
                return $this->faker->randomFloat(2, 0, 9999);

            // Strings
            case in_array($type, ['varchar', 'char', 'character varying', 'nvarchar', 'nchar']):
                $maxLen = min($column['length'] ?? 255, 255);
                return $this->faker->lexify(str_repeat('?', min($maxLen, 20)));

            // Long text
            case in_array($type, ['text', 'mediumtext', 'longtext', 'tinytext', 'clob']):
                return $this->faker->paragraph();

            // Date / Time
            case $type === 'date':
                return $this->faker->date('Y-m-d');

            case in_array($type, ['datetime', 'timestamp', 'timestamp without time zone', 'timestamp with time zone']):
                return $this->faker->dateTime()->format('Y-m-d H:i:s');

            case $type === 'time':
                return $this->faker->time('H:i:s');

            case $type === 'year':
                return $this->faker->year();

            // Enum / Set
            case in_array($type, ['enum', 'set']):
                if (!empty($column['values'])) {
                    return $this->faker->randomElement($column['values']);
                }
                return $this->faker->word();

            // JSON
            case in_array($type, ['json', 'jsonb']):
                return json_encode([
                    'key'   => $this->faker->word(),
                    'value' => $this->faker->sentence(),
                ]);

            // Binary / Blob — generate a small random hex string
            case in_array($type, ['blob', 'mediumblob', 'longblob', 'tinyblob', 'binary', 'varbinary', 'bytea']):
                return bin2hex(random_bytes(8));

            // UUID (PostgreSQL uuid type)
            case $type === 'uuid':
                return $this->faker->uuid();

            // Fallback: return a word
            default:
                return $this->faker->word();
        }
    }

    /**
     * Check if the column name is in the list or contains one of the keywords.
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
