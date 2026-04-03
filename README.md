# Auto-Seeder

A zero-config PHP package that connects to your database via an Eloquent model (or a raw PDO connection), reads all column definitions, and inserts randomly-generated, realistic data — no seeders to write by hand.

## Requirements

- PHP >= 7.4
- fakerphp/faker ^1.23

## Installation

```bash
composer require oluokunkabiru/auto-seeder
```

## Usage

### Laravel / Eloquent

```php
use Oluokunkabiru\AutoSeeder\AutoSeeder;
use App\Models\User;

// Seed 1 row (default)
AutoSeeder::fromModel(User::class)->seed();

// Seed 50 rows
AutoSeeder::fromModel(User::class)->seed(50);

// Shorthand — pass count as second argument to fromModel()
AutoSeeder::fromModel(User::class, 50);

// Skip extra columns
AutoSeeder::fromModel(User::class)
    ->skip(['api_token', 'two_factor_secret'])
    ->seed(100);
```

Inside `DatabaseSeeder.php`:

```php
public function run(): void
{
    AutoSeeder::fromModel(\App\Models\User::class)->seed(20);
    AutoSeeder::fromModel(\App\Models\Product::class)->seed(100);
}
```

### Standalone PHP (raw PDO)

```php
use Oluokunkabiru\AutoSeeder\AutoSeeder;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'root', '');

// Seed 10 rows into the "orders" table
AutoSeeder::fromPdo($pdo)->seed(10, 'orders');

// Seed 1 row (default)
AutoSeeder::fromPdo($pdo)->seed(table: 'orders');
```

## How It Works

1. **Inspect** — Reads every column from the table using `DESCRIBE` (MySQL), `PRAGMA table_info` (SQLite), or `information_schema` (PostgreSQL).
2. **Generate** — Maps each column to a Faker method using:
   - **Name heuristics** — e.g. a column named `email` → `$faker->safeEmail()`, `phone` → `$faker->phoneNumber()`.
   - **Type mapping** — e.g. `decimal` → `$faker->randomFloat(2)`, `datetime` → `$faker->dateTime()`, `enum('a','b')` → random pick.
3. **Insert** — Bulk-inserts all rows via a prepared PDO statement.

## Skipped Columns (automatic)

The following columns are **never seeded** (auto-detected):

| Column | Reason |
|---|---|
| `id` | Auto-increment PK |
| `created_at` / `updated_at` | Managed by ORM |
| `deleted_at` | Soft delete |
| `remember_token`, `email_verified_at` | Framework internals |

## Supported Column Types

| DB Type | Generated Value |
|---|---|
| `varchar`, `char` | Random word/sentence |
| `text`, `longtext` | Paragraph |
| `int`, `bigint`, etc. | Random number |
| `tinyint(1)` / `boolean` | `true` / `false` |
| `decimal`, `float`, `double` | Random float |
| `date` | Random date |
| `datetime`, `timestamp` | Random datetime |
| `enum` | Random pick from enum values |
| `json` | `{"key": "...", "value": "..."}` |
| `uuid` | UUID v4 |

## Supported Databases

- ✅ MySQL / MariaDB
- ✅ SQLite
- ✅ PostgreSQL

## License

MIT © OLUOKUN KABIRU ADESINA
