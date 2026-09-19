<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Model;

class AnnouncementController extends AdminModuleController
{
    protected string $moduleKey = 'announcements';

    protected function persist(array $data, ?Model $record): Model
    {
        if (! $record) {
            $data['created_by'] = auth()->id();
        }

        return parent::persist($data, $record);
    }
}
