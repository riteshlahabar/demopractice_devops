<?php

namespace App\Support\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleDefinitionContract;
use App\Contracts\Admin\Modules\ModuleExportContract;
use App\Contracts\Admin\Modules\ModuleFormDataContract;
use App\Contracts\Admin\Modules\ModuleInputContract;
use App\Contracts\Admin\Modules\ModuleQueryContract;
use App\Contracts\Admin\Modules\ModuleValidationContract;

/**
 * The collaborators every config driven admin module needs, grouped so the
 * roughly forty controllers that inherit AdminModuleController take one
 * constructor argument instead of six - and adding a seventh later does not
 * touch any of them.
 *
 * Each property is a real contract resolved by the container; this only bundles
 * them, it does not resolve anything itself.
 */
final class AdminModuleServices
{
    public function __construct(
        public readonly ModuleDefinitionContract $definition,
        public readonly ModuleQueryContract $queries,
        public readonly ModuleValidationContract $validation,
        public readonly ModuleFormDataContract $formData,
        public readonly ModuleInputContract $input,
        public readonly ModuleExportContract $export,
    ) {}
}
