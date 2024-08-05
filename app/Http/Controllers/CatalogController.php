<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return Inertia::render('Catalogs/General-Directions/Index', [
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
        dd( "Edit", $id );
    }

    /**
     * Update the specified resource in storage.
     */
    public function generalDirectionsUpdate(Request $request, string $catalogId)
    {
        dd( "udpate the catalog");;
    }
    #endregion

}
