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
    public $showSearchResults = false;

    public $alertMessage = null;
    public $alertType = null;

    public $showEditModal = false;
    public $editingClient;
    public $first_name;
    public $father_name;
    public $last_name;
    public $phone_number;
    public $address;
    public $generator_id;
    public $meter_category_id;
    public $is_offered;
    public $actual_current_meter;
    public $initial_meter;

    protected $rules = [
        'first_name' => 'required|string|min:2|max:255',
        'father_name' => 'nullable|string|min:2|max:255',
        'last_name' => 'nullable|string|min:2|max:255',
        'phone_number' => 'nullable|regex:/^(\+?\d{1,4})?\d{8,15}$/',
        'address' => 'required|string|min:3',
        'generator_id' => 'required|exists:generators,id',
        'meter_category_id' => 'nullable|exists:meter_categories,id',
        'is_offered' => 'boolean',
        'initial_meter' => 'required|numeric|min:0',
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
        'initial_meter.required' => 'يرجى إدخال عداد بداية القراءة القادمة.',
        'initial_meter.numeric' => 'عداد بداية القراءة القادمة يجب أن يكون رقمياً.',
        'initial_meter.min' => 'عداد بداية القراءة القادمة لا يمكن أن يكون أقل من صفر.',
    ];

    public function mount()
    {
        try {
            $this->authorize('viewAny', Client::class);

            $this->clients = collect();
            $this->displayClients = collect();

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
            $this->clients = Client::forUser(Auth::id())
                ->when($this->search, function ($query, $search) {
                    $query->search($search);
                })
                ->with(['generator', 'meterCategory'])
                ->ordered()
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
        $this->refreshSearchState(true);
    }

    public function updatedSearch()
    {
        $this->refreshSearchState(false);
    }

    public function selectClient($clientId)
    {
        $client = $this->clients->firstWhere('id', (int) $clientId);
        if (!$client) {
            return;
        }

        $this->selectedClientId = $client->id;
        $this->showSearchResults = false;
        $this->alertMessage = null;
        $this->alertType = null;
        $this->updateDisplayClients();
    }

    public function updateDisplayClients()
    {
        if ($this->selectedClientId) {
            $this->displayClients = $this->clients->filter(fn ($client) => $client->id == $this->selectedClientId);
        } else {
            $this->displayClients = $this->clients;
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->showSearchResults = false;
        $this->loadClients();
    }

    private function refreshSearchState(bool $allowAutoSelect): void
    {
        $this->loadClients();
        $this->selectedClientId = null;
        $this->alertMessage = null;
        $this->alertType = null;
        $this->showSearchResults = filled(trim($this->search));

        if ($allowAutoSelect && $this->clients->count() === 1) {
            $this->selectedClientId = $this->clients->first()->id;
            $this->showSearchResults = false;
        }

        $this->updateDisplayClients();
    }

    public function toggleActive($id)
    {
        try {
            $client = Client::forUser(Auth::id())
                ->where('id', $id)
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
            $client = Client::forUser(Auth::id())
                ->where('id', $id)
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

            $latestCompletedReading = $client->meterReadings()
                ->whereNotNull('reading_date')
                ->latest('reading_for_month')
                ->first();

            $latestPendingReading = $client->meterReadings()
                ->pending()
                ->latest('reading_for_month')
                ->first();

            $this->actual_current_meter = $latestCompletedReading?->current_meter ?? $client->initial_meter ?? 0;
            $this->initial_meter = $latestPendingReading?->previous_meter ?? $client->initial_meter ?? $this->actual_current_meter;

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
                'initial_meter' => (int) $this->initial_meter,
            ];

            $updateData['meter_category_id'] = $this->is_offered ? null : $this->meter_category_id;

            MeterReading::syncLatestPendingBaselineForClient($this->editingClient->id, (int) $this->initial_meter);

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
            'first_name',
            'father_name',
            'last_name',
            'phone_number',
            'address',
            'generator_id',
            'meter_category_id',
            'is_offered',
            'actual_current_meter',
            'initial_meter',
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
                'categories' => collect(),
            ]);
        }
    }
}
