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


    public function managePrices(){
        return view('users.managePrices');
    }

    public function manageGenerators(){
        return view('users.manageGenerators');
    }

    public function ClientsIndex(){
        return view('users.ClientsIndex');
    }

    public function trashedClientsIndex(){
        return view('users.TrashedClientsIndex');
    }

    public function meterReadings(){
        return view('users.meter-readings');
    }

    public function paymentEntry(){
        return view('users.paymentEntry');
    }

    public function paymentHistory($clientId)
    {
        return view('users.payment-history', compact('clientId'));
    }

    public function maintenanceEntry(){
        return view('users.maintenanceEntry');
    }

    public function maintenanceList($clientId){
        return view('users.maintenanceList', compact('clientId'));
    }
}
