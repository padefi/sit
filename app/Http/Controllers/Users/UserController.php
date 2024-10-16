<?php

namespace App\Http\Controllers\Users;

use App\Events\Users\UserEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserRequest;
use App\Http\Resources\Users\RoleResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Users\User;
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
        $this->middleware('check.permission:edit users')->only(['update', 'resetPassword']);
        $this->middleware('check.permission:permission users')->only('updatePermission');
    }

    public function index(): Response {
        $users = User::with('permissions')->get();
        $roles = Role::with('permissions')->get();
        $userRole = Auth::user()->roles->first();

        if ($userRole->name !== 'admin') {
            $users = $users->filter(function ($user) {
                return !$user->hasRole('admin');
            });
        }

        return Inertia::render('Users/UserIndex', [
            'users' => UserResource::collection($users),
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

        $user->load('roles');
        $tempUUID = $request->keys()[5];
        event(new UserEvent($user, $tempUUID, 'create'));

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

        $user->load('roles');
        event(new UserEvent($user, $request->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Usuario modificado exitosamente.'
            ],
            'success' => true,
        ]);
    }

    public function resetPassword(User $user) {
        $user->password = Hash::make($user->username);
        $user->save();

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Contraseña restablecida exitosamente.'
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
