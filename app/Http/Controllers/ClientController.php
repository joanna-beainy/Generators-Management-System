<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Generator;
use App\Models\MeterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function create()
    {
        $generators = Generator::where('user_id', Auth::id())->get();
        $categories = MeterCategory::where('user_id', Auth::id())->get();

        return view('clients.create', compact('generators', 'categories'));
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'first_name' => ['required', 'min:2', 'max:255'],
            'father_name' => ['nullable', 'min:2', 'max:255'],
            'last_name' => ['nullable', 'min:2', 'max:255'],
            'address' => ['required', 'min:3'],
            'phone_number' => ['nullable', 'regex:/^(\+?\d{1,4})?\d{8,15}$/'],
            'generator_id' => ['required', 'exists:generators,id'],
            'meter_category_id' => ['nullable', 'exists:meter_categories,id'],
            'initial_meter' => ['nullable', 'integer', 'min:0'],
            'is_offered' => ['nullable', 'boolean'],
        ]);

        foreach (['first_name', 'father_name', 'last_name', 'address'] as $field) {
            if (!empty($fields[$field])) {
                $fields[$field] = strip_tags(trim($fields[$field]));
            }
        }

        $fields['user_id'] = Auth::id();
        $fields['is_offered'] = $request->boolean('is_offered', false);
        $fields['initial_meter'] = $fields['initial_meter'] ?? 0;

        // If client is offered, remove meter_category_id
        if ($fields['is_offered']) {
            $fields['meter_category_id'] = null;
        }

        $client = Client::create($fields);

        if (!$client) {
            return redirect()->back()->withErrors(['client' => 'حدث خطأ أثناء إنشاء المشترك.']);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'تمت إضافة المشترك بنجاح.');
    }
}
