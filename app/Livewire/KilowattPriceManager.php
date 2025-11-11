<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Models\KilowattPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class KilowattPriceManager extends Component
{
    public $price;
    public $alertMessage = null;
    public $alertType = null;

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

    public function mount()
    {
        try {
            // Get the user's kilowatt price if it exists
            $userKilowattPrice = KilowattPrice::where('user_id', Auth::id())->first();
            
            if ($userKilowattPrice) {
                // Check if user can view their specific kilowatt price
                $this->authorize('view', $userKilowattPrice);
                $this->price = $userKilowattPrice->price;
            } else {
                // No existing record, use default
                $this->price = 0;
            }
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لعرض سعر الكيلووات', 'danger');
        }
    }

    public function updatePrice()
    {
        try {
            $existingPrice = KilowattPrice::where('user_id', Auth::id())->first();


            if ($existingPrice) {
                // Check authorization for update
                $this->authorize('update', $existingPrice);
            } else {
                // Check authorization for create
                $this->authorize('create', KilowattPrice::class);
            }

            $this->validate([
                'price' => 'required|numeric|min:0',
            ], [
                'price.required' => 'يرجى ادخال سعر الكيلووات .',
                'price.numeric' => 'يجب أن يكون سعر الكيلووات رقمياً.',
                'price.min' => 'لا يمكن أن يكون سعر الكيلووات أقل من صفر.',
            ]);
            
            if ($existingPrice) {
                $existingPrice->update(['price' => $this->price]);
            } else {

                $existingPrice = KilowattPrice::create([
                    'user_id' => Auth::id(),
                    'price' => $this->price
                ]);
            }

            $this->setAlert('تم تحديث سعر الكيلووات بنجاح.', 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->setAlert('ليس لديك صلاحية لتحديث سعر الكيلووات', 'danger');    
        } catch (Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث سعر الكيلووات. يرجى المحاولة لاحقاً.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.kilowatt-price-manager');
    }
}
