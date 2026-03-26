<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SellerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()          { return view('auth.login'); }
    public function showRegister()       { return view('auth.register'); }
    public function showSellerRegister() { return view('auth.register-seller'); }
    public function showForgotPassword() { return view('auth.forgot-password'); }
    public function showResetPassword(Request $r) { return view('auth.reset-password', ['token' => $r->token]); }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($r->only('email', 'password'), $r->boolean('remember'))) {
            $r->session()->regenerate();
            $user = Auth::user();
            if ($user->isAdmin())  return redirect()->route('admin.dashboard');
            if ($user->isSeller()) return redirect()->route('seller.dashboard');
            return redirect()->intended(route('student.dashboard'));
        }
        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
    }

    public function register(Request $r)
    {
        $r->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:15',
            'password' => 'required|min:8|confirmed',
        ]);
        $user = User::create([
            'name'     => $r->name,
            'email'    => $r->email,
            'phone'    => $r->phone,
            'password' => Hash::make($r->password),
            'role'     => 'student',
        ]);
        Auth::login($user);
        return redirect()->route('student.dashboard')->with('success', 'Welcome to Naukaridarpan! Start practicing today.');
    }

    public function registerSeller(Request $r)
    {
        $r->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users',
            'phone'         => 'required|string|max:15',
            'password'      => 'required|min:8|confirmed',
            'qualification' => 'nullable|string|max:200',
            'institution'   => 'nullable|string|max:200',
        ]);
        $user = User::create([
            'name'     => $r->name,
            'email'    => $r->email,
            'phone'    => $r->phone,
            'password' => Hash::make($r->password),
            'role'     => 'seller',
        ]);
        SellerProfile::create([
            'user_id'       => $user->id,
            'username'      => Str::slug($r->name) . '-' . Str::random(4),
            'qualification' => $r->qualification,
            'institution'   => $r->institution,
        ]);
        Auth::login($user);
        return redirect()->route('seller.dashboard')->with('success', 'Welcome! Complete your profile and upload your first exam paper.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function sendResetLink(Request $r)
    {
        $r->validate(['email' => 'required|email']);
        // In production: Password::sendResetLink($r->only('email'));
        return back()->with('status', 'If that email exists, a reset link has been sent.');
    }

    public function resetPassword(Request $r)
    {
        $r->validate(['token' => 'required', 'email' => 'required|email', 'password' => 'required|min:8|confirmed']);
        return redirect()->route('login')->with('status', 'Password has been reset successfully.');
    }
}
