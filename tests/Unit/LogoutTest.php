<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthService;
use App\Services\Interfaces\JwtProviderInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\RefreshTokenRepositoryInterface;

class LogoutTest extends TestCase
{
    public function test_logout_calls_revoke_by_hash_with_correct_hash()
    {
        // Arrange 🧩
        $mockJwt = $this->createMock(JwtProviderInterface::class);
        $mockRefreshRepo = $this->createMock(RefreshTokenRepositoryInterface::class);
        $mockUserRepo = $this->createMock(UserRepositoryInterface::class);
        $mockTx = $this->createMock(TransactionServiceInterface::class);

        $service = new AuthService($mockJwt, $mockRefreshRepo, $mockUserRepo, $mockTx);

        $plainToken = 'sample_refresh_token';
        $expectedHash = hash('sha256', $plainToken);

        // Assert 🧠
        $mockRefreshRepo
            ->expects($this->once()) // يجب أن يتم استدعاؤه مرة واحدة
            ->method('revokeByHash')
            ->with($this->equalTo($expectedHash)); // بنفس الهاش الصحيح

        // Act ⚙️
        $service->logout($plainToken);
    }
}
