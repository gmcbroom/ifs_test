<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends Controller {

    private $page_size = 25;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $user = Auth::user();
        $message = '';

        // Allow Admin user to edit other users
        if ($user->admin) {
            $users = User::paginate($this->page_size);
            return view('users.index', compact('users'));
        } else {
            $users = User::where('id', $user->id)->orderBy('id')->paginate($this->page_size);
            return view('users.index', compact('users'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        User::create($request->all());
        return redirect('/home');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {

        $user = Auth::user();
        $message = '';

        // Allow Admin user to edit other users
        if ($user->admin) {
            $user = User::findOrFail($id);
        }
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {

        $user = Auth::user();
        $message = '';

        // Allow Admin user to edit other users
        if ($user->admin) {
            $user = User::findOrFail($id);
        }
        return view('users.edit', compact('user', 'message'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $user = Auth::user();
        $message = '';

        // Allow Admin user to edit other users
        if ($user->admin) {
            $user = User::findOrFail($id);
        }

        if ($user->update($request->all())) {
            $message = "Updated";
        }
        return view('users.edit', compact('user', 'message'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function toggleStatus($id) {

        $user = Auth::user();
        $message = '';

        // Allow Admin user to edit other users
        if ($user->admin) {
            $user = User::findOrFail($id);
            $user->active = !$user->active;
            $user->save();
            return redirect('user');
        }
    }

    /**
     * Show confirmation that user has registered.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function registered() {

        Auth::logout();
        return view('users.registered');
    }

    public function verify($token) {

        $count = '';
        if ($token > "") {
            $count = User::where('verify_token', $token)->count();
        }

        if ($count == 1) {
            User::where('verified', 0)
                    ->where('verify_token', $token)
                    ->update(['verified' => 1, 'verify_token' => '']);

            return view('users.verified');
        } else {
            return view('users.unauth');
        }
    }

}
