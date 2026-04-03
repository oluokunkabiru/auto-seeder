<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Oluokunkabiru\AutoSeeder\AutoSeeder;
use Oluokunkabiru\AutoSeeder\RandomDataGenerator;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Unit tests for RandomDataGenerator — no real DB needed.
 */
class RandomDataGeneratorTest extends TestCase
{
    private RandomDataGenerator $gen;

    protected function setUp(): void
    {
        $this->gen = new RandomDataGenerator();
    }

    private function col(string $name, string $type, bool $nullable = false, ?int $length = null, array $values = [], bool $unsigned = false): array
    {
        return compact('name', 'type', 'nullable', 'length', 'values', 'unsigned') + ['key' => '', 'extra' => ''];
    }

    /** @test */
    public function it_generates_email_for_email_column(): void
    {
        $value = $this->gen->generate($this->col('email', 'varchar', false, 150));
        $this->assertStringContainsString('@', $value);
    }

    /** @test */
    public function it_generates_phone_number_for_phone_column(): void
    {
        $value = $this->gen->generate($this->col('phone', 'varchar', false, 20));
        $this->assertNotEmpty($value);
    }

    /** @test */
    public function it_generates_boolean_for_tinyint1(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $value = $this->gen->generate($this->col('is_active', 'tinyint', false, 1));
            $this->assertContains($value, [true, false]);
        }
    }

    /** @test */
    public function it_generates_random_number_for_int(): void
    {
        $value = $this->gen->generate($this->col('age', 'int', false, null));
        $this->assertIsInt($value);
    }

    /** @test */
    public function it_generates_float_for_decimal(): void
    {
        $value = $this->gen->generate($this->col('balance', 'decimal', false, null));
        $this->assertIsFloat($value);
    }

    /** @test */
    public function it_generates_paragraph_for_text(): void
    {
        $value = $this->gen->generate($this->col('description', 'text', false));
        $this->assertIsString($value);
        $this->assertNotEmpty($value);
    }

    /** @test */
    public function it_generates_date_string_for_date_column(): void
    {
        $value = $this->gen->generate($this->col('birth_date', 'date', false));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $value);
    }

    /** @test */
    public function it_generates_datetime_string_for_datetime(): void
    {
        $value = $this->gen->generate($this->col('created_on', 'datetime', false));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value);
    }

    /** @test */
    public function it_picks_from_enum_values(): void
    {
        $enumValues = ['draft', 'published', 'archived'];
        for ($i = 0; $i < 20; $i++) {
            // Use a neutral column name so no name-heuristic interferes
            $value = $this->gen->generate($this->col('publication', 'enum', false, null, $enumValues));
            $this->assertContains($value, $enumValues, "Value '{$value}' is not in the enum list.");
        }
    }

    /** @test */
    public function it_generates_valid_json_for_json_column(): void
    {
        $value = $this->gen->generate($this->col('meta', 'json', false));
        $decoded = json_decode($value, true);
        $this->assertIsArray($decoded);
    }

    /** @test */
    public function it_returns_null_sometimes_for_nullable_columns(): void
    {
        // Run 200 times — statistically at least one null should appear
        $hasNull = false;
        for ($i = 0; $i < 200; $i++) {
            $value = $this->gen->generate($this->col('notes', 'text', true));
            if ($value === null) {
                $hasNull = true;
                break;
            }
        }
        $this->assertTrue($hasNull, 'Expected at least one null value for a nullable column over 200 attempts.');
    }

    /** @test */
    public function it_generates_uuid_for_uuid_column(): void
    {
        $value = $this->gen->generate($this->col('uuid', 'uuid', false));
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }

    /** @test */
    public function it_generates_hashed_password_for_password_column(): void
    {
        $value = $this->gen->generate($this->col('password', 'varchar', false, 255));
        // bcrypt hashes start with $2y$
        $this->assertStringStartsWith('$2y$', $value);
    }

    /** @test */
    public function it_generates_url_for_url_column(): void
    {
        $value = $this->gen->generate($this->col('website_url', 'varchar', false, 255));
        $this->assertStringContainsString('://', $value);
    }

    /** @test */
    public function it_generates_slug_for_slug_column(): void
    {
        $value = $this->gen->generate($this->col('slug', 'varchar', false, 255));
        $this->assertNotEmpty($value);
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $value);
    }
}
