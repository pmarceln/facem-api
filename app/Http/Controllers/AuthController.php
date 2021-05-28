<?php

namespace App\Http\Controllers;

use Validator;
use App\Entities\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }
    /**
     * Create a new token.
     *
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60*60 // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    public function authenticate(User $user, Request $request) {

        $this->validate($this->request, [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        $user = User::where('email', $this->request->input('email'))->first();
        if (!$user) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }

        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json(['token' => $this->jwt($user), 'user' => $user], Response::HTTP_OK);
        }

        return response()->json(null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Registration method
     *
     * @param Request $request registration request
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'password' => 'required',
    //         'first_name' => 'required',
    //         'last_name' => 'required',
    //         'email' => 'required|email|unique:users'
    //     ]);

    //     if ($validator->fails()) {
    //         return array(
    //             'success' => false,
    //             'message' => $validator->errors()->all()
    //         );
    //     }

    //     $user =  new User;
    //     $user->first_name = $request->first_name;
    //     $user->last_name = $request->last_name;
    //     $user->email = $request->email;
    //     $user->is_active = 0;
    //     $user->activation_code = uniqid();
    //     $user->password = app('hash')->make($request->password);
    //     $user->save();

    //     $to_email = $user->email;
    //     $activation_code = $user->activation_code;
    //     Mail::send([], [], function ($message) use ($to_email, $activation_code) {
    //         $message->to($to_email)
    //         ->subject('Contul dumneavoastra a fost creat.')
    //         ->setBody('<h1>Contul dumneavoastra a fost creat!</h1><h2>Pentru activare folositi urmatorul cod: <span style="font-size: 24px;">' . $activation_code . '</span></h2>', 'text/html');
    //     });

    //     return response()->json(['success' => true], Response::HTTP_OK);
    // }
}
