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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): Response {
        return Inertia::render('Users/UserIndex', [
            'users' => UserResource::collection(User::all()),
            'roles' => RoleResource::collection(Role::all()),
            'permissions' => PermissionResource::collection(Permission::all()),
        ]);
    }

    public function store(UserRequest $request) {
        $userEmail = User::where('email', $request->email)->first();

        if ($userEmail) {
            throw ValidationException::withMessages([
                'message' => trans('El email ya se encuentra registrado.')
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
            'info' => [
                'type' => 'success',
                'message' => 'Usuario agregado exitosamente.'
            ],
            'success' => true,
        ]);
    }

    public function update(UserRequest $request, User $user) {
        $userEmail = User::where('email', $request->email)->whereNot('id', $user->id)->first();

        if ($userEmail) {
            throw ValidationException::withMessages([
                'message' => trans('El email ya se encuentra registrado.')
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
            'info' => [
                'type' => 'success',
                'message' => 'Usuario modificado exitosamente.'
            ],
            'success' => true,
        ]);
    }
}
