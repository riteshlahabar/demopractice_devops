<?php

namespace App\Contracts\Files;

use Illuminate\Http\UploadedFile;

interface PublicUploadContract
{
    public function store(UploadedFile $file, string $directory): string;

    public function delete(?string $path): void;
}
