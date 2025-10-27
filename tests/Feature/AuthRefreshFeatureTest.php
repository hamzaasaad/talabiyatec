<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
class AuthRefreshFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // إعدادات JWT (تكون بسيطة أثناء الاختبار)
        Config::set('jwt.secret', base64_encode(random_bytes(64)));
        Config::set('jwt.access_ttl', 1);  // دقيقة واحدة فقط
        Config::set('jwt.refresh_ttl', 30);
        Config::set('jwt.issuer', 'https://api.example.com');
        Config::set('jwt.audience', 'https://api.example.com');
    }

    /** ✅ اختبار دورة الحياة الكاملة لتجديد التوكن */
    public function test_user_can_refresh_token_successfully()
    {
        // 1️⃣ إنشاء مستخدم فعلي
        $user = User::factory()->create([
            'email' => 'refresh@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2️⃣ تسجيل الدخول للحصول على refresh_token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'refresh@example.com',
            'password' => 'password',
        ])->assertStatus(200);

        $tokens = $loginResponse->json('data');
        $refreshToken = $tokens['refresh_token'];

        // تأكد أن refresh_token تم إنشاؤه
        $this->assertNotEmpty($refreshToken);

        // 3️⃣ إرسال طلب تجديد
        $refreshResponse = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ])->assertStatus(200);

        // 4️⃣ التأكد من الرد
        $data = $refreshResponse->json('data');

        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertEquals($user->id, $data['user']['id']);

        // 5️⃣ تأكد أن refresh_token القديم تم إلغاؤه (revoked)
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => true,
        ]);

        // 6️⃣ تأكد أن refresh_token الجديد موجود
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => false,
        ]);
    }

    public function test_refresh_fails_with_revoked_token()
{
    $user = User::factory()->create();

    // أنشئ توكن ملغي يدوياً
   \App\Models\RefreshToken::create([
    'user_id' => $user->id,
    'token_hash' => hash('sha256', Str::random(80)),
    'device_name' => 'TestDevice',
    'ip' => '127.0.0.1',
    'expires_at' => now()->addDays(30),
    'revoked' => true,
]);

    $response = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => 'any_fake_value_here',
    ])->assertStatus(401);

    $response->assertJson([
        'success' => false,
        'message' => 'Invalid or expired refresh token',
    ]);
}

}
