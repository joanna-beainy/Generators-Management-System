<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Generator;
use App\Models\MeterCategory;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'first_name' => ['required', 'min:3', 'max:255'],
            'father_name' => ['required', 'min:3', 'max:255'],
            'last_name' => ['required', 'min:3', 'max:255'],
            'address' => ['required', 'min:3'],
            'phone_number' => ['required', 'regex:/^(\+?\d{1,4})?\d{8,15}$/'],
            'meter_category_id' => ['required', 'exists:meter_categories,id'],
            'generator_id' => ['required', 'exists:generators,id'],
            'previous_meter' => ['required', 'integer', 'min:0']
        ]);

        foreach (['first_name', 'father_name', 'last_name', 'address', 'meter_category_id', 'generator_id'] as $field) {
            $fields[$field] = strip_tags(trim($fields[$field]));
        }

        $fields['user_id'] = Auth::id();
        $previous_meter = $fields['previous_meter'];
        unset($fields['previous_meter']);

        DB::beginTransaction();

        try {
            $client = Client::create($fields);

            if (!$client) {
                return redirect()->back()->withErrors(['client' => 'خطأ في إنشاء المشترك']);
            }

            $this->createInitialMeterReading($client->id, $previous_meter);

            DB::commit();
            return redirect(route('active.clients.index'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['client' => 'حدث خطأ أثناء الحفظ']);
        }
    }

    private function createInitialMeterReading($clientId, $previous_meter)
    {
        MeterReading::create([
            'client_id' => $clientId,
            'previous_meter' => $previous_meter,
            'current_meter' => 0,
            'amount' => 0,
            'remaining_amount' => 0,
            'maintenance_cost' => 0,
            'reading_date' => null,
            'reading_for_month' => Carbon::now()->startOfMonth(),
            'status' => 'unpaid'
        ]);
    }

    }

