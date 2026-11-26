<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index',['users'=>User::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:120'],
            'email'=>['required','email','max:180','unique:users,email'],
            'role'=>['required','in:admin,teknisi,pegawai'],
            'unit'=>['nullable','string','max:120'],
            'password'=>['required','confirmed',Password::min(8)],
        ]);
        User::create($data + ['is_active'=>true]);
        return redirect()->route('users.index')->with('success','Pengguna berhasil ditambahkan.');
    }
}
