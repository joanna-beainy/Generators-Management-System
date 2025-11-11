<?php
namespace App\Livewire;

use Exception;
use App\Models\Client;
use Livewire\Component;
use App\Models\Generator;
use App\Models\MeterCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class CreateClient extends Component
{
    public $first_name = '';
    public $father_name = '';
    public $last_name = '';
    public $address = '';
    public $phone_number = null;
    public $generator_id = '';
    public $meter_category_id = '';
    public $initial_meter = null;
    public $is_offered = false;
    public $alertMessage = null;
    public $alertType = null;

    protected $rules = [
        'first_name' => 'required|min:2|max:255',
        'father_name' => 'nullable|min:2|max:255',
        'last_name' => 'nullable|min:2|max:255',
        'address' => 'required|min:3',
        'phone_number' => 'nullable|regex:/^(\+?\d{1,4})?\d{8,15}$/',
        'generator_id' => 'required|exists:generators,id',
        'meter_category_id' => 'nullable|exists:meter_categories,id',
        'initial_meter' => 'nullable|integer|min:0',
        'is_offered' => 'boolean',
    ];

    protected $messages = [
        'first_name.required' => 'يرجى ادخال الاسم الأول.',
        'first_name.min' => 'يجب أن يكون الاسم الأول مكون من حرفين على الأقل.',
        'first_name.max' => 'الاسم الأول طويل جداً.',
        'father_name.min' => 'يجب أن يكون اسم الأب مكون من حرفين على الأقل.',
        'father_name.max' => 'اسم الأب طويل جداً.',
        'last_name.min' => 'يجب أن يكون اسم العائلة مكون من حرفين على الأقل.',
        'last_name.max' => 'اسم العائلة طويل جداً.',
        'address.required' => 'يرجى ادخال العنوان.',
        'address.min' => 'يجب أن يكون العنوان مكون من 3 أحرف على الأقل.',
        'phone_number.regex' => 'رقم الهاتف غير صالح.',
        'generator_id.required' => 'يرجى اختيار المولد.',
        'generator_id.exists' => 'المولد المحدد غير صالح.',
        'meter_category_id.exists' => 'فئة العداد المحددة غير صالحة.',
        'initial_meter.integer' => 'قراءة العداد الابتدائية يجب أن تكون رقماً صحيحاً.',
        'initial_meter.min' => 'قراءة العداد الابتدائية لا يمكن أن تكون أقل من صفر.',
    ];

    public function mount()
    {
        try {
            // Check if user can create clients
            $this->authorize('create', Client::class);
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مشتركين', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Only handle auto-clearing when is_offered is checked
        if ($propertyName === 'is_offered' && $this->is_offered) {
            $this->meter_category_id = '';
        }
    }

    public function store()
    {
        $this->validate();

        try {
            // Check authorization again before creating
            $this->authorize('create', Client::class);

            // Additional validation for offered clients
            if ($this->is_offered && $this->meter_category_id) {
                $this->addError('meter_category_id', 'لا يمكن اختيار فئة العداد للمشترك المعفى.');
                return;
            }

            if (!$this->is_offered && !$this->meter_category_id) {
                $this->addError('meter_category_id', 'يرجى اختيار فئة العداد للمشترك غير المعفى.');
                return;
            }

            $clientData = [
                'first_name' => strip_tags(trim($this->first_name)),
                'father_name' => strip_tags(trim($this->father_name ?? '')),
                'last_name' => strip_tags(trim($this->last_name ?? '')),
                'address' => strip_tags(trim($this->address)),
                'phone_number' => !empty(trim($this->phone_number)) ? $this->phone_number : null,
                'generator_id' => $this->generator_id,
                'meter_category_id' => $this->is_offered ? null : $this->meter_category_id,
                'initial_meter' => $this->initial_meter ?? 0,
                'is_offered' => $this->is_offered,
                'user_id' => Auth::id(),
            ];

            $client = Client::create($clientData);

            if ($client) {
                session()->flash('alertMessage', 'تمت إضافة المشترك بنجاح.');
                session()->flash('alertType', 'success');
                
                // Reset form
                $this->reset([
                    'first_name', 'father_name', 'last_name', 'address',
                    'phone_number', 'generator_id', 'meter_category_id', 
                    'initial_meter', 'is_offered'
                ]);
                
                // redirect to clients list
                return redirect()->route('clients.index');
            } else {
                $this->setAlert('حدث خطأ أثناء إنشاء المشترك.', 'danger');
            }

        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لإضافة مشتركين', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ غير متوقع. الرجاء المحاولة مرة أخرى.', 'danger');
        }
    }

    public function render()
    {
        try {
            $generators = Generator::where('user_id', Auth::id())->get();
            $categories = MeterCategory::where('user_id', Auth::id())->get();

            return view('livewire.create-client', compact('generators', 'categories'));
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل البيانات', 'danger');
            return view('livewire.create-client', [
                'generators' => collect(),
                'categories' => collect()
            ]);
        }
    }
}