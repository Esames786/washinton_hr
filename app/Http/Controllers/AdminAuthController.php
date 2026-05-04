<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{

    public function index()
    {
        return view('admin.index');
    }

    public function login(Request $request)
    {
        // Check if the user is already authenticated
        if (auth()->guard('admin')->check()) {
            return Redirect::route('admin.dashboard');
        }

        // If not authenticated, return the login view
        return view('admin.auth.login');
    }
    public function admin_login(Request $request){


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'errors' => $validator->errors()
            ]);
        }

//
//        if (Auth::guard('employee')->check()) {
//            Auth::guard('employee')->logout();
//        }
//

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $admin = Admin::where('email',$request->input('email'))->first();

        if(!empty($admin)){
            if($admin->status == 1) {
                if (Auth::guard('admin')->attempt($credentials)) {
                    // Authentication passed
                    return response()->json([
                        'status' => 1,
                        'message' => 'Login successful',
                    ]);
                } else {
                    // Authentication failed
                    return response()->json([
                        'status' => 0,
                        'errors' => ['password' => ['Invalid email or password']],
                    ]);
                }
            }else{
                return response()->json([
                    'status' => 0,
                    'errors' => ['password' => ['User Not Active']],
                ]);
            }
        }else{
            return response()->json([
                'status' => 0,
                'errors' => ['password' => ['User Not Found']],
            ]);
        }

    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        // Invalidate the session
        $request->session()->invalidate();
        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('admin.login'); // Default route
    }

    public function not_found() {
        return view('admin.not_found');
    }

}
