<?php

namespace App\Http\Controllers;

use App\Http\Requests\Manager\CreateManagerRequest;
use App\Http\Requests\Manager\UpdateManagerRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManagerController extends Controller
{
    public function index()
    {
        $users = User::manager()->orderBy("id", "desc")->get();
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function store(CreateManagerRequest $request): JsonResponse
    {
        $password = Str::random(8);
        $user = new User($request->all());
        $user->role = "manager";
        $user->password = Hash::make($password);
        $user->save();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'password' => $password,
        ]);
    }

    public function update($managerId, UpdateManagerRequest $request): JsonResponse
    {
        $user = User::findOrFail($managerId);
        $user->fill($request->all());
        $user->save();
        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

    public function destroy($managerId): JsonResponse
    {
        $user = User::findOrFail($managerId);
        if ($user->role == "admin") {
            abort(400);
        }
        $user->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }
}
