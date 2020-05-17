<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Image;
use Gate;
class UserController extends Controller

{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       //$this->authorize('isAdmin');
       if(Gate::allows('isAdmin') || Gate::allows('isAuthor')){
        return User::latest()->paginate(5);
       }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ]);
       return User::create([
           'name' => $request['name'],
           'email' => $request['email'],
           'type' => $request['type'],
           'bio' => $request['bio'],
           'photo' => $request['photo'],
           'password' => Hash::make($request['password'])
       ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return auth('api')->user();
    }
    public function search()
    {
        if($search = \Request::get('q')) {
            
         /*$users = User::where(function($query) use ($search){
        $query->where('name','LIKE',"%$search%")
        ->orWhere('email','LIKE',"%$search%")
        ->orWhere('type','LIKE',"%$search%");
         })->paginate(5);*/

         $users = User::where('name','LIKE',"%$search%")
        ->orWhere('email','LIKE',"%$search%")
        ->orWhere('type','LIKE',"%$search%")
        ->paginate(5);
        }
        else {
            return User::latest()->paginate(5);
        }
        return $users;
    }

    public function updateinfo(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|string|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:8'
        ]);
        $currentphoto = $user->photo;
        if($request->photo != $currentphoto) {
            $name = time().'.' . explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            Image::make($request->photo)->save(public_path('img/profile/').$name);
            $request->merge(['photo' => $name]);

            $oldphotopath = public_path('img/profile/').$currentphoto;

            if(file_exists($oldphotopath)) {
                @unlink($oldphotopath);
            }
        }

        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|string|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:8'
        ]);
        $user->update($request->all());
        
        return ['message' => 'User Updated Succcessfully'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
        $user = User::findOrFail($id);
        $user -> delete();
        return ['message' => 'User Deleted Succcessfully'];
    }
}
