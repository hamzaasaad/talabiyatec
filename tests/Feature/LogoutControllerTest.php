<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;
protected function setUp(): void
{
    parent::setUp();

    // ⛔ تجاوز جميع الـ middleware أثناء هذا الاختبار
    $this->withoutMiddleware();
}

    public function test_logout_returns_success_response()
    {
        // Arrange 🧩
        $mockAuth = $this->createMock(AuthServiceInterface::class);
        $this->app->instance(AuthServiceInterface::class, $mockAuth);

        $mockAuth
            ->expects($this->once())
            ->method('logout')
            ->with('dummy_token');

        // Act ⚙️
        $response = $this->postJson('/api/v1/auth/logout', [
            'refresh_token' => 'dummy_token',
        ]);

        // Assert 🧠
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Logged out successfully',
                 ]);
    }
}
