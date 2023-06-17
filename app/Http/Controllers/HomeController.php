<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
       
        return view('home');
    }

    public function adminLogin(){
        $admin = User::where('role_id', '1')->first();
        Auth::login($admin);
        return redirect()->route('dashboard.home');
    }

    public function merchantLogin(User $user, Request $request){
        Auth::login($user);
        $from_admin = true;
        $request->session()->put('admin', $from_admin);
        return redirect()->route('dashboard.home');
    }
}
