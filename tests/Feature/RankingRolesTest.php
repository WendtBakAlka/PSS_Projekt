<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestDatabaseSeeder;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;

class RankingRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    #[Test]
    public function admin_widzi_link_do_panelu_admina()
    {
        // Stwórz admina
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/rankings/top-rated');

        $response->assertStatus(200);
        // Admin powinien widzieć link do panelu admina
        $response->assertSee('Panel admina');
    }

    #[Test]
    public function zwykly_user_nie_widzi_linku_do_panelu_admina()
    {
        // Stwórz zwykłego użytkownika
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/rankings/top-rated');

        $response->assertStatus(200);
        // Zwykły user NIE powinien widzieć linku do admina
        $response->assertDontSee('Panel admina');
    }

    #[Test]
    public function admin_ma_dostep_do_panelu_admina()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin');

        $response->assertStatus(200);
    }

    #[Test]
    public function zwykly_user_nie_ma_dostepu_do_panelu_admina()
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/admin');

        // Powinien dostać 403 (Forbidden)
        $response->assertStatus(302);
        $response->assertRedirect('/games');
    }
}
