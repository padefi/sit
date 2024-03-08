<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): Response {
        return Inertia::render('Admin/Users/UserIndex', [
            'users' => UserResource::collection(User::all()),
            'roles' => RoleResource::collection(Role::all()),
            'permissions' => PermissionResource::collection(Permission::all()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request) {
        $userEmail = User::where('email', $request->email)->first();

        if ($userEmail) {
            return Redirect::back()->with([
                'message' => [
                    'type' => 'error',
                    'message' => 'El email ya se encuentra registrado.'
                ],
                'success' => false,
            ]);
        }

        User::create([
            'surname' => $request->surname,
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => ($request->is_active) ? 1 : 0,
            'username' => User::generateUsername($request->name, $request->surname),
            'password' => Hash::make(User::generateUsername($request->name, $request->surname)),
        ])->assignRole($request->role);

        return Redirect::back()->with([
            'message' => [
                'type' => 'success',
                'message' => 'Usuario creado exitosamente.'
            ],
            'success' => true,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user) {
        $userEmail = User::where('email', $request->email)->whereNot('id', $user->id)->first();

        if ($userEmail) {
            return Redirect::back()->with([
                'message' => [
                    'type' => 'error',
                    'message' => 'El email ya se encuentra registrado.'
                ],
                'success' => false,
            ]);
        }

        $user->update([
            'surname' => $request->surname,
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => ($request->is_active) ? 1 : 0,
        ]);

        $user->syncRoles($request->role);

        return Redirect::back()->with([
            'message' => [
                'type' => 'success',
                'message' => 'Usuario modificado exitosamente.'
            ],
            'success' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }
}
