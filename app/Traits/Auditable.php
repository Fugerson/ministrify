<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /**
     * Boot the auditable trait
     */
    public static function bootAuditable(): void
    {
        // Log creation
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        // Log update
        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (!empty($dirty)) {
                $old = array_intersect_key($model->getOriginal(), $dirty);
                $model->logAudit('updated', $old, $dirty);
            }
        });

        // Log deletion
        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getOriginal(), null);
        });

        // Log restoration (if using SoftDeletes)
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logAudit('restored', null, $model->getAttributes());
            });
        }
    }

    /**
     * Create audit log entry
     */
    protected function logAudit(string $action, ?array $oldValues, ?array $newValues): void
    {
        // Skip if no auth user (during seeding, etc.)
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Skip logging for super admin in invisible mode (impersonating church)
        if ($user->isSuperAdmin() && session('impersonate_church_id')) {
            return;
        }

        // Get church_id from model or user
        $churchId = $this->church_id ?? $user->church_id ?? null;

        if (!$churchId) {
            return;
        }

        // Filter out sensitive/technical fields
        $sensitiveFields = ['password', 'remember_token', 'telegram_bot_token', 'calendar_token'];
        $oldValues = $this->filterSensitive($oldValues, $sensitiveFields);
        $newValues = $this->filterSensitive($newValues, $sensitiveFields);

        AuditLog::create([
            'church_id' => $churchId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'model_name' => $this->getAuditName(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Filter out sensitive fields
     */
    protected function filterSensitive(?array $data, array $sensitiveFields): ?array
    {
        if (!$data) {
            return null;
        }

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[HIDDEN]';
            }
        }

        return $data;
    }

    /**
     * Get a human-readable name for the audit log
     * Override this in your model for custom names
     */
    public function getAuditName(): string
    {
        // Try common name fields
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        if (isset($this->first_name) && isset($this->last_name)) {
            return trim("{$this->first_name} {$this->last_name}");
        }
        if (isset($this->full_name)) {
            return $this->full_name;
        }
        if (isset($this->email)) {
            return $this->email;
        }

        return "#{$this->getKey()}";
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return AuditLog::where('model_type', get_class($this))
            ->where('model_id', $this->getKey())
            ->orderByDesc('created_at');
    }

    /**
     * Log custom action
     */
    public function logCustomAction(string $action, ?string $notes = null): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Skip logging for super admin in invisible mode
        if ($user->isSuperAdmin() && session('impersonate_church_id')) {
            return;
        }

        $churchId = $this->church_id ?? $user->church_id ?? null;

        if (!$churchId) {
            return;
        }

        AuditLog::create([
            'church_id' => $churchId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'model_name' => $this->getAuditName(),
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
