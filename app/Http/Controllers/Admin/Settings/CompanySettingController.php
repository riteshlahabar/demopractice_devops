<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanySettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.company', [
            'setting' => CompanySetting::query()->first() ?: new CompanySetting,
            'pageTitle' => 'Company Profile',
            'breadcrumbs' => ['Admin', 'Settings', 'Company Profile'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'short_intro' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:2048'],
            'map_embed_url' => ['nullable', 'url', 'max:2048'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'cin_number' => ['nullable', 'string', 'max:50'],
            'founder_name' => ['nullable', 'string', 'max:255'],
            'chairman_name' => ['nullable', 'string', 'max:255'],
            'managing_director_name' => ['nullable', 'string', 'max:255'],
            'google_business_url' => ['nullable', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
        ]);

        unset($validated['logo']);
        $setting = CompanySetting::query()->first() ?: new CompanySetting;

        if ($request->hasFile('logo')) {
            $directory = public_path('uploads/company');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file = $request->file('logo');
            $filename = now()->format('YmdHis').'-'.Str::random(16).'.'.strtolower($file->getClientOriginalExtension() ?: 'png');
            $file->move($directory, $filename);
            $validated['logo_path'] = 'uploads/company/'.$filename;
        }

        $setting->fill($validated)->save();

        return back()->with('success', 'Company profile updated successfully.');
    }
}
