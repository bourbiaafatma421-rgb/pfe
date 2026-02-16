<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\Auth\UserNotAuthenticatedException;
use App\Exceptions\Auth\PasswordAlreadyChangedException;

class ChangePasswordService
{
    
    public function setPassword(?User $user, string $newPassword): void
    {
        if (!$user) {
            throw new UserNotAuthenticatedException();
        }

        if ($user->password_changed) {
            throw new PasswordAlreadyChangedException();
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed' => true,
        ]);
    }
}