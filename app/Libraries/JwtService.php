<?php

namespace App\Libraries;

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private $secretKey;
    private $algorithm;

    public function __construct()
    {
        // Set your secret key and algorithm
        $this->secretKey = str_replace('hex2bin:', '', env('encryption.key'));
        $this->algorithm = 'HS256';
    }

    public function generateAccessToken(array $data, $expiration = '+6000 Minute')
    {
        $issuedAt = time();
        $expirationTime = strtotime($expiration);

        $token = [
            'iat'  => $issuedAt,
            'exp'  => $expirationTime,
            'data' => $data,
        ];

        return JWT::encode($token, $this->secretKey, $this->algorithm);
    }

    public function generateRefreshToken(array $data, $expiration = '+1 month')
    {
        $issuedAt = time();
        $expirationTime = strtotime($expiration);

        $token = [
            'iat'  => $issuedAt,
            'exp'  => $expirationTime,
            'data' => $data,
        ];

        return JWT::encode($token, $this->secretKey, $this->algorithm);
    }

    public function decodeToken($token)
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
