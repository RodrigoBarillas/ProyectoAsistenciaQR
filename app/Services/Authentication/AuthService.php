<?php

namespace App\Services\Authentication;

use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RefreshRequest;
use App\Http\Resources\Authentication\AuthResource;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\Authentication\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    private const REFRESH_TOKEN_NAME = 'refresh_token';

    /**
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): AuthResource
    {
        $credentials = $request->only('email', 'password');

        $accessToken = Auth::attempt($credentials);

        if (!$accessToken) {
            throw new AuthenticationException('Invalid credentials.');
        }

        /** @var User $user */
        $user = Auth::user();

        $refreshToken = $this->issueRefreshToken($user);

        $user->load('role');

        return new AuthResource(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            tokenType: 'bearer',
            expiresIn: Auth::factory()->getTTL() * 60,
            role: $user?->role?->nombre
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function refresh(RefreshRequest $request): AuthResource
    {
        $record = RefreshToken::query()
            ->refreshTokens()
            ->where('token', $request->string('refresh_token'))
            ->first();

        if (!$record || $record->isExpired()) {
            throw new AuthenticationException('Invalid or expired refresh token.');
        }

        /** @var User $user */
        $user = $record->tokenable;

        $user->load('role');

        $record->update(['last_used_at' => now()]);

        $accessToken = JWTAuth::fromUser($user);

        $newRefreshToken = $this->issueRefreshToken($user);

        return new AuthResource(
            accessToken: $accessToken,
            refreshToken: $newRefreshToken,
            tokenType: 'bearer',
            expiresIn: Auth::factory()->getTTL() * 60,
            role: $user?->role?->nombre
        );
    }

    public function logout(): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Revoke all refresh tokens on logout.
        $user->refreshTokens()->delete();

        Auth::logout();
    }

    /**
     * Revoke all existing refresh tokens for the user and issue one new UUID token.
     */
    private function issueRefreshToken(User $user): string
    {
        // Invalidate every previous refresh token — one token per user.
        $user->refreshTokens()->delete();

        $uuid = Str::uuid()->toString();

        RefreshToken::create([
            'tokenable_type' => User::class,
            'tokenable_id'   => $user->getKey(),
            'name'           => self::REFRESH_TOKEN_NAME,
            'token'          => $uuid,
            'abilities'      => ['refresh'],
            'expires_at'     => now()->addDays(env('REFRESH_TOKEN_EXPIRES_IN_DAYS', 30)),
        ]);

        return $uuid;
    }
}
