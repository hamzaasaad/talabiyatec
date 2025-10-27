<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\LcobucciJwtProvider;
use Lcobucci\JWT\UnencryptedToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Token\Plain;

class JwtProviderTest extends TestCase
{
    use RefreshDatabase;

    protected LcobucciJwtProvider $jwt;

    protected function setUp(): void
    {
        parent::setUp();

        // إعدادات افتراضية
        Config::set('jwt.secret', base64_encode(random_bytes(64)));
        Config::set('jwt.access_ttl', 1); // دقيقة واحدة فقط للاختبار
        Config::set('jwt.issuer', 'https://api.example.com');
        Config::set('jwt.audience', 'https://api.example.com');
        Config::set('jwt.algo', 'HS256');

        $this->jwt = new LcobucciJwtProvider();
    }

    /** ✅ اختبار: توليد توكن JWT صحيح */
    public function test_it_can_generate_a_valid_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = $this->jwt->createAccessToken($user);

        $this->assertIsString($token, 'Token should be a string');
        $parsed = $this->jwt->parse($token);
$this->assertInstanceOf(Plain::class, $parsed);

        $claims = $parsed->claims()->all();
        $this->assertEquals($user->email, $claims['email']);
        $this->assertEquals($user->id, (int) $claims['sub']);
    }

    /** ⚠️ اختبار: التحقق من التوقيع الصحيح */
    public function test_token_fails_with_wrong_signature()
    {
        $user = User::factory()->create();
        $token = $this->jwt->createAccessToken($user);

        // تعديل المفتاح السري لمحاكاة تزوير التوقيع
        Config::set('jwt.secret', base64_encode(random_bytes(64)));
        $invalidJwt = new LcobucciJwtProvider();

        $parsed = $invalidJwt->parse($token);
        $isValid = $invalidJwt->validate($parsed);

        $this->assertFalse($isValid, 'Token should fail validation with wrong secret');
    }

    /** ⏰ اختبار: انتهاء صلاحية التوكن */
    public function test_token_expires_correctly()
    {
        $user = User::factory()->create();
        Config::set('jwt.access_ttl', 0); // ينتهي فورًا
        $jwt = new LcobucciJwtProvider();

        $token = $jwt->createAccessToken($user);
        sleep(2); // ننتظر حتى تنتهي الصلاحية

        $parsed = $jwt->parse($token);
        $isValid = $jwt->validate($parsed);

        $this->assertFalse($isValid, 'Expired token should fail validation');
    }
}
