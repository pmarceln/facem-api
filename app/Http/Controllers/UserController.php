<?php

namespace App\Http\Controllers;

use Validator;
use App\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController
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
     * Activate user
     *
     * @param  Integer $activation_code
     * @return mixed
     */
    public function activate(Request $request) {
        if ($request->has('activation_code')) {
            $activation_code = $request->input('activation_code');
            if (!isset($activation_code)) {
                return response()->json(null, Response::HTTP_BAD_REQUEST);
            }

            if ($request->auth->activation_code === $activation_code) {

                $user = User::find($request->auth->id);
                if ($user) {
                    $user->activation_code = null;
                    $user->is_active = 1;
                    $user->save();

                    return response()->json(['success' => true], Response::HTTP_OK);
                }

                return response()->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return response()->json(['success' => false], Response::HTTP_OK);
        }

        return response()->json(null, Response::HTTP_BAD_REQUEST);
    }
}
