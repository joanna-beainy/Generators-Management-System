<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)->with('phoneNumbers')->get();
        return view('admin.dashboard', compact('users'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request){
        $fields = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'password' => ['required', 'min:6', 'max:32'],
            'phone_numbers' => ['required', 'array'], // Validate that phone_numbers is an array
            'phone_numbers.*' => ['required', 'regex:/^(\+?\d{1,4})?\d{8,15}$/'], // Validate each phone number
        ]);
    
        $fields['name'] = strip_tags(trim($fields['name']));
        $fields['password'] = bcrypt(trim($fields['password']));
    
        // Start the database transaction
        DB::beginTransaction();
    
        try {
            $user = User::create([
                'name' => $fields['name'],
                'password' => $fields['password'],
            ]);
    
            // If phone numbers are provided, create them for the user
            if (isset($fields['phone_numbers'])) {
                foreach ($fields['phone_numbers'] as $phoneNumber) {
                    // Create the phone number with the user_id automatically
                    $user->phoneNumbers()->create(['phone_number' => $phoneNumber]);
                }
            }
    
            // Commit the transaction if everything is successful
            DB::commit();
            return redirect()->route('admin.dashboard')->with('success', 'تم إنشاء المستخدم بنجاح.');
    
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            return back()->withErrors(['registration' => 'حدث خطأ أثناء التسجيل. حاول مرة أخرى.']);
        }
    }

    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return back()->withErrors(['error' => 'لا يمكن حذف مدير النظام.']);
        }

        $user->delete();

        return redirect()->route('admin.dashboard')->with('success', 'تم حذف المستخدم بنجاح.');
    }

}
