<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AuthSessionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // ⚙️ إعداد JWT للاختبار فقط
        Config::set('jwt.secret', base64_encode(random_bytes(64)));
        Config::set('jwt.access_ttl', 1);
        Config::set('jwt.refresh_ttl', 30);
        Config::set('jwt.issuer', 'https://api.example.com');
        Config::set('jwt.audience', 'https://api.example.com');
    }

    /** ✅ اختبار عرض جميع الجلسات ثم مسحها */
    public function test_user_can_view_and_clear_all_sessions()
    {
        // 1️⃣ إنشاء مستخدم حقيقي
        $user = User::create([
            'name' => 'MultiSessionUser',
            'email' => 'multi@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2️⃣ إنشاء جلستين يدويًا تحاكي تسجيل الدخول من جهازين
        $tokens = collect(['Laptop', 'Mobile'])->map(function ($device) use ($user) {
            return RefreshToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', Str::random(80)),
                'device_name' => $device,
                'ip' => $device === 'Laptop' ? '192.168.1.10' : '192.168.1.22',
                'expires_at' => now()->addDays(30),
                'revoked' => false,
            ]);
        });

        // 3️⃣ تسجيل دخول المستخدم فعليًا للحصول على access_token صالح
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'multi@example.com',
            'password' => 'password',
        ])->assertStatus(200);

        $accessToken = $loginResponse->json('data.access_token');
        $this->assertNotEmpty($accessToken);

        // 4️⃣ استدعاء endpoint عرض الجلسات
        $sessionsResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->getJson('/api/v1/auth/sessions')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Active sessions retrieved successfully',
            ]);

        $sessions = $sessionsResponse->json('data');

        // 🧠 تأكد أن هناك جلستين فعالتين
    $this->assertGreaterThanOrEqual(3, count($sessions));
$this->assertTrue(
    collect($sessions)->pluck('device_name')->contains('Laptop')
);
$this->assertTrue(
    collect($sessions)->pluck('device_name')->contains('Mobile')
);


        // 5️⃣ تنفيذ logoutAll لمسح كل الجلسات
        $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/v1/auth/logout-all')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out from all devices successfully',
            ]);

        // 6️⃣ تأكد أن جميع الجلسات تم إلغاؤها في قاعدة البيانات
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->id,
            'revoked' => true,
        ]);

        // 7️⃣ استدعاء /sessions مرة أخرى
        $afterLogoutResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->getJson('/api/v1/auth/sessions')
            ->assertStatus(200);

        $this->assertCount(0, $afterLogoutResponse->json('data'));
    }
}
