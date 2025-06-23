<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class userManagerController extends Controller
{
    /**
     * Display a listing of the users.
     * Accessible only by admins.
     */
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch all users from the database
        $users = User::all();

        return view('admin.users', compact('users'));
    }

    /**
     * Store a newly created user in storage.
     * Accessible only by admins.
     */
    public function store(Request $request)
    {
        // Ensure only admins can create users
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'supplier', 'vendor'])], // Use Rule::in for better validation
            'phone' => ['nullable', 'string', 'max:255'], // Ensure phone validation is here if you're collecting it 
        ]);

            // Get the last user's ID to generate the next sequential ID
        // This is crucial for new user creation via forms.
        /*$lastUser = User::latest('id')->first();
        $newId = 'U00001'; // Default starting ID if no users exist yet

        if ($lastUser) {
            // Extract the numeric part (e.g., '00001' from 'U00001')
            $numericPart = (int) substr($lastUser->id, 1);
            $nextNumericPart = $numericPart + 1;
            // Pad the number with leading zeros and prefix with 'U'
            $newId = 'U' . str_pad($nextNumericPart, 5, '0', STR_PAD_LEFT);
        }*/

        // Create the new user
        User::create([
            /*'id' => $newId,*/
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'phone' => $validatedData['phone'] ?? null,
        ]);

        // Redirect back with a success message
        return redirect()->route('admin.users.index')->with('success', 'User added successfully!');
    }

    /**
     * Update the specified user in storage.
     * Accessible only by admins.
     */
    public function update(Request $request, User $user)
    {
        // Ensure only admins can update users
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the incoming request data
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:admin,supplier,vendor'],
        ];

        // Only validate password if it's provided (optional for updates)
        if ($request->filled('password')) {
            $validationRules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validatedData = $request->validate($validationRules);

        // Update user data
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validatedData['password'])
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     * Accessible only by admins.
     */
    public function destroy(User $user)
    {
        // Ensure only admins can delete users
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent users from deleting themselves
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        // Delete the user
        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User '{$userName}' has been deleted successfully.");
    }
}


