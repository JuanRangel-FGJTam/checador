<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Inertia;
use App\Http\Requests\{
    NewUserRequest,
    UpdateUserRequest
};
use App\Models\{
    User,
    GeneralDirection,
    Direction,
    Subdirectorate,
    Department,
    Menu
};
use App\Services\UserService;
use PhpParser\Node\Stmt\Catch_;

class UserController extends Controller
{
   
    private UserService $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // * get the users data
        $users = $this->userService->getUsers();

        // * return the view
        return Inertia::render('Admin/UserIndex', [
            "users" => array_values( is_array($users) ?$users :$users->toArray() )
        ]);
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
    public function edit(string $userid)
    {
        
        // * Get the user
        $user = array();
        try {
            $user = $this->userService->getUser($userid);
        } catch (ModelNotFoundException $mnf) {
            Log::error("User id '$userid' not found at UserController.edit", [ "message" => $mnf->getMessage() ]);
            return redirect()->back()->withErrors([
                "message" => "User not found"
            ])->withInput();
        }

        // * get the menus assigned to the user
        $user->load('menus');
        $selectedOptions = $user->menus->pluck('id');
        
        // * Get the catalogs
        $generalDirections = GeneralDirection::select('id','name')->get();
        $directions = Direction::select('id','name')->get();
        $subdirectorates = Subdirectorate::select('id','name')->get();
        $departments = Department::select('id','name')->get();
        $menuOptions = Menu::select('id','name', 'url')->get();

        // * Return the views
        return Inertia::render('Admin/UserEdit', [
            "title" => sprintf( "Editando usuario: %s", $user->name),
            "user" => $user,
            "selectedOptions" => $selectedOptions,
            "generalDirections" => $generalDirections,
            "directions" => $directions,
            "subdirectorates" => $subdirectorates,
            "departments" => $departments,
            "menuOptions" => $menuOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $userid)
    {

        // * retrive the user
        $user = null;
        try {
            $user = $this->userService->getUser($userid);
            $user->load("menus");
        } catch (ModelNotFoundException $th) {
            return redirect()->back()->withErrors([
                "message" => "User not found"
            ])->withInput();
        }

        try {

            // * update the general data
            $user->name = $request->name;
            $user->email = $request->email;
            $user->general_direction_id = $request->generalDirection_id;
            $user->direction_id = $request->direction_id;
            $user->subdirectorates_id = $request->subdirectorate_id;
            $user->departments_id = $request->departments_id;
            $user->level_id = $request->level_id;
            
            // * update the menu elements
            $user->menus()->sync($request->options);
                
            $user->update();

            // * redirect to user index
            return redirect()->route('admin.users.index');

        } catch (\Throwable $th) {
            Log::error( "Error at attempting to update the user '{userid}' at UserController.update: {message}", [
                "userid" => $userid,
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" => "Error no controlado; intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }
        
    }

    /**
     * Update the user password
     */
    public function updatePassword(Request $request, string $userid)
    {
        
        // * some validations
        $request->validate([
            "password" => 'required|string|min:8|max:24',
            "password_confirmation" => 'required|string|min:8|max:24|same:password',
        ]);


        // * retrive the user
        $user = null;
        try {
            $user = $this->userService->getUser($userid);
        } catch (ModelNotFoundException $th) {
            return redirect()->back()->withErrors([
                "message" => "User not found"
            ])->withInput();
        }

        try {

            // * update user password
            $user->password = $request->password;
            $user->update();

            // * redirect to user index
            return redirect()->route('admin.users.index');

        } catch (\Throwable $th) {
            Log::error( "Error at attempting to update the password of the user '{userid}' at UserController.updatePassword: {message}", [
                "userid" => $userid,
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" => "Error no controlado; intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
