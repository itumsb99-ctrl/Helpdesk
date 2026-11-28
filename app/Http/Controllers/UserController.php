<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->latest();
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn ($builder) => $builder->where('employee_id', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('unit', 'like', "%{$term}%"));
        }
        if ($request->filled('role') && in_array($request->role, ['admin', 'teknisi', 'pegawai'], true)) $query->where('role', $request->role);
        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'], true)) $query->where('is_active', $request->status === 'active');
        return view('users.index', ['users' => $query->paginate(15)->withQueryString()]);
    }

    public function create() { return view('users.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'string', 'regex:/^SBA[0-9]{3,}$/i', 'max:20', 'unique:users,employee_id'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'role' => ['required', 'in:admin,teknisi,pegawai'],
            'unit' => ['nullable', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $data['employee_id'] = strtoupper($data['employee_id']);
        User::create($data + ['is_active' => true]);
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user) { return view('users.edit', compact('user')); }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'employee_id' => ['required', 'string', 'regex:/^SBA[0-9]{3,}$/i', 'max:20', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,teknisi,pegawai'],
            'unit' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);
        $data['employee_id'] = strtoupper($data['employee_id']);
        if ($request->user()->is($user) && $data['role'] !== $user->role) {
            return back()->withInput()->withErrors(['role' => 'Anda tidak dapat mengubah role akun sendiri.']);
        }
        if ($user->role === 'admin' && $user->is_active && $data['role'] !== 'admin' &&
            User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withInput()->withErrors(['role' => 'Minimal harus ada satu Admin yang aktif.']);
        }
        if (empty($data['password'])) unset($data['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggle(Request $request, User $user)
    {
        if ($request->user()->is($user)) return back()->withErrors(['user' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        if ($user->role === 'admin' && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'Minimal harus ada satu Admin yang aktif.']);
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', $user->is_active ? 'Akun pengguna berhasil diaktifkan.' : 'Akun pengguna berhasil dinonaktifkan.');
    }
}
