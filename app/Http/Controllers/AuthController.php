<?php

namespace App\Http\Controllers;

use App\Models\Users\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class AuthController extends Controller
{

    public function checkUser($email)
    {
        $user = User::where('email', '=', $email)->first();
        //dd($user);
        return response()->json($user);
    }
    public function createUser()
    {
        $form = request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required'
        ]);

        User::create([
            'name' => $form['name'],
            'email' => $form['email'],
            'password' => Hash::make($form['password']),

        ]);

        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',


        ], 200);
    }

    public function loginUser(Request $request)
    {
        try {
            DB::beginTransaction();
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'

                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'error' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            $res = [
                'status' => true,
                'message' => 'User login Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'role' => $user->getRoles(),
                'username' => $user->name,
            ];
            if (isset($user->email)) {
                DB::table('password_resets')->where('email', $user->email)->delete();
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $res['token'],
                    'created_at' => Carbon::now()
                ]);
            }
            DB::commit();
            return response()->json($res, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function changePassword()
    {

        $form = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
            'token' => 'required',
        ]);


        $token = request()->token;
        $reset = DB::table('password_resets')
            ->where('email', '=', $form['email'])
            ->where('token', '=', $token)->get();


        if ($reset->isEmpty()) {
            abort(400, __('auth.reset_failed'));
        }


        // Reset Password
        $user = User::where('email', '=', $form['email'])->first();
        $user->password = Hash::make($form['password']);
        $user->save();

        // Delete Reset Token
        DB::table('password_resets')->where('token', $token)->delete();

        return [
            'message' => __('auth.reset_success')
        ];
    }
}
