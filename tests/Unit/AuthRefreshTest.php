<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use App\Repositories\Interfaces\RefreshTokenRepositoryInterface;
use App\Services\AuthService;
use App\Services\LcobucciJwtProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Mockery;

class AuthRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $auth;
    protected $jwt;
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

     Config::set('jwt.secret', base64_encode(random_bytes(64)));
    Config::set('jwt.access_ttl', 15);
    Config::set('jwt.refresh_ttl', 30);
    Config::set('jwt.issuer', 'https://api.example.com');
    Config::set('jwt.audience', 'https://api.example.com');

    // 🧱 إنشاء mock لكل الاعتمادات
    $this->jwt = new \App\Services\LcobucciJwtProvider();
    $this->refreshRepo = \Mockery::mock(\App\Repositories\Interfaces\RefreshTokenRepositoryInterface::class);
    $this->userRepo = \Mockery::mock(\App\Repositories\Interfaces\UserRepositoryInterface::class);
    $this->tx = \Mockery::mock(\App\Services\Interfaces\TransactionServiceInterface::class);

    // 🧩 تسجيلهم في الـ container
    $this->app->instance(\App\Repositories\Interfaces\RefreshTokenRepositoryInterface::class, $this->refreshRepo);
    $this->app->instance(\App\Repositories\Interfaces\UserRepositoryInterface::class, $this->userRepo);
    $this->app->instance(\App\Services\Interfaces\TransactionServiceInterface::class, $this->tx);

    // 🧠 إنشاء كائن الخدمة مع جميع التبعيات
    $this->auth = new \App\Services\AuthService(
        $this->jwt,
        $this->refreshRepo,
        $this->userRepo,
        $this->tx
    );
    }

    /** ✅ يجب أن ينجح التجديد بتوكن صالح */
    public function test_refresh_with_valid_token()
{
    $user = User::factory()->create();

    $plainToken = \Str::random(80);
    $hash = hash('sha256', $plainToken);

    // إنشاء كائن RefreshToken وهمي من نوع الموديل الفعلي
$fakeToken = new \App\Models\RefreshToken();
$fakeToken->revoked = false;
$fakeToken->setRelation('user', $user);

$fakeToken = \Mockery::mock($fakeToken)->makePartial();
$fakeToken->shouldReceive('isExpired')->andReturn(false);
$fakeToken->shouldReceive('revoke')->andReturnNull();


    $this->refreshRepo->shouldReceive('findByHash')->once()->andReturn($fakeToken);
    $this->refreshRepo->shouldReceive('create')->once();

    $this->tx->shouldReceive('run')->andReturnUsing(fn($cb) => $cb());

    $result = $this->auth->refresh($plainToken, 'UnitTest', '127.0.0.1');

    $this->assertArrayHasKey('access_token', $result);
    $this->assertArrayHasKey('refresh_token', $result);
    $this->assertEquals($user->id, $result['user']->id);
}



    /** ❌ يجب أن يفشل التجديد عند توكن منتهي */
    public function test_refresh_fails_with_expired_token()
    {
        $user = User::factory()->create();
        $plainToken = Str::random(80);
        $hashed = hash('sha256', $plainToken);

        $expiredToken = new \App\Models\RefreshToken([
            'user_id' => $user->id,
            'token_hash' => $hashed,
            'revoked' => false,
            'expires_at' => now()->subDay(),
        ]);

        $this->refreshRepo->shouldReceive('findByHash')->once()->andReturn($expiredToken);

        $this->expectException(\App\Exceptions\InvalidRefreshTokenException::class);

        $this->auth->refresh($plainToken, 'UnitTest', '127.0.0.1');
    }

    /** ⚠️ يجب أن يفشل التجديد عند توكن مزوّر */
    public function test_refresh_fails_with_fake_token()
    {
        $this->refreshRepo->shouldReceive('findByHash')->once()->andReturn(null);

        $this->expectException(\App\Exceptions\InvalidRefreshTokenException::class);

        $this->auth->refresh('fake_refresh_token', 'FakeDevice', '127.0.0.1');
    }
}
