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

    public function __construct(
        string $accessToken,
        string $refreshToken,
        string $tokenType = 'bearer',
        int    $expiresIn = 3600,
    ) {
        $this->accessToken  = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->tokenType    = $tokenType;
        $this->expiresIn    = $expiresIn;
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
                'expires_in'    => $this->expiresIn,
            ],
        ];
    }
}
