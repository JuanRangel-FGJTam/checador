<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\{
    TypeJustify,
    GeneralDirection
};

class HollidaysController extends Controller
{

    
    public function create(){

        // * get catalogs
        $justificationsType = TypeJustify::select('id', 'name')->get()->all();
        $generalDirections = GeneralDirection::select('id', 'name')->get()->all();

        return Inertia::render('Hollidays/Create', [
            "justificationsType" => $justificationsType,
            "generalDirections" => $generalDirections,
            "breadcrumbs" => null
        ]);
    }

    public function store(Request $request){

        $request->validate([
            "initialDay" => "required|date",
            "endDay" => "required|date|after:initialDay",
            "type_id" => "required|integer|exists:type_justifies,id",
            "general_direction" =>"required|integer|exists:general_directions,id",
            "file" => "required|file|mimes:pdf|max:10240"
        ],[],[
            "initialDay" => "fecha inicial",
            "endDay" => "fecha final",
            "type_id" => "tipo de justificacion",
            "general_direction" =>"direccion general",
            "file" => "oficio"
        ]);

        dd("you sall no pass", $request);
    }

}
