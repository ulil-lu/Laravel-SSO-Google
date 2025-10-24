<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        // Google user object dari google
        // use stateless() to avoid "InvalidStateException" when the session state
        // cannot be validated (useful for some dev setups or cross-origin flows).
        // If you prefer stricter protection, remove ->stateless() and ensure sessions
        // persist between redirect and callback.
        // Removed ->stateless() because the Provider contract does not declare it;
        // use ->user() and ensure sessions persist between redirect and callback.
        $userFromGoogle = Socialite::driver('google')->user();
        // Ambil user dari database berdasarkan google user id
        $userFromDatabase = User::where('google_id', $userFromGoogle->getId())->first();
        // Jika tidak ada user, maka buat user baru
        if (!$userFromDatabase) {
            // ensure we set a password because the users table requires it
            // we generate a random password; the User model casts 'password' to 'hashed'

            $newUser = new User([
                'google_id' => $userFromGoogle->getId(),
                'name' => $userFromGoogle->getName(),
                'email' => $userFromGoogle->getEmail(),
                'password' => Str::random(32),
            ]);

            $newUser->save();

            // Login user yang baru dibuat
            /** @var \Illuminate\Contracts\Auth\StatefulGuard $guard */
            $guard = auth('web');
            $guard->login($newUser);
            session()->regenerate();

            return redirect('/');
        }

        // Jika ada user langsung login saja
        /** @var \Illuminate\Contracts\Auth\StatefulGuard $guard */
        $guard = auth('web');
        $guard->login($userFromDatabase);
        session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request)
    {
        /** @var \Illuminate\Contracts\Auth\StatefulGuard $guard */
        $guard = Auth::guard('web');
        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
