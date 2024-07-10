<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Http\Requests\NewUserRequest;
use App\Models\{
    User,
    GeneralDirection,
    Direction,
    Subdirectorate,
    Department
};


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $generalDirections = GeneralDirection::select('id','name')->get();
        $directions = Direction::select('id','name')->get();
        $subdirectorates = Subdirectorate::select('id','name')->get();
        $departments = Department::select('id','name')->get();

        return Inertia::render('Admin/UserNew', [
            "generalDirections" => $generalDirections,
            "directions" => $directions,
            "subdirectorates" => $subdirectorates,
            "departments" => $departments
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewUserRequest $request)
    {
        try {
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'general_direction_id' => $request->generalDirection_id,
                'direction_id' => $request->direction_id,
                'subdirectorates_id' => $request->subdirectorate_id,
                'departments_id' => $request->departments_id,
                'level_id' => $request->level_id
            ]);
    
            Log::info("New user id:'{userid}' email:'{email}' created.", [
                "userid" => $newUser->id,
                "email" => $newUser->email
            ]);

            return redirect()->route('admin.index');

        } catch (\Throwable $th) {
            Log::error("Unhandle exception at store a new user: {message}", [
                "request" => $request->request->all(),
                "message" => $th->getMessage()
            ]);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
