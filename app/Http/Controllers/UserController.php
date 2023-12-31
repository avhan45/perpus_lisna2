<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->level == 'user') {
            Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
            return redirect()->to('/');
        }

        $datas = User::get();
        return view('auth.user', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(Auth::user()->level == 'user') {
            Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
            return redirect()->to('/');
        }
        return view('auth.register');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $count = User::where('username',$request->input('username'))->count();

        if($count>0){
            // Session::flash('message', 'Already exist!');
            // Session::flash('message_type', 'danger');
            return redirect()->to('user');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:20|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);


        if($request->file('gambar') == '') {
            $gambar = NULL;
        } else {
            $file = $request->file('gambar');
            $dt = Carbon::now();
            $acak  = $file->getClientOriginalExtension();
            $fileName = rand(11111,99999).'-'.$dt->format('Y-m-d-H-i-s').'.'.$acak; 
            $request->file('gambar')->move("images/user", $fileName);
            $gambar = $fileName;
        }

        User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'level' => $request->input('level'),
            'password' => bcrypt(($request->input('password'))),
            'gambar' => $gambar
        ]);

        // Session::flash('message', 'Berhasil ditambahkan!');
        // Session::flash('message_type', 'success');
        return redirect()->route('user.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if((Auth::user()->level == 'user') && (Auth::user()->id != $id)) {
            Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
            return redirect()->to('/');
    }

    $data = User::findOrFail($id);

    return view('auth.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if((Auth::user()->level == 'user') && (Auth::user()->id != $id)) {
            Alert::info('Oopss..', 'Anda dilarang masuk ke area ini.');
            return redirect()->to('/');
    }

    $data = User::findOrFail($id);

    return view('auth.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user_data = User::findOrFail($id);

        if($request->file('gambar')) 
        {
            $file = $request->file('gambar');
            $dt = Carbon::now();
            $acak  = $file->getClientOriginalExtension();
            $fileName = rand(11111,99999).'-'.$dt->format('Y-m-d-H-i-s').'.'.$acak; 
            $request->file('gambar')->move("images/user", $fileName);
            $user_data->gambar = $fileName;
        }

        $user_data->name = $request->input('name');
        $user_data->email = $request->input('email');
        if($request->input('password')) {
        $user_data->level = $request->input('level');
        }

        if($request->input('password')) {
            $user_data->password= bcrypt(($request->input('password')));
        
        }

        $user_data->update();

        // Session::flash('message', 'Berhasil diubah!');
        // Session::flash('message_type', 'success');
        return redirect()->to('user');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if(Auth::user()->id != $id) {
            $user_data = User::findOrFail($id);
            $user_data->delete();
            // Session::flash('message', 'Berhasil dihapus!');
            // Session::flash('message_type', 'success');
        } else {
            // Session::flash('message', 'Akun anda sendiri tidak bisa dihapus!');
            // Session::flash('message_type', 'danger');
        }
        return redirect()->to('user');
    }
}
