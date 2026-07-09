<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
         $venezuelaId = 1; // ID de Venezuela

    $states = \App\Models\State::where('country_id', $venezuelaId)
        ->orderBy('name')
        ->get()
        ->map(function($state) {
            $state->properties_count = \App\Models\Property::where('status', 'publicada')
                ->where('state_id', $state->id)
                ->count();
            return $state;
        });

    return view('home', compact('states'));
    }

    public function dashboard()
    {
        // Redirigir al dashboard específico según el rol
        return redirect()->route('dashboard');
    }
}
