<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Native\Desktop\Facades\Alert;

class UserProfile extends Component
{
    public $name;
    public $password;
    public $password_confirmation;
    public $phoneNumbers = [];
    public $newPhone = '';
    public $alertMessage = null;
    public $alertType = null;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->phoneNumbers = $user->phoneNumbers->pluck('phone_number', 'id')->toArray();
    }

    private function setAlert($message, $type = 'success')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.required' => 'يرجى إدخال الاسم.',
            'name.max' => 'الاسم طويل جداً.',
            'password.min' => 'يجب أن تكون كلمة المرور 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        try {
            $user = Auth::user();
            $updated = false;

            // Update name if changed
            if ($this->name !== $user->name) {
                $user->name = $this->name;
                $updated = true;
            }

            // Update password if provided
            if ($this->password) {
                $user->password = Hash::make($this->password);
                $updated = true;
            }

            if ($updated) {
                $user->save();
                $this->setAlert('تم تحديث المعلومات بنجاح.', 'success');
                $this->reset(['password', 'password_confirmation']);
            } else {
                $this->setAlert('لم يتم إجراء أي تغييرات.', 'info');
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->setAlert('حدث خطأ أثناء تحديث المعلومات.', 'danger');
        }
    }

    public function addPhone()
    {
        $this->validate([
            'newPhone' => 'required|string|regex:/^(\+?\d{1,4})?\d{8,15}$/|max:20',
        ], [
            'newPhone.required' => 'يرجى إدخال رقم الهاتف.',
            'newPhone.regex' => 'رقم الهاتف غير صالح.',
            'newPhone.max' => 'الرقم طويل جداً.',
        ]);

        try {
            $user = Auth::user();
            $phone = $user->phoneNumbers()->create(['phone_number' => $this->newPhone]);
            $this->phoneNumbers[$phone->id] = $phone->phone_number;
            $this->newPhone = '';

            $this->setAlert('تمت إضافة الرقم بنجاح.', 'success');
        } catch (ValidationException $e) {
            throw $e;
        }catch (\Exception $e) {
            $this->setAlert('حدث خطأ أثناء إضافة الرقم.', 'danger');
        }
    }

    public function confirmDeletePhone($id)
    {
        $buttonIndex = Alert::title('تأكيد الحذف')
            ->buttons(['الغاء', 'نعم'])
            ->show('هل أنت متأكد من حذف هذا الرقم ؟');

        if ($buttonIndex === 1) {
            $this->deletePhone($id);
        }
    }

    public function deletePhone($id)
    {
        try {
            $user = Auth::user();
            $phone = $user->phoneNumbers()->where('id', $id)->first();
            if ($phone) {
                $phone->delete();
                unset($this->phoneNumbers[$id]);
                $this->setAlert('تم حذف الرقم بنجاح.', 'success');
            } else {
                $this->setAlert('الرقم غير موجود.', 'danger');
            }
        } catch (\Exception $e) {
            $this->setAlert('حدث خطأ أثناء حذف الرقم.', 'danger');
        }
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}