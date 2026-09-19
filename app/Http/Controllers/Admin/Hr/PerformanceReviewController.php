<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Model;

class PerformanceReviewController extends AdminModuleController
{
    protected string $moduleKey = 'performance-reviews';

    /**
     * A blank overall rating means "average of the three scores".
     */
    protected function persist(array $data, ?Model $record): Model
    {
        if (($data['overall_rating'] ?? null) === null) {
            $scores = [(float) ($data['sales_score'] ?? 0), (float) ($data['collection_score'] ?? 0), (float) ($data['visit_score'] ?? 0)];
            $data['overall_rating'] = round(array_sum($scores) / count($scores), 2);
        }

        $data['reviewed_by'] = auth()->id();

        return parent::persist($data, $record);
    }
}
