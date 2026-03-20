<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class SeoAdminController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name' => SeoSetting::get('site_name', 'RoyalCasinoHub'),
            'meta_title_pattern' => SeoSetting::get('meta_title_pattern', '{Casino Name} Review {Year} — Bonuses, Games & Rating | {Site Name}'),
            'meta_description_pattern' => SeoSetting::get('meta_description_pattern', 'Read our {Casino Name} review. Honest ratings, bonuses, games & more. Updated {Year}.'),
            'meta_title_default' => SeoSetting::get('meta_title_default', 'RoyalCasinoHub — Trusted Online Casino Reviews & Ratings'),
            'meta_description_default' => SeoSetting::get('meta_description_default', 'Discover trusted online casino reviews, ratings, and bonuses.'),
        ];

        return view('admin.seo.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'meta_title_pattern' => 'nullable|string|max:500',
            'meta_description_pattern' => 'nullable|string|max:500',
            'meta_title_default' => 'nullable|string|max:255',
            'meta_description_default' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            SeoSetting::set($key, $value);
        }

        return back()->with('success', 'SEO settings updated.');
    }
}
