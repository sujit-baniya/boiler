<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        //dd(User::find(1)->billingAddresses);
        //dd(User::findOutside(5, 'kilometers', '45.4756120', '131.5901990')->paginate(12));
        return response()->json(country('np')->getAttributes());
    }
}
