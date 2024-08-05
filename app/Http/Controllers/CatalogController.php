<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\{
    GeneralDirection
};

class CatalogController extends Controller
{

    #region general directions
    /**
     * Display a listing of the resource.
     */
    public function generalDirectionsIndex()
    {

        $data = GeneralDirection::select([
            'id',
            'name',
            'abbreviation'
        ])->get()->toArray();

        return Inertia::render('Catalogs/GeneralDirections/Index', [
            "data" => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generalDirectionsCreate()
    {
        dd( "create new catalog");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function generalDirectionsStore(Request $catalogId)
    {
        dd( "store the catalog");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function generalDirectionsEdit(string $id)
    {

        // * load the resource
        $generalDirection = GeneralDirection::find($id);
        if( $generalDirection == null){
            Log::warning("General direction id '$id' not found");
            return redirect()->route('admin.catalogs.general-directions.index');
        }

        // * return the view
        return Inertia::render('Catalogs/GeneralDirections/Edit', [
            "generalDirection" => $generalDirection
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function generalDirectionsUpdate(Request $request, string $catalogId)
    {

        // * load the resource
        $generalDirection = GeneralDirection::find($catalogId);
        if( $generalDirection == null){
            return redirect()->back()->withErrors([
                'message' => 'El recurso que se trata de actializar no existe o no esta disponible'
            ])->withInput();
        }
        
        // * validate the request
        $request->validate([
            "name" => 'required|string|max:200',
            "abbreviation" => 'required|string|max:50'
        ]);


        // * update the model
        $generalDirection->name = $request->input('name');
        $generalDirection->abbreviation = $request->input('abbreviation');
        $generalDirection->save();

        // * redirect to index
        Log::info("Resource 'GeneralDirection' id '$catalogId' updated at CatalogController.generalDirectionsUpdate");
        return redirect()->route('admin.catalogs.general-directions.index' );
    }
    #endregion

}
