<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Generator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class Generators extends Component
{
    public $generators = [];
    public $name;
    public $showAddForm = false;

    public function mount()
    {
        $this->loadGenerators();
    }

    public function loadGenerators()
    {
         $this->generators = Generator::where('user_id', Auth::id())
        ->withCount('customers') 
        ->get();
    }

    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
    }

    public function addGenerator()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->name = strip_tags(trim($this->name));

        Generator::create([
            'name' => $this->name,
            'user_id' => Auth::id(),
        ]);

        session()->flash('success', 'تمت إضافة المولد بنجاح.');

        $this->reset(['name', 'showAddForm']);
        $this->loadGenerators();
    }

    public function deleteGenerator($id)
    {
        $generator = Generator::findOrFail($id);
        try {
            $this->authorize('delete', $generator);
            $generator->delete();
            session()->flash('success', 'تم حذف المولد بنجاح.');
        } catch (AuthorizationException $e) {
            session()->flash('error', 'لا يمكن حذف هذا المولد لأنه مرتبط بمشتركين.');
        }
        $this->loadGenerators();
    }

    public function render()
    {
        return view('livewire.generators');
    }
}
