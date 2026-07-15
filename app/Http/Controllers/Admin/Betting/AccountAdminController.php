<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Enums\AccountState;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountAdminController extends Controller
{
    public function edit(User $user)
    {
        $user->load('bettingProfile');
        $states = AccountState::cases();

        return view('admin.betting.accounts.edit', compact('user', 'states'));
    }

    public function update(Request $request, User $user)
    {
        $profile = $user->bettingProfile;
        if (! $profile) {
            return back()->with('error', 'User has no betting profile.');
        }

        $validated = $request->validate([
            'account_state' => ['required', 'string', Rule::in(array_column(AccountState::cases(), 'value'))],
        ]);

        $profile->account_state = AccountState::from($validated['account_state']);
        $profile->save();

        return back()->with('success', 'Account state updated to '.$profile->account_state->value.'.');
    }
}
