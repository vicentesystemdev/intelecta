<?php

namespace Tests\Feature\Institucional;

use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DemoEnvironmentGuardTest extends TestCase
{
    use RefreshDatabase;

    public static function protectedEnvironments(): array
    {
        return [['production'], ['staging']];
    }

    #[DataProvider('protectedEnvironments')]
    public function test_demo_accounts_are_rejected_before_any_writes(string $environment): void
    {
        $original = app()->environment();
        $permissionsBefore = DB::table('permissions')->orderBy('id')->get();
        app()->detectEnvironment(fn () => $environment);

        try {
            app(RolesAndUsersSeeder::class)->run();
            $this->fail('La carga demo debe detenerse fuera de local/testing.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('demostración', $exception->getMessage());
            $this->assertDatabaseCount('users', 0);
            $this->assertDatabaseCount('roles', 0);
            $this->assertEquals($permissionsBefore, DB::table('permissions')->orderBy('id')->get());
        } finally {
            app()->detectEnvironment(fn () => $original);
        }
    }
}
