<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showOnboarding()
    {
        return view('auth.onboarding');
    }
    
    public function roleSelection(Request $request)
    {
        $role = $request->validate([
            'role' => 'required|string|in:admin,supplier,vendor',
        ])['role'];
        
        // Store the selected role in the session
        session(['selected_role' => $role]);
        
        // Check if the user has an account with this email for the selected role
        if (Auth::check()) {
            // If user is already logged in, redirect to dashboard
            return redirect()->route('dashboard');
        }
        
        // User is not logged in, send to login page with role
        return redirect()->route('show.login', ['role' => $role]);
    }
    
    public function showcreate(Request $request) 
    {
        $role = $request->query('role', session('selected_role'));
        
        if (!$role) {
            return redirect()->route('onboarding');
        }
        
        return view('auth.create', ['role' => $role]);
    }
    
    public function showlogin(Request $request) 
    {
        $role = $request->query('role', session('selected_role'));
        
        if (!$role) {
            return redirect()->route('onboarding');
        }
        
        return view('auth.login', ['role' => $role]);
    }

    public function showApp() {
        if (!Auth::check()) {
            return redirect()->route('onboarding');
        }
        
        return view('dashboard');
    }
    
    public function create(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,supplier,vendor',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role']
        ]);
        
        Auth::login($user);
        return redirect()->route('dashboard');
    } 
       
    public function login(Request $request) 
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'role' => 'required|string|in:admin,supplier,vendor',
        ]);
        
        // Check if user exists with the email and role
        $user = User::where('email', $validated['email'])
                    ->where('role', $validated['role'])
                    ->first();
                    
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email for the selected role.',
            ]);
        }
        
        if (Auth::attempt([
            'email' => $validated['email'], 
            'password' => $validated['password'],
            'role' => $validated['role']
        ])) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }
        
        throw ValidationException::withMessages([
            'email' => 'These credentials do not match our records.',
        ]);
    }
    
    public function logout(Request $request) 
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('onboarding');
    }
}

