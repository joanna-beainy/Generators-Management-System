<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function showDashboard(){
        return view('users.dashboard');
    }

    public function userProfile(){
        return view('users.UserProfile');
    }

    public function managePrices(){
        return view('users.managePrices');
    }

    public function manageGenerators(){
        return view('users.manageGenerators');
    }

    public function ClientsIndex(){
        return view('users.ShowClients');
    }

    public function createClient(){
        return view('users.CreateClient');
    }

    public function trashedClientsIndex(){
        return view('users.TrashedClientsIndex');
    }

    public function meterReadings(){
        return view('users.meter-readings');
    }

    public function clientMeterReadings($clientId){
        return view('users.ClientMeterReadings', compact('clientId'));
    }

    public function paymentEntry(){
        return view('users.paymentEntry');
    }

    public function paymentHistory($clientId)
    {
        return view('users.payment-history', compact('clientId'));
    }

    public function monthlyPaymentReport(){
        return view('users.MonthlyPaymentReport');
    }

    public function maintenanceEntry(){
        return view('users.maintenanceEntry');
    }

    public function maintenanceList($clientId){
        return view('users.maintenanceList', compact('clientId'));
    }

    public function monthlyClientReport(){
        return view('users.MonthlyMeterReadingsReport');
    } 
    
    public function meterReadingFormReport(){
        return view('users.MeterReadingFormReport');
    }

    public function outstandingAmountsReport(){
        return view('users.OutstandingAmountsReport');
    }

    public function fuelPurchaseReport(){
        return view('users.FuelPurchasesReport');
    }

    public function fuelConsumptionReport(){
        return view('users.FuelConsumptionsReport');
    }
}
