<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class PasswordConfirmationModal extends Component
{
    public $isOpen = false;
    public $password = '';
    public $targetRoute = '';

    #[On('confirmPassword')]
    public function confirmPassword($data = null)
    {
        // Capture route and save to session immediately
        $this->targetRoute = $data ?: '';
        session(['confirm_destination' => $this->targetRoute]);
        
        $this->password = '';
        $this->resetErrorBag();
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
        $this->password = '';
        session()->forget('confirm_destination');
    }

    public function verify()
    {
        // Get target from session if property is lost
        $destination = $this->targetRoute ?: session('confirm_destination');

        $this->validate([
            'password' => 'required',
        ], [
            'password.required' => 'يرجى إدخال كلمة السر.',
        ]);

        if (Hash::check($this->password, Auth::user()->password)) {
            if (!str_contains($destination, 'user-profile')) {
                session()->put('auth.password_confirmed_at', time());
            }
            
            session()->forget('confirm_destination');
            $this->isOpen = false;
            
            return redirect()->to($destination);
        }

        $this->addError('password', 'كلمة السر غير صحيحة.');
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.password-confirmation-modal');
    }
}
