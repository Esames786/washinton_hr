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
    public function admin_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 0, 'errors' => $validator->errors()]);
            }
            return back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $admin = Admin::where('email', $request->input('email'))->first();

        if (!$admin) {
            $error = ['password' => ['User Not Found']];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 0, 'errors' => $error]);
            }
            return back()->withErrors($error)->withInput();
        }

        if ($admin->status != 1) {
            $error = ['password' => ['User Not Active']];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 0, 'errors' => $error]);
            }
            return back()->withErrors($error)->withInput();
        }

        if (Auth::guard('admin')->attempt($credentials)) {
            // Explicitly save session before redirect
            $request->session()->save();

            \Illuminate\Support\Facades\Log::info('[AdminLogin] Success', [
                'email'      => $request->input('email'),
                'session_id' => session()->getId(),
            ]);
            return redirect()->route('admin.dashboard');
        }

        $error = ['password' => ['Invalid email or password']];
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 0, 'errors' => $error]);
        }
        return back()->withErrors($error)->withInput();
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
