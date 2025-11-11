<?php
namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class ExchangeRateModal extends Component
{
    public $showModal = false;
    public $exchangeRate;
    public $alertMessage = null;
    public $alertType = null;

    protected $listeners = ['openExchangeRateModal' => 'openModal'];

    public function mount()
    {
        try {
            // Get the user's exchange rate if it exists
            $userExchangeRate = ExchangeRate::where('user_id', Auth::id())->first();
            
            if ($userExchangeRate) {
                // Check if user can view their specific exchange rate
                $this->authorize('view', $userExchangeRate);
                $this->exchangeRate = $userExchangeRate->exchange_rate;
            } else {
                // No existing record, use default
                $this->exchangeRate = 89500;
            }
        } catch (AuthorizationException $e) {
            // Silently handle authorization error in mount
            $this->exchangeRate = 89500;
        }
    }

    public function openModal()
    {
        try {
            // Get the user's exchange rate to check authorization
            $userExchangeRate = ExchangeRate::where('user_id', Auth::id())->first();
            
            if ($userExchangeRate) {
                $this->authorize('view', $userExchangeRate);
            } else {
                // If no record exists, user can still view (they'll be creating one)
                $this->authorize('create', ExchangeRate::class);
            }
            
            $this->showModal = true;
            $this->clearAlert();
        } catch (AuthorizationException $e) {
            $this->dispatch('showAlert', 
                message: 'ليس لديك صلاحية لعرض سعر الصرف', 
                type: 'danger'
            );
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

    public function updateRate()
    {
        try {

            // Get existing exchange rate if it exists
            $existingRate = ExchangeRate::where('user_id', Auth::id())->first();

            if ($existingRate) {
                // Check if user can update their specific exchange rate
                $this->authorize('update', $existingRate);
            } else {
                // If no record exists, check if user can create one
                $this->authorize('create', ExchangeRate::class);
            }


            $this->validate([
                'exchangeRate' => 'required|integer|min:1',
            ], [
                'exchangeRate.required' => 'يرجى إدخال سعر الصرف',
                'exchangeRate.min' => 'سعر الصرف يجب أن يكون أكبر من الصفر',
            ]);

            if ($existingRate) {
                // Update existing record
                $existingRate->update([
                    'exchange_rate' => $this->exchangeRate,
                ]);
            } else {
                // Create new record
                ExchangeRate::create([
                    'user_id' => Auth::id(),
                    'exchange_rate' => $this->exchangeRate,
                ]);
            }

            // Dispatch event to show alert in dashboard
            $this->dispatch('showAlert', 
                message: 'تم تحديث سعر الصرف بنجاح!', 
                type: 'success'
            );
            
            $this->showModal = false;
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتحديث سعر الصرف', 'danger');
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث سعر الصرف. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->clearAlert();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.exchange-rate-modal');
    }
}