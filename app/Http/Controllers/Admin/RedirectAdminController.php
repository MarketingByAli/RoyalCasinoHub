<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RedirectOldUrls;
use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectAdminController extends Controller
{
    public function index()
    {
        $redirects = Redirect::latest()->paginate(25);
        return view('admin.redirects.index', compact('redirects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_url' => 'required|string|max:2048',
            'to_url' => ['required', 'string', 'max:2048', function ($attr, $val, $fail) {
                if (!str_starts_with($val, '/') && !filter_var($val, FILTER_VALIDATE_URL)) {
                    $fail('The redirect target must be a relative path (starting with /) or a valid URL.');
                }
            }],
            'status_code' => 'required|in:301,302',
        ]);

        $validated['from_url'] = '/' . trim($validated['from_url'], '/');
        if ($validated['from_url'] === '/') {
            $validated['from_url'] = '/';
        }

        Redirect::updateOrCreate(
            ['from_url' => $validated['from_url']],
            ['to_url' => $validated['to_url'], 'status_code' => $validated['status_code']]
        );

        RedirectOldUrls::clearCache();

        return back()->with('success', 'Redirect created.');
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        RedirectOldUrls::clearCache();

        return back()->with('success', 'Redirect deleted.');
    }
}
