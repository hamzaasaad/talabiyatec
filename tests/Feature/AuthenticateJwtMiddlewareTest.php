<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\LcobucciJwtProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\UnencryptedToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticateJwtMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected LcobucciJwtProvider $jwt;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('jwt.secret', base64_encode(random_bytes(64)));
        Config::set('jwt.access_ttl', 1);
        Config::set('jwt.issuer', 'https://api.example.com');
        Config::set('jwt.audience', 'https://api.example.com');
        Config::set('jwt.algo', 'HS256');

        $this->jwt = new LcobucciJwtProvider();
        
    app('router')->aliasMiddleware('auth.jwt', \App\Http\Middleware\AuthenticateJwt::class);

        // نضيف Route وهمي عليه الميدل وير لاختباره
        Route::middleware('auth.jwt')->get('/test-protected', function () {
            return response()->json(['message' => 'Access granted']);
        });
    }

    /** ✅ التحقق أن الطلب ينجح مع توكن صحيح */
    public function test_allows_request_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $this->jwt->createAccessToken($user);

        $response = $this->getJson('/test-protected', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Access granted']);
    }

    /** ❌ يرفض الطلب بدون توكن */
    public function test_rejects_request_without_token()
    {
        $response = $this->getJson('/test-protected');
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized - Missing Bearer Token']);
    }

    /** ⚠️ يرفض الطلب مع توكن مزور */
    public function test_rejects_request_with_invalid_token()
    {
        $fakeToken = 'Bearer invalid.token.signature';

        $response = $this->getJson('/test-protected', [
            'Authorization' => $fakeToken
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid or expired token']);
    }

    /** ⏰ يرفض الطلب مع توكن منتهي الصلاحية */
    public function test_rejects_request_with_expired_token()
    {
        $user = User::factory()->create();
        Config::set('jwt.access_ttl', 0); // توكن ينتهي فوراً
        $jwt = new LcobucciJwtProvider();
        $token = $jwt->createAccessToken($user);

        sleep(2); // ننتظر لينتهي

        $response = $this->getJson('/test-protected', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid or expired token']);
    }
}
