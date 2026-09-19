<?php

namespace App\Http\Controllers\Admin\StorefrontAbout;

use App\Http\Controllers\Controller;
use App\Models\Storefront\StorefrontAboutPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontAboutPageController extends Controller
{
    /** Where the two intro photos are stored, relative to public/. */
    private const UPLOAD_DIR = 'uploads/storefront/about';

    public function edit(): View
    {
        return view('admin.storefront-about.page', [
            'setting' => StorefrontAboutPage::query()->first() ?: new StorefrontAboutPage,
            'pageTitle' => 'About Page',
            'breadcrumbs' => ['Admin', 'Storefront', 'About Page'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intro_label' => ['nullable', 'string', 'max:255'],
            'intro_heading' => ['nullable', 'string', 'max:255'],
            'intro_text' => ['nullable', 'string', 'max:5000'],
            'image_one' => ['nullable', 'image', 'max:2048'],
            'image_two' => ['nullable', 'image', 'max:2048'],
            'stats_label' => ['nullable', 'string', 'max:255'],
            'stats_heading' => ['nullable', 'string', 'max:255'],
            'team_label' => ['nullable', 'string', 'max:255'],
            'team_heading' => ['nullable', 'string', 'max:255'],
        ]);

        unset($validated['image_one'], $validated['image_two']);
        $setting = StorefrontAboutPage::query()->first() ?: new StorefrontAboutPage;

        foreach (['image_one' => 'image_one_path', 'image_two' => 'image_two_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $validated[$column] = $this->store($request, $input);
            }
        }

        $setting->fill($validated)->save();

        return back()->with('success', 'About page updated successfully.');
    }

    /** Moves one uploaded photo into public/uploads and returns its relative path. */
    private function store(Request $request, string $input): string
    {
        $directory = public_path(self::UPLOAD_DIR);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file($input);
        $filename = now()->format('YmdHis').'-'.Str::random(16).'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move($directory, $filename);

        return self::UPLOAD_DIR.'/'.$filename;
    }
}
