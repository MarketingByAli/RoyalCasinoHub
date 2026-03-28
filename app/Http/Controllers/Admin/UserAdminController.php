<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('id');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], substr($request->search, 0, 255));
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        $users = $query->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:user,casino_owner,admin',
            'is_active' => 'required|in:0,1',
        ]);

        $active = (bool) (int) $validated['is_active'];

        if ($user->id === $request->user()->id) {
            if (! $active) {
                return back()->with('error', 'You cannot deactivate your own account.');
            }
            if ($validated['role'] !== 'admin') {
                return back()->with('error', 'You cannot remove your own admin role.');
            }
        }

        $user->role = $validated['role'];
        $user->is_active = $active;
        $user->save();

        return back()->with('success', 'User updated.');
    }
}
