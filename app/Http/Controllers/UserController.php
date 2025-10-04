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

    public function activeClientsIndex(){
        return view('users.ActiveClientsIndex');
    }

    public function trashedClientsIndex(){
        return view('users.TrashedClientsIndex');
    }

    public function meterReadings(){
        return view('users.meter-readings');
    }

}
