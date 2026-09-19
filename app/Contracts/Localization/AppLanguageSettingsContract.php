<?php

namespace App\Contracts\Localization;

/**
 * Which of the translated languages each mobile app shows in its picker.
 * Translation itself is not affected: every active language is still
 * translated so it is ready the moment an app switches it on.
 */
interface AppLanguageSettingsContract
{
    /**
     * @return array<int, array{code: string, name: string, native_name: string, is_default: bool, apps: array<string, bool>}>
     */
    public function matrix(): array;

    /**
     * @param  array<string, array<int, string>>  $enabled  app => switched-on locale codes
     */
    public function save(array $enabled): void;

    /**
     * @return array<int, string>
     */
    public function activeFor(string $app): array;
}
