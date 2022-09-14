<?php

namespace App\Http\Controllers;

use App\Models\Tokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function admin_area()
    {
        $token_data = Tokens::where('token_type', 'Bearer')->first();
        $api_authorized = !empty($token_data->access_token) && !empty($token_data->refresh_token) ? true : false;
        $tenant_id = config('msgraph.tenant_id');
        $client_id = config('msgraph.app_id');
        $redirect = url('msgraph-api');
        $state = rand(1000, 9999);
        $endpoint = 'https://login.microsoftonline.com/' . $tenant_id . '/oauth2/v2.0/authorize?client_id=' . $client_id . '&response_type=code&redirect_uri=' . $redirect . '&response_mode=query&scope=offline_access ChannelMessage.Send&state=' . $state;

        return view('admin')->with(['endpoint' => $endpoint, 'authorized' => $api_authorized]);
    }

    public function login()
    {
        return redirect('/')->with('admin_message', 'Please login!');
    }

    public function login_process(Request $request)
    {
        // This is a passcode only simple login so a dummy email has been used
        $request->validate([
            'password' => 'required',
        ]);
        $request->request->add(['email' => 'admin@msgraph-api-app.com']);
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('/')->with('admin_message', 'Signed in successfully!');
        }

        return redirect('/')->with('admin_message', 'Passcode is not valid!');
    }

    public function logOut()
    {
        Session::flush();
        Auth::logout();

        return redirect('/')->with('admin_message', 'You are now logged out!');
    }
}
