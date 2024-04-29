<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;
use App\Enums\TokenAbility;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
final readonly class Login
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $user = User::where('email', $args['email'])->first();

        if (! $user || ! Hash::check($args['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
                'password' => ['The provided credentials are incorrect.'],
            ]);
        }
        return $user->createToken(
            'access_token', 
            [TokenAbility::ALL->value], 
            Carbon::now()->addMinutes(config('sanctum.ac_expiration'))
        )->plainTextToken;
    }
}
