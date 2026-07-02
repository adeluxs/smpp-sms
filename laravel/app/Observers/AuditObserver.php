<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        AuditLog::create([
            'tenant_id' => $model->tenant_id ?? null,
            'action' => 'created',
            'resource_type' => $model->getTable(),
            'resource_id' => $model->id,
            'new_values' => $model->toArray(),
        ]);
    }

    public function updated(Model $model): void
    {
        AuditLog::create([
            'tenant_id' => $model->tenant_id ?? null,
            'action' => 'updated',
            'resource_type' => $model->getTable(),
            'resource_id' => $model->id,
            'old_values' => $model->getOriginal(),
            'new_values' => $model->getChanges(),
        ]);
    }

    public function deleted(Model $model): void
    {
        AuditLog::create([
            'tenant_id' => $model->tenant_id ?? null,
            'action' => 'deleted',
            'resource_type' => $model->getTable(),
            'resource_id' => $model->id,
            'old_values' => $model->getOriginal(),
        ]);
    }
}