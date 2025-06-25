<?php

namespace AlingAi\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTManager
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $expiresIn = 3600; // 1小时

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function generateToken(array $payload): string
    {
        $payload['exp'] = time() + $this->expiresIn;
        return JWT::encode($payload, $this->secretKey, $this->algorithm];
    }

    public function decodeToken(string $token): object
    {
        return JWT::decode($token, new Key($this->secretKey, $this->algorithm)];
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }
}
