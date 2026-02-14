<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Licensing\LicenseService;

class Activation extends Component
{
    use WithFileUploads;

    public string $machineId = '';
    public $licenseFile;
    public string $error = '';

    public function mount(LicenseService $license)
    {
        if ($license->isActivated()) {
            return redirect('/');
        }

        $this->machineId = $license->getMachineId();
    }

    public function activate(LicenseService $license)
    {
        $this->error = '';

        if (!$this->licenseFile) {
            $this->error = 'Please select a license file.';
            return;
        }

        try {
            $content = $this->licenseFile->get();
            if ($license->activateFromFile($content)) {
                return redirect('/');
            } else {
                $this->error = 'Activation failed. Please ensure the license matches this Machine ID.';
            }
        } catch (\Throwable $e) {
            // Show the actual error message for debugging
            $this->error = 'Error: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.activation')
            ->layout('layouts.guest');
    }
}
