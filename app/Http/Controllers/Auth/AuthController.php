<?php

namespace App\Http\Controllers\Auth;

use App\DTO\EmployeeDTO;
use App\Models\AuditLog\AuditLog;
use App\Models\Employee\Employee;
use App\Models\User;
use App\Utils\UtilService;
use DateTime;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'username' => 'required|unique:users',
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'username' => $request->username,
            'block' => 'NO'
        ]);

        //return response JSON user is created
        if ($user) {
            return response()->json([
                'success' => true,
                'user' => $user,
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required'
            ]);

            
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $credentials = $request->only('email', 'password');

            $token = Auth::guard('api')->attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wrong email or password !',
                    'credentials' => $credentials
                ], 401);
            }

            $user = Auth::guard('api')->user();
            $payload = Auth::guard('api')->payload();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'exp' => $payload['exp']
                ]
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
            ], 500);
        }

    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ], 200);
    }


    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::guard('api')->user(),
            'authorisation' => [
                'token' => Auth::guard('api')->refresh(),
                'type' => 'Bearer',
            ]
        ], 200);
    }

    public function me()
    {
        DB::beginTransaction();
        try {
            $user = Auth::guard('api')->user();
            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            echo $th;
            DB::rollBack();
            return response()->json([
                'message' => 'Unauthorized',
                'error' => $th,
            ], 401);
        }
    }

    public function changePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'old_pasword' => 'required',
            'new_pasword' => 'required|min:8',
            'confirm_new_password' => 'required|min:8'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {

            $user = Auth::guard('api')->user();
            $check_user = DB::table('users')
                ->select('password', 'email')
                ->where('id', $id)
                ->first();

            if (!$check_user) {
                return response()->json([
                    'message' => 'User not found !'
                ], 400);
            }


            if (!Hash::check($request->old_pasword, $check_user->password)) {
                return response()->json([
                    'message' => 'Password not match !'
                ], 400);
            }

            User::where('id', $id)
                ->update([
                    'password' => Hash::make($request->new_pasword)
                ]);

            DB::commit();

            return response()->json([
                'status' => 'Change password successfully !',
            ]);
        } catch (\Throwable $th) {
            echo $th;
            DB::rollBack();
            return response()->json([
                'message' => 'Error change password',
                'error' => $th,
            ], 401);
        }
    }


    public function resetPassword($id)
    {
        try {
            $check_user = User::find($id);

            if (!$check_user) {
                return response()->json([
                    'message' => 'User not found !'
                ], 400);
            }


            User::where('id', $id)
                ->update([
                    'password' => Hash::make("password123")
                ]);

            return response()->json([
                'status' => 'Reset password successfully !',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error reset password',
                'error' => $th,
            ], 401);
        }
    }

}

