<?php

namespace App\Livewire;

use Exception;
use App\Models\Client;
use Livewire\Component;
use App\Models\Generator;
use App\Models\MeterReading;
use App\Models\MeterCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class ShowClients extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $clients = [];
    public $displayClients = [];

    public $alertMessage = null;
    public $alertType = null;

    public $showEditModal = false;
    public $editingClient;
    public $first_name, $father_name, $last_name, $phone_number, $address, $generator_id, $meter_category_id, $is_offered, $current_meter;

    protected $rules = [
        'first_name' => 'required|string|min:2|max:255',
        'father_name' => 'nullable|string|min:2|max:255',
        'last_name' => 'nullable|string|min:2|max:255',
        'phone_number' => 'nullable|regex:/^(\+?\d{1,4})?\d{8,15}$/',
        'address' => 'required|string|min:3',
        'generator_id' => 'required|exists:generators,id',
        'meter_category_id' => 'nullable|exists:meter_categories,id',
        'is_offered' => 'boolean',
        'current_meter' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'first_name.required' => 'يرجى إدخال الاسم الأول.',
        'first_name.min' => 'يجب أن يكون الاسم الأول مكون من حرفين على الأقل.',
        'first_name.max' => 'الاسم الأول طويل جداً.',
        'father_name.min' => 'يجب أن يكون اسم الأب مكون من حرفين على الأقل.',
        'father_name.max' => 'اسم الأب طويل جداً.',
        'last_name.min' => 'يجب أن يكون اسم العائلة مكون من حرفين على الأقل.',
        'last_name.max' => 'اسم العائلة طويل جداً.',
        'address.required' => 'يرجى إدخال العنوان',
        'address.min' => 'يجب أن يكون العنوان مكون من 3 أحرف على الأقل.',
        'phone_number.regex' => 'رقم الهاتف غير صالح.',
        'generator_id.required' => 'يرجى اختيار المولد.',
        'generator_id.exists' => 'المولد المحدد غير صالح.',
        'meter_category_id.exists' => 'فئة العداد المحددة غير صالحة.',
        'current_meter.required' => 'يرجى إدخال العداد الحالي.',
        'current_meter.numeric' => 'العداد الحالي يجب أن يكون رقمياً.',
        'current_meter.min' => 'العداد الحالي لا يمكن أن يكون أقل من صفر.',
    ];

    public function mount()
    {
        try {
            // Check if user can view clients
            $this->authorize('viewAny', Client::class);

            $this->clients = collect();
            $this->displayClients = collect();
            
            // Check for session flash messages from create client
            if (session('alertMessage')) {
                $this->alertMessage = session('alertMessage');
                $this->alertType = session('alertType', 'success');
            }

            $this->loadClients();

        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض المشتركين', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشتركين', 'danger');
        }
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function loadClients()
    {
        try {
            $this->clients = Client::where('user_id', Auth::id())
                ->when($this->search, function ($query, $search) {
                    $query->search($search);
                })
                ->with(['generator', 'meterCategory'])
                ->orderBy('id')
                ->get();

            $this->updateDisplayClients();

        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل قائمة المشتركين', 'danger');
            $this->clients = collect();
            $this->displayClients = collect();
        }
    }

    public function handleSearch()
    {
        $this->loadClients();
        $this->alertMessage = null;
        $this->alertType = null;

        // Auto-select if only one result
        if ($this->clients->count() === 1) {
            $this->selectedClientId = $this->clients->first()->id;
            $this->updateDisplayClients();
        } else {
            $this->selectedClientId = null;
            $this->updateDisplayClients();
        }
    }

    public function updateDisplayClients()
    {
        if ($this->selectedClientId) {
            $this->displayClients = $this->clients->filter(fn($client) => $client->id == $this->selectedClientId);
        } else {
            $this->displayClients = $this->clients;
        }
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $this->resetValidation();
            $this->alertMessage = null;
            $this->alertType = null;
            $this->updateDisplayClients();
        } else {
            $this->updateDisplayClients();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->loadClients();
    }

    public function toggleActive($id)
    {
        try {
            $client = Client::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $this->authorize('toggleActive', $client);

            $client->is_active = !$client->is_active;
            $client->save();

            $this->setAlert('تم تغيير حالة المشترك.', 'success');
            $this->loadClients();
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتغيير حالة هذا المشترك', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تغيير حالة المشترك.', 'danger');
        }
    }

    public function editClient($id)
    {
        try {
            $client = Client::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $this->authorize('update', $client);

            $this->editingClient = $client;
            $this->first_name = $client->first_name;
            $this->father_name = $client->father_name;
            $this->last_name = $client->last_name;
            $this->phone_number = $client->phone_number;
            $this->address = $client->address;
            $this->generator_id = $client->generator_id;
            $this->meter_category_id = $client->meter_category_id;
            $this->is_offered = $client->is_offered;
            
            $latestReading = $client->meterReadings()->latest('reading_for_month')->first();
            $this->current_meter = $latestReading?->current_meter ?? $client->initial_meter ?? 0;

            $this->showEditModal = true;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتعديل هذا المشترك', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل بيانات المشترك.', 'danger');
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Only handle auto-clearing when is_offered is checked
        if ($propertyName === 'is_offered' && $this->is_offered) {
            $this->meter_category_id = '';
        }
    }
    
    public function updateClient()
    {
        try {
            $this->validate();

            $this->authorize('update', $this->editingClient);

            if (!$this->is_offered && !$this->meter_category_id) {
                $this->addError('meter_category_id', 'يجب اختيار فئة العداد للمشترك غير المعفى من الدفع.');
                return;
            }

            $updateData = [
                'first_name' => $this->first_name,
                'father_name' => $this->father_name,
                'last_name' => $this->last_name,
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'generator_id' => $this->generator_id,
                'is_offered' => $this->is_offered ?? false,
            ];

            $updateData['meter_category_id'] = $this->is_offered ? null : $this->meter_category_id;

            // Check if this is a new client (no meter readings yet)
            $hasMeterReadings = MeterReading::where('client_id', $this->editingClient->id)->exists();
            $meterUpdated = false;
            
            if (!$hasMeterReadings) {
                // New client - update initial_meter only if different
                if ($this->editingClient->initial_meter != $this->current_meter) {
                    $updateData['initial_meter'] = $this->current_meter;
                    $meterUpdated = true;
                }
            } else {
                // Existing client - update current meter only if different
                $latestReading = MeterReading::where('client_id', $this->editingClient->id)
                    ->latest('reading_for_month')
                    ->first();

                if ($latestReading && $latestReading->current_meter != $this->current_meter) {
                    $latestReading->update([
                        'current_meter' => $this->current_meter,
                    ]);
                    $meterUpdated = true;
                }
            }

            $this->editingClient->update($updateData);

            $this->showEditModal = false;
            
            $this->setAlert('تم تحديث بيانات المشترك بنجاح.', 'success');
            
            $this->loadClients();
        } catch (ValidationException $e) {
            return;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتعديل هذا المشترك', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث بيانات المشترك.', 'danger');
        }
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->resetValidation();
        $this->reset([
            'first_name', 'father_name', 'last_name', 'phone_number',
            'address', 'generator_id', 'meter_category_id', 'is_offered', 'current_meter'
        ]);
    }

    public function render()
    {
        try {
            $generators = Generator::where('user_id', Auth::id())->get();
            $categories = MeterCategory::where('user_id', Auth::id())->get();

            return view('livewire.show-clients', compact('generators', 'categories'));
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحميل البيانات', 'danger');
            return view('livewire.show-clients', [
                'generators' => collect(),
                'categories' => collect()
            ]);
        }
    }
}