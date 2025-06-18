<?php

namespace App\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Wizard extends Component
{
    public $selectedRole;
    
    public function selectRole($role)
    {
        Auth::user()->update([
            'role' => $role,
            'onboarded' => true,
        ]);


        return redirect()->route("{$role}.dashboard");
    }
    
    public function render()
    {
        return view('livewire.onboarding.wizard')
            ->layout('components.layouts.app');
    }
}