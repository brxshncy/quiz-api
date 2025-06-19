<?php 

namespace App\Services;

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
   
    public function loginAsAdmin(array $credentials): array
    {
        $user = $this->validateCredentials($credentials);

        if (!$user->hasRole(RoleEnum::ADMIN)) {
            throw ValidationException::withMessages([
                'email' => ['User is not an admin'],
            ]);
        }

        return $this->issueToken($user, 'admin-token');
    }

    public function loginAsUser(array $credentials): array
    {
        $user = $this->validateCredentials($credentials);

        if (! $user->hasRole(RoleEnum::USER)) {
            throw ValidationException::withMessages([
                'email' => ['User does not have user access'],
            ]);
        }

        return $this->issueToken($user, 'user-token');
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    protected function validateCredentials(array $credentials): User
    {
        $user = User::where("email", $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email not found']
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'Invalid credentials']);
        }

        return $user;
    }

    protected function issueToken (User $user, string $token): array
    {
        return [
            'user' => $user,
            'token'=> $token
        ];          
    }

}