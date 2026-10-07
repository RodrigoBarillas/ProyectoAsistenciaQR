<?php

namespace App\Http\Resources\Authentication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    private string $accessToken;
    private string $refreshToken;
    private string $tokenType;
    private int    $expiresIn;
    private $role;

    public function __construct(
        string $accessToken,
        string $refreshToken,
        string $tokenType = 'bearer',
        int    $expiresIn = 3600,
        mixed $role = null
    ) {
        $this->accessToken  = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->tokenType    = $tokenType;
        $this->expiresIn    = $expiresIn;
        $this->role         = $role;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => [
                'access_token'  => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'token_type'    => $this->tokenType,
                'role'          => $this->role,
                'expires_in'    => $this->expiresIn,
            ],
        ];
    }
}
