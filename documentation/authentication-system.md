# BeanTrack Authentication System Documentation

This document provides a comprehensive overview of the authentication system implemented in the BeanTrack application, which tracks coffee beans from farm to cup.

## Table of Contents

1. [Overview](#overview)
2. [User Flow](#user-flow)
3. [Key Components](#key-components)
4. [Database Structure](#database-structure)
5. [Controllers](#controllers)
6. [Views](#views)
7. [Middleware](#middleware)
8. [Models](#models)
9. [Routes](#routes)

## Overview

BeanTrack's authentication system provides role-based access control with three distinct user roles: Admin, Supplier, and Vendor. The system starts with an onboarding page where users select their role, after which they are directed to either login or register based on whether they already have an account.

## User Flow

1. User visits the root URL (`beantrack.test/`)
2. The onboarding page is displayed with three role options
3. User selects a role (Admin, Supplier, or Vendor)
4. System checks if the user is already authenticated:
   - If authenticated, redirect to the dashboard
   - If not authenticated, redirect to the login page
5. At the login page:
   - If the user has an account, they log in
   - If not, they can navigate to the registration page
6. After successful authentication, the user is directed to a role-specific dashboard

## Key Components

### Database Structure

User data is stored in the `users` table with the following structure:

```php
Schema::create('users', function (Blueprint $table) {
    $table->string('id', 6)->primary();
    $table->string('email', 255)->unique();
    $table->string('password', 255);
    $table->enum('role', ['admin', 'supplier', 'vendor']);
    $table->string('name', 255);
    $table->string('phone', 255)->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
});
```

A trigger is used to generate sequential user IDs:

```php
DB::unprepared("CREATE TRIGGER before_users_insert BEFORE INSERT ON users FOR EACH ROW 
    BEGIN 
        DECLARE last_id INT; 
        SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM users ORDER BY id DESC LIMIT 1; 
        SET NEW.id = CONCAT('U', LPAD(COALESCE(last_id + 1, 1), 5, '0')); 
    END");
```

### Controllers

#### AuthController

The `AuthController` handles all authentication-related actions:

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Shows the initial role selection page
    public function showOnboarding() { /* ... */ }
    
    // Processes the role selection form
    public function roleSelection(Request $request) { /* ... */ }
    
    // Shows the registration form
    public function showcreate(Request $request) { /* ... */ }
    
    // Shows the login form
    public function showlogin(Request $request) { /* ... */ }
    
    // Shows the dashboard after authentication
    public function showApp() { /* ... */ }
    
    // Processes the registration form
    public function create(Request $request) { /* ... */ }
    
    // Processes the login form
    public function login(Request $request) { /* ... */ }
    
    // Handles user logout
    public function logout(Request $request) { /* ... */ }
}
```

**Key Method: Role Selection**

```php
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
```

**Key Method: User Creation**

```php
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
```

**Key Method: User Login**

```php
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
```

### Views

#### Onboarding View (onboarding.blade.php)

The onboarding view displays the role selection options:

```html
<section class="px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
    <!-- Admin Card -->
    <form action="{{ route('role.select') }}" method="POST">
        @csrf
        <input type="hidden" name="role" value="admin">
        <button type="submit" class="w-full">
            <div class="cursor-pointer transition hover:scale-110 bg-white shadow rounded-lg overflow-hidden text-center">
                <img src="/images/landing-page-image-1.jpg" alt="Admin" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">Admin</h3>
                    <p class="text-sm text-gray-600 mt-2">Manage users, roles, and system settings.</p>
                </div>
            </div>
        </button>
    </form>

    <!-- Similar cards for Supplier and Vendor roles -->
</section>
```

#### Login View (login.blade.php)

```html
<form method="POST" action="{{ route('login') }}" class="w-full">
    @csrf
    <input type="hidden" name="role" value="{{ $role ?? 'admin' }}">
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-coffee-brown mb-2">Account Type: {{ ucfirst($role ?? 'admin') }}</h2>
        <p class="text-sm text-brown">Logging in as {{ ucfirst($role ?? 'admin') }}</p>
    </div>
    <!-- Email and password fields -->
    <button type="submit" class="text-white w-full rounded p-3 font-semibold mt-5 bg-coffee-brown hover:bg-light-brown transition-colors duration-300">
        Login
    </button>
</form>
```

#### Registration View (create.blade.php)

```html
<form action="{{ route('create') }}" method="POST">
    @csrf
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-coffee-brown mb-2">Account Type: {{ ucfirst($role ?? 'admin') }}</h2>
        <p class="text-sm text-brown">Creating a new {{ ucfirst($role ?? 'admin') }} account</p>
    </div>
    <input type="hidden" name="role" value="{{ $role ?? 'admin' }}">
    <!-- Name, email, password fields -->
    <button type="submit" class="w-full font-semibold bg-coffee-brown text-white hover:bg-light-brown rounded p-3">Create account</button>
</form>
```

#### Dashboard View (dashboard.blade.php)

```html
@extends('layouts.main-view')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-semibold text-coffee-brown mb-6">{{ ucfirst(Auth::user()->role) }} Dashboard</h1>
    
    <!-- Analytics Cards -->
    
    <!-- Role-specific content -->
    @if(Auth::user()->isAdmin())
        @include('dashboard.admin')
    @elseif(Auth::user()->isSupplier())
        @include('dashboard.supplier')
    @elseif(Auth::user()->isVendor())
        @include('dashboard.vendor')
    @endif
</div>
@endsection
```

### Middleware

#### Middleware Stack

BeanTrack uses Laravel's middleware system to process HTTP requests. The middleware configuration is defined in `app/Http/Kernel.php`:

```php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        
        // API middleware group...
    ];

    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'role' => \App\Http\Middleware\CheckRole::class,
        // Other middleware aliases...
    ];
}
```

#### Core Middleware Classes

The application includes several core middleware classes essential for web functionality:

1. **EncryptCookies**: Encrypts and decrypts cookies to prevent tampering
2. **VerifyCsrfToken**: Protects against Cross-Site Request Forgery attacks
3. **TrimStrings**: Automatically trims whitespace from request inputs
4. **TrustProxies**: Configures trusted proxies for proper IP detection
5. **PreventRequestsDuringMaintenance**: Blocks requests when in maintenance mode

#### Authentication Middleware

##### CheckRole Middleware

This middleware verifies that the authenticated user has the required role:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
```

##### Authenticate Middleware

This middleware ensures that users are authenticated before accessing protected routes:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
```

##### RedirectIfAuthenticated Middleware

This middleware prevents authenticated users from accessing guest routes:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
```

### Models

#### User Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'email', 'email_verified_at', 'password', 'role', 'phone', 
        'remember_token', 'created_at', 'updated_at'
    ];
    
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'user_id');
    }
    
    // Other relationships...
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function isSupplier()
    {
        return $this->role === 'supplier';
    }
    
    public function isVendor()
    {
        return $this->role === 'vendor';
    }
    
    public function getDashboardRoute()
    {
        switch ($this->role) {
            case 'admin':
                return 'admin.dashboard';
            case 'supplier':
                return 'supplier.dashboard';
            case 'vendor':
                return 'vendor.dashboard';
            default:
                return 'dashboard';
        }
    }
}
```

### Routes

```php
// Onboarding route - entry point
Route::get('/', [AuthController::class, 'showOnboarding'])->name('onboarding');

// Role selection from onboarding page
Route::post('/role-select', [AuthController::class, 'roleSelection'])->name('role.select');

// Authentication routes for guests
Route::middleware(['guest'])->controller(AuthController::class)->group(function () {
    Route::get('/create', 'showcreate')->name('show.create');
    Route::get('/login', 'showlogin')->name('show.login');
    Route::post('/create', 'create')->name('create');
    Route::post('/login', 'login')->name('login');
});

// Routes for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::post('/logout',[AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'showApp'])->name('dashboard');
    
    // Role-specific routes
    Route::middleware(['role:admin'])->group(function () {
        // Admin routes
    });
    
    Route::middleware(['role:supplier'])->group(function () {
        // Supplier routes
    });
    
    Route::middleware(['role:vendor'])->group(function () {
        // Vendor routes
    });
});
```

## Data Flow

1. **User Selects a Role (Onboarding → Role Selection):**
   - Form submission with role value to `roleSelection` method
   - Role is stored in the session
   - User is redirected to login page with the role parameter

2. **User Registers (Registration Form → Database):**
   - Form submission to `create` method in AuthController
   - Data is validated
   - New user record is created in the database with the specified role
   - User is automatically logged in
   - Redirect to role-specific dashboard

3. **User Logs In (Login Form → Authentication):**
   - Form submission to `login` method in AuthController
   - Credentials are validated against database records
   - System checks that email and role match
   - On success, user session is created
   - Redirect to role-specific dashboard

4. **Dashboard Display (Auth → View):**
   - `showApp` method renders the dashboard view
   - User's role is used to determine which dashboard partial to include
   - Appropriate data is fetched and displayed based on role

5. **User Logs Out (Request → Session Termination):**
   - `logout` method terminates the user session
   - Session data is invalidated
   - New CSRF token is generated
   - User is redirected to onboarding page

## Middleware Implementation Details

### VerifyCsrfToken

```php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
```

This middleware validates CSRF tokens on incoming requests for routes that modify state, protecting your application from cross-site request forgery attacks. Form requests without a valid CSRF token will be rejected.

### EncryptCookies

```php
namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
```

This middleware encrypts cookies to prevent tampering and unauthorized access to cookie data. BeanTrack uses this to protect session cookies, CSRF tokens, and other sensitive cookie data.

### TrimStrings

```php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];
}
```

This middleware automatically trims whitespace from all request input fields except for password fields, improving data quality and user experience.

## Security Considerations

1. **Role Validation:**
   - Roles are validated at both the controller and middleware levels
   - Only predefined roles (admin, supplier, vendor) are accepted

2. **Authentication:**
   - Passwords are hashed using Laravel's Hash facade
   - Authentication status is checked before accessing protected routes
   - Session regeneration on login for security

3. **Access Control:**
   - Role-specific middleware restricts access to appropriate routes
   - Users can only access features designated for their role

4. **Form Protection:**
   - CSRF tokens are used on all forms to prevent cross-site request forgery
   - Input validation is performed on all form submissions
   - Laravel's built-in protection mechanisms are fully implemented

## Conclusion

The BeanTrack authentication system provides a comprehensive role-based approach to user management, ensuring that users can only access features and data relevant to their role in the coffee supply chain. The system leverages Laravel's robust middleware architecture to handle various aspects of HTTP request processing, from CSRF protection and cookie encryption to role-based access control.

The implementation includes all necessary middleware components for a secure web application:
- **Security middleware** for CSRF protection, cookie encryption, and input sanitization
- **Authentication middleware** for controlling access to protected routes
- **Role-based middleware** for fine-grained permission control

By utilizing these components together, BeanTrack creates a secure, efficient, and user-friendly authentication experience tailored to the specific needs of different user types in the coffee bean supply chain.

This documentation provides a complete reference for understanding the authentication system's architecture, data flow, and security features, serving as a guide for future development and maintenance of the system.
