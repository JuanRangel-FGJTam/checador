<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\{
    GeneralDirection
};
use Exception;
use Throwable;

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
        return Inertia::render('Catalogs/GeneralDirections/New');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function generalDirectionsStore(Request $request)
    {
        // * validate the request
        $request->validate([
            "name" => 'required|string|max:200',
            "abbreviation" => 'required|string|max:50'
        ]);


        // * create the new resource
        try {
            $generalDirection = GeneralDirection::create([
                'name' => $request->input('name'),
                'abbreviation' => $request->input('abbreviation')
            ]);

            // * redirect to index
            Log::info("New resource 'GeneralDirection' created with id '$generalDirection->id' at CatalogController.generalDirectionsStore");
            return redirect()->route('admin.catalogs.general-directions.index' );

        } catch(ValidationException $ve){
            Log::error("Fail to create the new resource 'GeneralDirection' validations fails at CatalogController.generalDirectionsStore: {message}", [
                "message" => $ve->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors( $ve->errors() )->withInput();

        } catch(Throwable $th){
            Log::error("Fail to create the new resource 'GeneralDirection' at CatalogController.generalDirectionsStore: {message}", [
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" =>  "Error al registrar la dirección general, intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }

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
