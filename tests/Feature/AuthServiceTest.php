<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // ⚙️ إعداد إعدادات JWT للاختبار فقط
        Config::set('jwt.secret', base64_encode(random_bytes(64)));
        Config::set('jwt.access_ttl', 1);   // دقيقة واحدة
        Config::set('jwt.refresh_ttl', 30); // 30 يوم
        Config::set('jwt.issuer', 'https://api.example.com');
        Config::set('jwt.audience', 'https://api.example.com');
    }

    /** ✅ اختبار دورة حياة كاملة: login → refresh → logout */
    public function test_user_can_login_refresh_and_logout_successfully()
    {
        // 1️⃣ إنشاء مستخدم حقيقي
        $user = User::create([
            'name' => 'E2E User',
            'email' => 'e2e@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2️⃣ تسجيل الدخول فعليًا
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'e2e@example.com',
            'password' => 'password',
        ])->assertStatus(200);

        $loginData = $loginResponse->json('data');
        $accessToken = $loginData['access_token'];
        $refreshToken = $loginData['refresh_token'];

        $this->assertNotEmpty($accessToken);
        $this->assertNotEmpty($refreshToken);

        // 🧩 تأكد من تخزين refresh_token في قاعدة البيانات
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => false,
        ]);

        // 3️⃣ تجربة تجديد التوكن
        $refreshResponse = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ])->assertStatus(200);

        $refreshData = $refreshResponse->json('data');
        $newAccessToken = $refreshData['access_token'];
        $newRefreshToken = $refreshData['refresh_token'];

        $this->assertNotEmpty($newAccessToken);
        $this->assertNotEmpty($newRefreshToken);

        // ✅ تأكد أن التوكن القديم تم إبطاله
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => true,
        ]);

        // ✅ تأكد من وجود التوكن الجديد في قاعدة البيانات
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => false,
        ]);

        // 4️⃣ تسجيل الخروج باستخدام access_token الجديد
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$newAccessToken}")
            ->postJson('/api/v1/auth/logout', [
                'refresh_token' => $newRefreshToken,
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        // ✅ تأكد أن refresh_token الجديد تم إبطاله
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => true,
        ]);
    }

    /** ❌ اختبار فشل عند استخدام refresh_token منتهي الصلاحية */
    public function test_refresh_fails_when_token_is_expired()
    {
        // 1️⃣ إنشاء مستخدم
        $user = User::create([
            'name' => 'Expired User',
            'email' => 'expired@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2️⃣ إنشاء refresh_token منتهي يدويًا
        $plainToken = Str::random(80);
        $hash = hash('sha256', $plainToken);

        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => $hash,
            'device_name' => 'Testing Device',
            'ip' => '127.0.0.1',
            'expires_at' => now()->subMinutes(5), // منتهي
            'revoked' => false,
        ]);

        // 3️⃣ إرسال طلب تجديد بالتوكن المنتهي
        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $plainToken,
        ])->assertStatus(401);

        $response->assertJson([
            'success' => false,
            'message' => 'Invalid or expired refresh token',
        ]);
    }
}
