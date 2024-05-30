<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PermissionMiddleware;
use App\Http\Requests\UserRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function __construct() {
        $this->middleware('check.permission:view users')->only('index');
        $this->middleware('check.permission:create users')->only('store');
        $this->middleware('check.permission:edit users')->only('update');
        $this->middleware('check.permission:permission users')->only('updatePermission');
    }
    
    public function index(): Response {
        $userPermissions = User::with('permissions')->get();
        $roles = Role::with('permissions')->get();

        return Inertia::render('Users/UserIndex', [
            'users' => UserResource::collection($userPermissions),
            'roles' => RoleResource::collection($roles),
        ]);
    }

    public function store(UserRequest $request) {
        $userEmail = User::where('email', $request->email)->first();

        if ($userEmail) {
            throw ValidationException::withMessages([
                'message' => trans('El email ya se encuentra registrado.')
            ]);
        }

        $user = User::create([
            'surname' => $request->surname,
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => ($request->is_active) ? 1 : 0,
            'username' => User::generateUsername($request->name, $request->surname),
            'password' => Hash::make(User::generateUsername($request->name, $request->surname)),
        ])->assignRole($request->role);

        User::assignDefaultPermissions($user, $request->role);

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Usuario agregado exitosamente.',
                'user' => $user,
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

        $userRole = User::with('roles')->find($user->id);
        $user->syncRoles($request->role);

        if ($userRole->roles->first()->name !== $request->role) {
            User::assignDefaultPermissions($user, $request->role);
        }

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Usuario modificado exitosamente.'
            ],
            'success' => true,
        ]);
    }

    public function updatePermission(Request $request, User $user) {
        $permission = Permission::findById($request->permission);

        if ($user->hasDirectPermission($permission->name)) {
            $user->revokePermissionTo($permission->name);
        } else {
            $user->givePermissionTo($permission->name);
        }

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Permisos actualizados exitosamente.'
            ],
            'success' => true,
        ]);
    }
}
