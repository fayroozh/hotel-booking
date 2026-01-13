<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        // هنا نرجّع URL حتى React يرسل المستخدم لجوجل
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        // لو البريد موجود نسجله وإلا نعمل مستخدم جديد
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'email_verified_at' => now(),
                // لو بدك تحفظ الصورة:
                'avatar' => $googleUser->getAvatar(),
            ]
        );

        // أعمل Token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
