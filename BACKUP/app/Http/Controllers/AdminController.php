<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\Accountant;

class AdminController extends Controller
{
    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
            'status'   => 'active'
        ];

        if (Auth::guard('admin')->attempt($credentials, $request->get('remember'))) {
            $admin = Auth::guard('admin')->user();

            Log::info('Admin login successful', [
                'email' => $admin->email,
                'name'  => $admin->name,
                'time'  => now()
            ]);

            return redirect()->route('admin.dashboard');
        } else {
            Log::warning('Admin login failed', [
                'email' => $request->email,
                'time'  => now()
            ]);

            session()->flash('error', 'Either Email/Password is incorrect');
            return back()->withInput($request->only('email'));
        }
    }



    public function signOut()
    {
        // Capture admin info before logout
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            Log::info('Admin logged out', [
                'email' => $admin->email,
                'name'  => $admin->name,
                'role'  => $admin->role, // assuming 'role' is a column like 'admin' or 'superadmin'
                'time'  => now()
            ]);
        }

        Auth::guard('admin')->logout(); // Logs out the admin user
        session()->flash('success', 'You have been logged out successfully.');
        
        return redirect()->route('admin.login'); // Redirect to named route
    }

    public function dashboard()
    {
        return view('admin.dashboard');
      
    }


    public function organizations()
    {
        return view('admin.organizations');
      
    }

    
    
}
