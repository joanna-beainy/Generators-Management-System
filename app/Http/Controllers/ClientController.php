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
            'phone' => ['required', 'regex:/^(\+?\d{1,4})?\d{8,15}$/'],
            'meter_category_id' => ['required', 'exists:meter_categories,id'],
            'generator_id' => ['required', 'exists:generators,id'],
            'previousMeter' => ['required', 'integer', 'min:0']
        ]);

        foreach (['firstName', 'fatherName', 'lastName', 'address', 'meter_category_id', 'generator_id'] as $field) {
            $fields[$field] = strip_tags(trim($fields[$field]));
        }

        $fields['user_id'] = Auth::id();
        $previousMeter = $fields['previousMeter'];
        unset($fields['previousMeter']);

        DB::beginTransaction();

        try {
            $client = Client::create($fields);

            if (!$client) {
                return redirect()->back()->withErrors(['client' => 'خطأ في إنشاء المشترك']);
            }

            $this->createInitialMeterReading($client->id, $previousMeter);

            DB::commit();
            return redirect(route('active.clients.index'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['client' => 'حدث خطأ أثناء الحفظ']);
        }
    }

    private function createInitialMeterReading($clientId, $previousMeter)
    {
        MeterReading::create([
            'client_id' => $clientId,
            'previousMeter' => $previousMeter,
            'currentMeter' => 0,
            'amount' => 0,
            'remaining_amount' => 0,
            'reading_date' => null,
            'reading_for_month' => Carbon::now()->startOfMonth(),
            'status' => 'unpaid'
        ]);
    }

    }

