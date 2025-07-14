<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\SupplyCenter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Log;

class AuthController extends Controller
{
    public function showOnboarding()
    {
        return view('auth.onboarding');
    }
    
    public function roleSelection(Request $request)
    {
        Log::info("Role selection: " . $request);
        $role = $request->validate([
            'role' => 'required|string|in:admin,supplier,vendor',
        ])['role'];
        
        // Store the selected role in the session
        session(['selected_role' => $role]);
        
        Log::info("USER LOGGED IN? " . (Auth::check() ? 'true' : 'false'));

        // Check if the user has an account with this email for the selected role
        if (Auth::check()) {
            // If user is already logged in, redirect to dashboard
            Log::info("USER LOGGED IN? " . (Auth::check() ? 'true' : 'false'));
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

    /*public function showApp() {
        if (!Auth::check()) {
            return redirect()->route('onboarding');
        }
        
        return view('dashboard');
    }*/
    
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

        // Since the database trigger generates the ID, we need to retrieve it manually
        $user->id = \DB::table('users')->where('email', $user->email)->value('id');

        // Automatically create supplier or wholesaler record
        $this->createRoleSpecificRecord($user);
        
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
    
    /**
     * Create supplier or wholesaler record based on user role
     */
    private function createRoleSpecificRecord(User $user)
    {
        // Ensure user has an ID
        if (!$user->id) {
            Log::error('Cannot create role-specific record: User has no ID', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role
            ]);
            return;
        }

        try {
            Log::info('Creating role-specific record via AuthController', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => $user->role
            ]);

            if ($user->role === 'supplier') {
                // Get first available supply center
                $supplyCenter = SupplyCenter::first();
                if ($supplyCenter) {
                    $supplier = Supplier::create([
                        'user_id' => $user->id,
                        'supply_center_id' => $supplyCenter->id,
                        'name' => $user->name,
                        'contact_person' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? '0000000000',
                        'address' => 'Address to be updated',
                        'registration_number' => 'REG' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . time(),
                        'approved_date' => now()
                    ]);
                    
                    Log::info('Supplier record created successfully via AuthController', [
                        'user_id' => $user->id,
                        'supplier_id' => $supplier->id
                    ]);
                } else {
                    Log::error('No supply center found for supplier creation via AuthController', [
                        'user_id' => $user->id
                    ]);
                }
            } elseif ($user->role === 'vendor') {
                $wholesaler = Wholesaler::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'contact_person' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '0000000000',
                    'address' => 'Address to be updated',
                    'distribution_region' => 'Region to be updated',
                    'registration_number' => 'WHL' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . time(),
                    'approved_date' => now()
                ]);
                
                \Log::info('Wholesaler record created successfully via AuthController', [
                    'user_id' => $user->id,
                    'wholesaler_id' => $wholesaler->id
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the user creation
            \Log::error('Failed to create role-specific record via AuthController', [
                'user_id' => $user->id,
                'role' => $user->role,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

