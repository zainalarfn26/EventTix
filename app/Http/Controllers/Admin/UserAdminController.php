<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public const ROLES = ['admin', 'organizer', 'customer'];

    public function index(Request $request)
    {
        $users = User::with('roles')
            ->withCount(['orders', 'tickets'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->role($request->role))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $roleCounts = [];
        foreach (self::ROLES as $role) {
            $roleCounts[$role] = User::role($role)->count();
        }
        $roleCounts['all'] = User::count();

        return view('admin.accounts.index', compact('users', 'roleCounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);
        $user->syncRoles([Role::findByName($data['role'], 'web')]);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Akun ' . $user->name . ' berhasil dibuat sebagai ' . $data['role'] . '.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $isSelf = $user->id === $request->user()->id;

        if ($user->hasRole('admin') && $data['role'] !== 'admin') {
            if ($isSelf) {
                return $this->failWithModal('Anda tidak bisa menurunkan role akun Anda sendiri.');
            }
            if ($this->isLastAdmin($user)) {
                return $this->failWithModal('Minimal harus ada satu akun admin. Role admin terakhir tidak bisa diubah.');
            }
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        $user->syncRoles([Role::findByName($data['role'], 'web')]);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Akun ' . $user->name . ' berhasil diperbarui' . (!empty($data['password']) ? ' (password diganti).' : '.'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun yang sedang Anda gunakan.');
        }

        if ($user->hasRole('admin') && $this->isLastAdmin($user)) {
            return back()->with('error', 'Minimal harus ada satu akun admin. Admin terakhir tidak bisa dihapus.');
        }

        if ($user->orders()->where('status', 'paid')->exists()) {
            return back()->with('error', 'Akun ' . $user->name . ' memiliki riwayat pembelian tiket yang sudah dibayar sehingga tidak bisa dihapus.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun ' . $name . ' berhasil dihapus.');
    }

    protected function isLastAdmin(User $user): bool
    {
        return User::role('admin')->where('id', '!=', $user->id)->doesntExist();
    }

    protected function failWithModal(string $message)
    {
        return back()->withInput()->withErrors(['role' => $message]);
    }
}
