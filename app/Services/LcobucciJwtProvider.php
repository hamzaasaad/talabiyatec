<?php

namespace App\Services;

use App\Services\Interfaces\JwtProviderInterface;
use App\Models\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token;
use Lcobucci\Clock\SystemClock;
use Illuminate\Support\Str;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Exception;

class LcobucciJwtProvider implements JwtProviderInterface
{
    protected Configuration $config;
    protected int $ttl;
    protected string $issuer;
    protected string $audience;

    public function __construct()
    {
        $secret = base64_decode(str_replace('base64:', '', config('jwt.secret')));

        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret)
        );

        $this->ttl = (int) config('jwt.access_ttl', 15);
        $this->issuer = config('jwt.issuer', config('app.url'));
        $this->audience = config('jwt.audience', config('app.url'));

        $this->config->setValidationConstraints(
            new Constraint\IssuedBy($this->issuer),
            new Constraint\PermittedFor($this->audience),
            new Constraint\SignedWith(
                $this->config->signer(),
                $this->config->verificationKey()
            ),
            new ValidAt(new SystemClock(new DateTimeZone(config('app.timezone', 'UTC'))))
        );
    }

  
    public function createAccessToken(User $user, array $claims = []): string
    {
        $now = new DateTimeImmutable();

        $builder = $this->config->builder()
            ->issuedBy($this->issuer)
            ->permittedFor($this->audience)
            ->identifiedBy(Str::uuid()->toString())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$this->ttl} minutes"))
            ->relatedTo((string) $user->id)
            ->withClaim('email', $user->email)
            ->withClaim('roles', $user->getRoleNames())
            ->withHeader('alg', config('jwt.algo', 'HS256'))
            ->withHeader('typ', 'JWT');

        foreach ($claims as $key => $value) {
            $builder->withClaim($key, $value);
        }

        return $builder
            ->getToken(
                $this->config->signer(),
                $this->config->signingKey()
            )
            ->toString();
    }

   
    public function parse(string $token): Plain
    {
        $parsed = $this->config->parser()->parse($token);

        
        if ($parsed instanceof Token\Plain) {
            return $parsed;
        }

        if ($parsed instanceof \Lcobucci\JWT\UnencryptedToken) {
            return $parsed;
        }

        throw new RuntimeException('Invalid JWT token format.');
    }

    public function validate(Plain $token): bool
    {
        $constraints = $this->config->validationConstraints();
        return $this->config->validator()->validate($token, ...$constraints);
    }

   
    public function decode(string $token): array
    {
        $parsed = $this->parse($token);

        if (!$this->validate($parsed)) {
            throw new Exception('Invalid or expired token.');
        }

        return $parsed->claims()->all();
    }
}
