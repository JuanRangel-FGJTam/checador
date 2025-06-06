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

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        $title = "Usuarios Activos";
        $users = array();

        // * get the users data
        if($request->query->has('inactives')){
            $title = "Usuarios de baja";
            $users = User::onlyTrashed()->with(['generalDirection', 'direction', 'subdirectorate', 'department' ])->get();
        }else {
            $users = $this->userService->getUsers();
        }

        // * return the view
        return Inertia::render('Admin/UserIndex', [
            "title" => $title,
            "users" => array_values( is_array($users) ?$users :$users->toArray() )
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // * Get the catalogs
        $generalDirections = GeneralDirection::select('id','name')->get();
        $directions = [];
        $subdirectorates = [];
        $departments = [];

        if ($request->has('generalDirection_id')) {
            $directions = Direction::where('general_direction_id', $request->generalDirection_id)->select('id','name')->get();
        }
        else
        {
            $directions = Direction::where('id', 1)->select('id','name')->get();
        }

        if ($request->has('direction_id'))
        {
            $subdirectorates = Subdirectorate::where('direction_id', $request->direction_id)->select('id','name')->get();
        }
        else
        {
            $subdirectorates = Subdirectorate::where('id', 1)->select('id','name')->get();
        }

        if ($request->subdirectorate_id)
        {
            $departments = Department::where('subdirectorate_id', $request->subdirectorate_id)->select('id','name')->get();
        }
        else
        {
            $departments = Department::where('id', 1)->select('id','name')->get();
        }

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
                'subdirectorate_id' => $request->subdirectorate_id,
                'department_id' => $request->departments_id,
                'level_id' => $request->level_id ?? 0
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
    public function edit(Request $request, string $userid)
    {
        $user = array();
        $directions = [];
        $subdirectorates = [];
        $departments = [];

        try {
            $user = $this->userService->getUser($userid);
        } catch (ModelNotFoundException $mnf) {
            Log::error("User id '$userid' not found at UserController.edit", [ "message" => $mnf->getMessage() ]);
            return redirect()->back()->withErrors([
                "message" => "User not found"
            ])->withInput();
        }

        $user->load('menus');
        $selectedOptions = $user->menus->pluck('id');

        // * Get the catalogs
        $generalDirections = GeneralDirection::select('id','name')->get();

        if ($request->has('generalDirection_id')) {
            $directions = Direction::where('general_direction_id', $request->generalDirection_id)->select('id','name')->get();
        } else {
            if ($user->general_direction_id) {
                $directions = Direction::where('general_direction_id', $user->general_direction_id)->select('id','name')->get();
            }
        }

        if ($request->has('direction_id')) {
            $subdirectorates = Subdirectorate::where('direction_id', $request->direction_id)->select('id','name')->get();
        } else {
            if ($user->direction_id) {
                $subdirectorates = Subdirectorate::where('direction_id', $user->direction_id)->select('id','name')->get();
            }
        }

        if ($user->subdirectorate_id) {
            $departments = Department::where('subdirectorate_id', $user->subdirectorate_id)->select('id','name')->get();
        }

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
            $user->subdirectorate_id = $request->subdirectorate_id;
            $user->department_id = $request->departments_id;
            $user->level_id = $request->level_id;

            if ($request->has('password') && $request->password) {
                // * update the password
                $user->password = $request->password;
            }

            // * update the menu elements
            $user->menus()->sync($request->options);

            $user->update();

            // * redirect to user index
            Log::info("User id '$userid' updated");
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
            Log::info("Password of the user '$userid' updated");
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
    public function destroy(Request $request, string $id)
    {
         try {
            $user = $this->userService->getUser($id);
            $user->delete();

            Log::info("User id '$id' deleted");
            return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (ModelNotFoundException $mnf) {
            Log::error("User id '$id' not found at UserController.destroy", [ "message" => $mnf->getMessage() ]);
            return redirect()->back()->withErrors([
                "message" => "Usuario no encontrado"
            ])->withInput();
        } catch (\Throwable $th) {
            Log::error("Error at attempting to delete the user '{userid}' at UserController.destroy: {message}", [
                "userid" => $id,
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" => "Error no controlado; intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Request $request, string $id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
            Log::info("User id '$id' restored");
            return redirect()->route('admin.users.index')->with('success', 'Usuario restaurado correctamente.');
        } catch (ModelNotFoundException $mnf) {
            Log::error("User id '$id' not found at UserController.restore", [ "message" => $mnf->getMessage() ]);
            return redirect()->back()->withErrors([
                "message" => "Usuario no encontrado"
            ])->withInput();
        } catch (\Throwable $th) {
            Log::error("Error at attempting to restore the user '{userid}' at UserController.restore: {message}", [
                "userid" => $id,
                "message" => $th->getMessage(),
                "request" => $request->request->all()
            ]);
            return redirect()->back()->withErrors([
                "message" => "Error no controlado; intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }
        return redirect()->back()->withErrors([
            "message" => "Error no controlado; intente de nuevo o comuníquese con el administrador."
        ])->withInput();
    }
}
