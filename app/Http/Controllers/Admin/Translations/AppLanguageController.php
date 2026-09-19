<?php

namespace App\Http\Controllers\Admin\Translations;

use App\Contracts\Admin\Access\AdminAccessContract;
use App\Contracts\Localization\AppLanguageSettingsContract;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Translation → App Languages: which languages each mobile app offers.
 */
final class AppLanguageController extends Controller
{
    public function __construct(
        private readonly AppLanguageSettingsContract $settings,
        private readonly AdminAccessContract $access,
    ) {}

    public function edit(Request $request): View
    {
        return view('admin.translations.app-languages', [
            'languages' => $this->settings->matrix(),
            'apps' => (array) config('localization.apps', []),
            'canEdit' => $request->user() !== null && $this->access->allows($request->user(), 'app-languages', 'edit'),
            'pageTitle' => 'App Languages',
            'breadcrumbs' => ['Admin', 'Translation', 'App Languages'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apps' => ['nullable', 'array'],
            'apps.*' => ['array'],
            'apps.*.*' => ['string', 'max:10'],
        ]);

        $this->settings->save((array) ($validated['apps'] ?? []));

        return redirect()->route('admin.app-languages.edit')->with('success', 'App languages updated successfully.');
    }
}
