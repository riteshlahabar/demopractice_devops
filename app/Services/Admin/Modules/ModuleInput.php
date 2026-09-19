<?php

namespace App\Services\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleInputContract;
use App\Contracts\Files\PublicUploadContract;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ModuleInput implements ModuleInputContract
{
    public function __construct(private readonly PublicUploadContract $uploads) {}

    public function prepare(array $validated, Request $request, array $module): array
    {
        foreach ($module['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $type = $field['type'] ?? null;

            if ($type === 'checkbox') {
                $validated[$name] = $request->boolean($name);
            }

            // A blank optional field arrives as null, but plenty of columns are
            // NOT NULL with a database default, so writing the null explicitly
            // fails the insert. Fall back to the default the config declares.
            if (array_key_exists('default', $field)
                && array_key_exists($name, $validated)
                && $validated[$name] === null) {
                $validated[$name] = $field['default'];
            }

            // An empty password field means "keep the current one".
            if ($type === 'password' && empty($validated[$name] ?? null)) {
                unset($validated[$name]);
            }

            if (in_array($type, ['file', 'image'], true)) {
                if ($request->hasFile($name)) {
                    $validated[$name] = $this->store($request->file($name), $module, $field);
                } else {
                    unset($validated[$name]);
                }
            }
        }

        if (array_key_exists('slug', $validated) && empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $field
     */
    private function store(mixed $file, array $module, array $field): string
    {
        $directory = str_replace('\\', '/', trim((string) ($field['upload_dir'] ?? 'uploads/'.$module['key']), '/\\'));

        return $this->uploads->store($file, $directory);
    }
}
