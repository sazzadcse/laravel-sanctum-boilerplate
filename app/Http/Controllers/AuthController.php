<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {

    use HttpResponses;

    public function login (LoginUserRequest $request) {
        $request->validated( $request->all() );

        if ( !Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->error( '', 'Creadintial do not match.', 401 );
        } 

        $user = User::where( 'email', $request->email )->first();

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API token of '. $user->name )->plainTextToken
        ], 'You have successfully login.', 200);

    }

    public function register (StoreUserRequest $request) {

        $request->validated( $request->all() );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Toke of '. $user->name)->plainTextToken
        ], 'User Registration Successfully');
    }

    public function logout () {
        Auth::user()->currentAccessToken()->delete();

        return $this->success('', 'You have successgully logged out.', 200);
    }


}
