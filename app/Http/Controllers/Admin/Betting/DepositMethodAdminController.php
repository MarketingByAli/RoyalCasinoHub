<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Models\DepositMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepositMethodAdminController extends Controller
{
    public function index()
    {
        $methods = DepositMethod::orderBy('sort_order')->orderBy('coin_name')->get();

        return view('admin.betting.deposit-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.betting.deposit-methods.form', ['method' => new DepositMethod]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        unset($validated['qr_code']);
        $path = $this->storeQr($request);
        if ($path) {
            $validated['qr_path'] = $path;
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        DepositMethod::create($validated);

        return redirect()->route('admin.betting.deposit-methods.index')->with('success', 'Deposit method created.');
    }

    public function edit(DepositMethod $depositMethod)
    {
        return view('admin.betting.deposit-methods.form', ['method' => $depositMethod]);
    }

    public function update(Request $request, DepositMethod $depositMethod)
    {
        $validated = $this->validated($request);
        unset($validated['qr_code']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('qr_code')) {
            if ($depositMethod->qr_path) {
                Storage::disk('public')->delete($depositMethod->qr_path);
            }
            $validated['qr_path'] = $this->storeQr($request);
        }

        $depositMethod->update($validated);

        return redirect()->route('admin.betting.deposit-methods.index')->with('success', 'Deposit method updated.');
    }

    public function destroy(DepositMethod $depositMethod)
    {
        if ($depositMethod->qr_path) {
            Storage::disk('public')->delete($depositMethod->qr_path);
        }
        $depositMethod->delete();

        return back()->with('success', 'Deposit method removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'coin_name' => 'required|string|max:64',
            'network' => 'nullable|string|max:64',
            'address' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:5000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'qr_code' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);
    }

    private function storeQr(Request $request): ?string
    {
        if (! $request->hasFile('qr_code')) {
            return null;
        }

        return $request->file('qr_code')->store('betting/deposit-qr', 'public');
    }
}
