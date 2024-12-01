<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Contracts\Service\Attribute\Required;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'users' => User::all()->load('customer'),
        ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'level' => 'required',
            // 'block' => 'required'
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'level' => $request->level,
            'block' => $request->block
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $updated = $user->update([
            // 'id_customer' => $request->id_customer,
            // 'level' => $request->level
            // 'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => $updated,
            'user' => $user,
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

}
