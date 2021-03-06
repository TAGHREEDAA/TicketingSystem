<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;

class ConcertsController extends Controller
{
    public function index()
    {
        return view('concerts.index')->with('concerts',Concert::all());
    }

    public function show(Concert $concert)
    {
        $concert = Concert::published()->findOrFail($concert->id);

        return view('concerts.show')->with('concert', $concert);
    }
}
