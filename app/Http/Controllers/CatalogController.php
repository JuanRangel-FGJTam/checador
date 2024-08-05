<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\{
    GeneralDirection,
    Direction,
    Subdirectorate
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

    #region Directions
    public function directionsIndex()
    { 

        // * retrive the data
        $data = Direction::with([
            'generalDirection' => fn($query) => $query->select('id', 'name', 'abbreviation')
        ])->select([
            'id',
            'name',
            'general_direction_id'
        ])->get()->toArray();

        
        // * return the view
        return Inertia::render('Catalogs/Directions/Index', [
            "data" => $data
        ]);
    }

    /**
     * Show the form for creating a new direction resource.
     */
    public function directionsCreate()
    {
        // * get general directions availables
        $generalDirections = $this->getGeneralDirections();

        // * return the view
        return Inertia::render('Catalogs/Directions/New', [
            "generalDirections" => $generalDirections
        ]);
    }

    /**
     * Store a newly created the direction in storage.
     */
    public function directionsStore(Request $request)
    {
        // * validate the request
        $request->validate([
            "name" => 'required|string|max:200',
            "general_direction_id" => 'required|numeric|exists:general_directions,id'
        ]);


        // * attempting to create the new resource
        try {

            $direction = Direction::create([
                'name' => $request->input('name'),
                'general_direction_id' => $request->input('general_direction_id')
            ]);

            // * redirect to index
            Log::info("New resource 'Direction' created with id '$direction->id' at CatalogController.directionsStore");
            return redirect()->route('admin.catalogs.directions.index' );

        } catch(ValidationException $ve){
            Log::error("Fail to create the new resource 'Direction' validations fails at CatalogController.directionsStore: {message}", [
                "message" => $ve->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors( $ve->errors() )->withInput();

        } catch(Throwable $th){
            Log::error("Fail to create the new resource 'Direction' at CatalogController.directionsStore: {message}", [
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" =>  "Error al registrar la dirección, intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }

    }

    /**
     * Show the form for editing the direction resource.
     */
    public function directionsEdit(string $directionId)
    {

        // * load the resource
        $direction = Direction::find($directionId);
        if($direction == null){
            Log::warning("Direction id '$directionId' not found");
            return redirect()->route('admin.catalogs.directions.index');
        }

        // * get general directions availables
        $generalDirections = $this->getGeneralDirections();

        // * return the view
        return Inertia::render('Catalogs/Directions/Edit', [
            "direction" => $direction,
            "generalDirections" => $generalDirections
        ]);

    }

    /**
     * Update the drirection resource in storage.
     */
    public function directionsUpdate(Request $request, string $directionId)
    {
        
        // * load the resource
        $direction = Direction::find($directionId);
        if( $direction == null){
            return redirect()->back()->withErrors([
                'message' => 'El recurso que se trata de actializar no existe o no esta disponible'
            ])->withInput();
        }
        
        // * validate the request
        $request->validate([
            "name" => 'required|string|max:200',
            "general_direction_id" => 'required|numeric|exists:general_directions,id'
        ]);


        // * update the model
        $direction->name = $request->input('name');
        $direction->general_direction_id = $request->input('general_direction_id');
        $direction->save();

        // * redirect to index
        Log::info("Resource 'Direction' id '$directionId' updated at CatalogController.directionsUpdate");
        return redirect()->route('admin.catalogs.directions.index' );
    }
    
    /**
     * get general directions availables
     *
     * @return Array<GeneralDirection>
     */
    private function getGeneralDirections(): Array{
        return GeneralDirection::select('id', 'name', 'abbreviation')->get()->toArray();
    }

    #endregion

    #region Sub-Direction
    public function subDirectionsIndex()
    { 
        // * retrive the data
        $data = Subdirectorate::with([
            'direction' => fn($query) => $query->select('id', 'name')
        ])->select([
            'id',
            'name',
            'direction_id'
        ])->get()->toArray();

        
        // * return the view
        return Inertia::render('Catalogs/SubDirections/Index', [
            "data" => $data
        ]);
    }

    /**
     * Show the form for creating a new direction resource.
     */
    public function subDirectionCreate()
    {
        // * get general directions availables
        $directions = $this->getDirectionsAvailables();

        // * return the view
        return Inertia::render('Catalogs/SubDirections/New', [
            "directions" => $directions
        ]);
    }

    /**
     * Store a newly created the direction in storage.
     */
    public function subDirectionStore(Request $request)
    {
        // * validate the request
        $request->validate([
            "name" => 'required|string|max:200',
            "direction_id" => 'required|numeric|exists:directions,id'
        ]);


        // * attempting to create the new resource
        try {

            $subdirectorate = Subdirectorate::create([
                'name' => $request->input('name'),
                'direction_id' => $request->input('direction_id')
            ]);

            // * redirect to index
            Log::info("New resource 'SubDirection' created with id '$subdirectorate->id' at CatalogController.subDirectionStore");
            return redirect()->route('admin.catalogs.sub-directions.index' );

        } catch(ValidationException $ve){
            Log::error("Fail to create the new resource 'SubDirection' validations fails at CatalogController.subDirectionStore: {message}", [
                "message" => $ve->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors( $ve->errors() )->withInput();

        } catch(Throwable $th){
            Log::error("Fail to create the new resource 'SubDirection' at CatalogController.subDirectionStore: {message}", [
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" =>  "Error al registrar la dirección, intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }

    }

    /**
     * Show the form for editing the direction resource.
     */
    public function subDirectionEdit(string $subDirectionId)
    {

        // * load the resource
        $subdirection = Subdirectorate::find($subDirectionId);
        if($subdirection == null){
            Log::warning("Sub direction id '$subDirectionId' not found");
            return redirect()->route('admin.catalogs.sub-directions.index');
        }

        // * get general directions availables
        $directions = $this->getDirectionsAvailables();

        // * return the view
        return Inertia::render('Catalogs/SubDirections/Edit', [
            "subdirection" => $subdirection,
            "directions" => $directions
        ]);

    }

    /**
     * Update the drirection resource in storage.
     */
    public function subDirectionUpdate(Request $request, string $subDirectionId)
    {
        
        // * load the resource
        $direction = Subdirectorate::find($subDirectionId);
        if( $direction == null){
            return redirect()->back()->withErrors([
                'message' => 'El recurso que se trata de actializar no existe o no esta disponible'
            ])->withInput();
        }
        
        // * validate the request
        $request->validate([
            "name" => 'required|string|max:200',
            "direction_id" => 'required|numeric|exists:directions,id'
        ]);


        // * update the model
        $direction->name = $request->input('name');
        $direction->direction_id = $request->input('direction_id');
        $direction->save();

        // * redirect to index
        Log::info("Resource 'Subdirectorate' id '$subDirectionId' updated at CatalogController.subDirectionUpdate");
        return redirect()->route('admin.catalogs.sub-directions.index' );
    }
    
    /**
     * get directions availables
     *
     * @return Array<Direction>
     */
    private function getDirectionsAvailables(): Array {
        return Direction::select('id', 'name')->get()->toArray();
    }

    #endregion

}
