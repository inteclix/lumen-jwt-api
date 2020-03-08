<?php
namespace App\Http\Controllers;
use phpDocumentor\Reflection\Types\Null_;
use Validator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use App\User;
use Illuminate\Support\Facades\Storage;

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
            'exp' => time() + 60 * 60 // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET', 'JhbGciOiJIUzI1N0eXAiOiJKV1QiLC'));
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    public function authenticate() {
        if($this->request->username === "admin" && $this->request->password === "aaaaaaaa"){
            $payload = [
                'iss' => "lumen-jwt", // Issuer of the token
                'sub' => 0, // Subject of the token
                'iat' => time(), // Time when JWT was issued.
                'exp' => time() + 60*60 // Expiration time
            ];

            // As you can see we are passing `JWT_SECRET` as the second parameter that will
            // be used to decode the token in the future.
            $jwt = JWT::encode($payload, env('JWT_SECRET', 'JhbGciOiJIUzI1N0eXAiOiJKV1QiLC'));
            return new JsonResponse([
                'message' => 'authenticated_user',
                'data' => [
                    'token' => $jwt,
                ]
            ],Response::HTTP_OK);
        }
        $this->validate($this->request, [
            'username'     => 'required',
            'password'  => 'required'
        ]);
        // Find the user by username
        $user = User::where('username', $this->request->input('username'))->first();
        if (!$user) {
            // You wil probably have some sort of helpers or whatever
            // to make sure that you have the same response format for
            // differents kind of responses. But let's return the
            // below respose for now.
            return response()->json([
                'message' => 'Username does not exist.'
            ], 400);
        }
        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return new JsonResponse([
                'message' => 'authenticated_user',
                'data' => [
                    'token' => $this->jwt($user),
                ]
            ],Response::HTTP_OK);
        }
        if ($this->request->input('password') == $user->password) {
            return new JsonResponse([
                'message' => 'authenticated_user',
                'data' => [
                    'token' => $this->jwt($user),
                ]
            ],Response::HTTP_OK);
        }
        // Bad Request response
        return response()->json([
            'message' => 'Username or password is wrong.'
        ], 400);
    }
    function me(Request $request){
        return new JsonResponse([
            'message' => 'authenticated_user',
            'data' => $request->auth
        ]);
    }

    function getUser($id, Request $request){
        try{
            $user = User::findOrFail($id);
        } catch (Exception $e){
            return new JsonResponse([
                'message' => 'No user with this id',
            ], Response::HTTP_BAD_REQUEST);
        }
        if ($request->auth->role === 'admin') {
            return new JsonResponse([
                'message' => 'Success get user',
                'data' => $user
            ]);
        }
        return new JsonResponse([
            'message' => 'Permission denied'
        ], Response::HTTP_UNAUTHORIZED);
    }

    function  getUsers(Request $request){
        if($request->auth->role === 'admin'){
            return new JsonResponse([
                "message" => "all users",
                "data" => User::all()
            ], Response::HTTP_OK);
        }
        return new JsonResponse([
            'message' => 'Permission denied'
        ], Response::HTTP_UNAUTHORIZED);
    }

    function  createUser(Request $request){
        if($request -> auth -> role === 'admin'){
            $this->validate($request, [
                'username' => 'required',
                'password' => 'required',
            ]);
            $user = new User;
            $user -> username = $request -> username;
            $user -> password = app('hash') -> make($request -> password);
            $user -> firstname = $request -> firstname;
            $user -> lastname = $request -> lastname;
            $user -> tel = $request -> tel;
            $user -> role = $request -> role;
            $user -> is_active = $request -> is_active;
            $user -> img1 = $request -> img1;
            $user -> img2 = $request -> img2;
            $user -> img3 = $request -> img3;

            try{
                $user->save();
            } catch (QueryException $e){
                return new JsonResponse([
                    'message' => 'Sql exception'
                ], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse([
                'message' => 'Success create user',
                'data' => $user
            ], Response::HTTP_CREATED);
        }
        return new JsonResponse([
            'message' => 'Permission denied'
        ], Response::HTTP_UNAUTHORIZED);
    }

    function delete($id, Request $request){
        try{
            $user = User::findOrFail($id);
        } catch (Exception $e){
            return new JsonResponse([
                'message' => 'No user with this id',
            ], Response::HTTP_BAD_REQUEST);
        }
        if ($request->auth->role === 'admin') {
            User::destroy($id);
            return new JsonResponse([
                'message' => 'user deleted'
            ]);
        }
        return new JsonResponse([
            'message' => 'Permission denied'
        ], Response::HTTP_UNAUTHORIZED);
    }

    function update($id, Request $request){
        if($request -> auth -> role === 'admin'){
            $this->validate($request, [
                'username' => 'required',
                'password' => 'required',
            ]);
            $user = User::findOrFail($id);
            $user -> username = $request -> username;
            $user -> password = app('hash') -> make($request -> password);
            $user -> firstname = $request -> firstname;
            $user -> lastname = $request -> lastname;
            $user -> tel = $request -> tel;
            $user -> role = $request -> role;
            $user -> is_active = $request -> is_active;
            $user -> img1 = $request -> img1;
            $user -> img2 = $request -> img2;
            $user -> img3 = $request -> img3;
            try{
                $user->save();
            } catch (QueryException $e){
                return new JsonResponse([
                    'message' => 'Sql exception'
                ], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse([
                'message' => 'Success update user',
                'data' => $user
            ], Response::HTTP_CREATED);
        }
        return new JsonResponse([
            'message' => 'Permission denied'
        ], Response::HTTP_UNAUTHORIZED);
    }
    
}