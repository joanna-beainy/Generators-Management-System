<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Models\Generator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class Generators extends Component
{
    public $generators = [];
    public $name;
    public $showAddForm = false;
    public $alertMessage = null;
    public $alertType = null;

    // Listen for the generator-deleted event to reload generators
    protected $listeners = ['generator-deleted' => 'loadGenerators'];

    public function mount()
    {
        try {
            // Check if user can view generators
            $this->authorize('viewAny', Generator::class);
            $this->loadGenerators();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض المولدات', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    private function clearAlert()
    {
        $this->alertMessage = null;
        $this->alertType = null;
    }

    public function loadGenerators()
    {
        try {
            $this->generators = Generator::where('user_id', Auth::id())
                ->withCount('clients') 
                ->get();
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل المولدات', 'danger');
            $this->generators = collect();
        }
    }

    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
        $this->clearAlert();
    }

    public function addGenerator()
    {
        try {
            // Check if user can create generators
            $this->authorize('create', Generator::class);

            $this->validate([
                'name' => 'required|string|max:255|unique:generators,name,NULL,id,user_id,' . Auth::id(),
            ], [
                'name.required' => 'يرجى ادخال اسم المولد.',
                'name.max' => 'اسم المولد طويل جداً.',
                'name.unique' => 'هذا الاسم مستخدم.',
            ]);

            $this->name = strip_tags(trim($this->name));

            Generator::create([
                'name' => $this->name,
                'user_id' => Auth::id(),
            ]);

            $this->setAlert('تمت إضافة المولد بنجاح.', 'success');
            $this->reset(['name', 'showAddForm']);
            $this->loadGenerators();
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مولدات', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء إضافة المولد. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function deleteGenerator($id)
    {
        try {
            $generator = Generator::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if ($generator) {
                $this->authorize('delete', $generator);
                $generator->delete();
                $this->setAlert('تم حذف المولد بنجاح.', 'success');
            } else {
                $this->setAlert('المولد غير موجود.', 'danger');
            }

            // Use event to reload after alert is displayed
            $this->dispatch('generator-deleted');
            
        } catch (AuthorizationException $e) {
            $this->setAlert('لا يمكن حذف هذا المولد لأنه مرتبط بمشتركين.', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف المولد. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.generators');
    }
}