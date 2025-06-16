<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class AuthController extends Controller
{
   public function showcreate() 
   {
   return view('auths.create');
   }
    public function showlogin() 
    {
    return view('auths.login');
    }
     public function create(request $request){
        $Validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user= User::create($Validated);
        
        Auth::login($user);
        return redirect()->route('web');
     } 
     

   
   
    public function login(Request $request) 
    {
     $Validated = $request->validate([
        
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        if(Auth::attempt($Validated))
        {
            $request->session()->regenerate();
            return redirect()->route('web');
        }
        throw ValidationException::withMessages([
            'credentials' => 'Sorry, incorrect  credentials provided.'
        ]);
            
    }
    public function logout(Request $request) 
    {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
    }
}
