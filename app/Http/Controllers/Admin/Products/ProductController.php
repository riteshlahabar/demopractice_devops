<?php

namespace App\Http\Controllers\Admin\Products;

use App\Contracts\Catalog\Product\ProductFormContract;
use App\Contracts\Catalog\Product\ProductInputContract;
use App\Contracts\Catalog\Product\ProductValidationContract;
use App\Contracts\Catalog\Product\ProductWorkflowContract;
use App\Data\Catalog\ProductSaveData;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Catalog\Product;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * SRP: Product CRUD orchestration only.
 * DIP: Depends only on focused Product contracts.
 */
final class ProductController extends AdminModuleController
{
    protected string $moduleKey = 'products';

    public function __construct(
        AdminModuleServices $modules,
        private readonly ProductValidationContract $validation,
        private readonly ProductInputContract $input,
        private readonly ProductWorkflowContract $workflow,
        private readonly ProductFormContract $form,
    ) {
        parent::__construct($modules);
    }

    protected function rules(array $module, ?Model $record = null): array
    {
        return $this->validation->extend(
            parent::rules($module, $record),
            $record instanceof Product ? $record : null,
        );
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $prepared = parent::prepareData($validated, $request, $module);

        return [
            '__product_save_data' => $this->input->make(
                $prepared,
                $request->all(),
                $request->allFiles(),
                $module,
            ),
        ];
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $payload = $data['__product_save_data'] ?? null;
        abort_unless($payload instanceof ProductSaveData, 500, 'Invalid product save payload.');

        $product = $this->workflow->save(
            $payload,
            $record instanceof Product ? $record : null,
        );

        $this->bumpCacheVersionForModule();

        return $product;
    }

    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);

        return $record instanceof Product
            ? $this->form->augmentData($record, $data)
            : $data;
    }

    protected function formOptions(array $module): array
    {
        return $this->form->augmentOptions(parent::formOptions($module));
    }
}
