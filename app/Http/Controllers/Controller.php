<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Per-request cache for current church to avoid repeated DB lookups.
     */
    private ?Church $cachedChurch = null;

    /**
     * Get the current church context.
     * Handles super admin impersonation.
     * Uses per-request cache to avoid repeated queries within the same request.
     */
    protected function getCurrentChurch(): Church
    {
        if ($this->cachedChurch) {
            return $this->cachedChurch;
        }

        $user = auth()->user();

        // Super admin impersonation
        if ($user->is_super_admin && session('impersonate_church_id')) {
            return $this->cachedChurch = Church::findOrFail(session('impersonate_church_id'));
        }

        // Super admin without impersonation - redirect to system panel
        if ($user->is_super_admin && !$user->church_id) {
            abort(redirect()->route('system.index')
                ->with('warning', 'Оберіть церкву для роботи.'));
        }

        $church = $user->church;

        if (!$church) {
            abort(redirect()->route('dashboard')
                ->with('error', 'Ця функція доступна тільки для користувачів з церквою.'));
        }

        return $this->cachedChurch = $church;
    }

    /**
     * Authorize that a model belongs to the current church.
     * Use this for any model with church_id field.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function authorizeChurch(Model $model): void
    {
        if (!isset($model->church_id)) {
            return;
        }

        $churchId = $this->getCurrentChurch()->id;

        // For User models, check via pivot (user may belong to multiple churches)
        if ($model instanceof \App\Models\User) {
            if (!$model->belongsToChurch($churchId)) {
                abort(404);
            }
            return;
        }

        if ($model->church_id !== $churchId) {
            abort(404);
        }
    }

    /**
     * Authorize that the current user has a person profile.
     * Required for some operations like assignments.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function requirePersonProfile(): \App\Models\Person
    {
        $person = auth()->user()->person;

        if (!$person) {
            abort(403, 'Для цієї дії потрібен профіль у системі.');
        }

        return $person;
    }

    /**
     * Get current user's person profile or null.
     */
    protected function getCurrentPerson(): ?\App\Models\Person
    {
        return auth()->user()->person;
    }

    /**
     * Check if current user is admin.
     */
    protected function isAdmin(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Check if current user is at least a leader.
     */
    protected function isLeaderOrAbove(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->isLeader();
    }

    /**
     * Validate that a related model belongs to the current church.
     * Returns the validated model or null.
     */
    protected function validateChurchRelation(string $modelClass, ?int $id): ?Model
    {
        if (!$id) {
            return null;
        }

        $model = $modelClass::find($id);

        if (!$model || $model->church_id !== $this->getCurrentChurch()->id) {
            return null;
        }

        return $model;
    }

    /**
     * Log a custom audit action.
     * Use for actions that don't automatically trigger model events.
     *
     * @param string $action Action name (e.g., 'exported', 'imported', 'bulk_deleted')
     * @param string $modelType Model class name without namespace (e.g., 'Person')
     * @param int|null $modelId Model ID if applicable
     * @param string|null $modelName Human-readable model name
     * @param array|null $newValues Additional data to log
     * @param array|null $oldValues Previous state if applicable
     * @param string|null $notes Additional notes
     */
    protected function logAuditAction(
        string $action,
        string $modelType,
        ?int $modelId = null,
        ?string $modelName = null,
        ?array $newValues = null,
        ?array $oldValues = null,
        ?string $notes = null
    ): void {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Skip logging for super admin in system admin panel (not impersonating)
        if ($user->isSuperAdmin() && !session('impersonating_from')) {
            return;
        }

        $churchId = $this->cachedChurch?->id ?? $user->church_id ?? null;

        if (!$churchId) {
            return;
        }

        $userName = $user->name;
        if (session('impersonating_from')) {
            $userName = $user->name . ' (via super admin #' . session('impersonating_from') . ')';
        }

        AuditLog::create([
            'church_id' => $churchId,
            'user_id' => $user->id,
            'user_name' => $userName,
            'action' => $action,
            'model_type' => 'App\\Models\\' . $modelType,
            'model_id' => $modelId,
            'model_name' => $modelName,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
