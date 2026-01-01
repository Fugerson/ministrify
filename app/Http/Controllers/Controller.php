<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Get the current church context.
     * Handles super admin impersonation.
     */
    protected function getCurrentChurch(): Church
    {
        $user = auth()->user();

        // Super admin impersonation
        if ($user->is_super_admin && session('impersonate_church_id')) {
            return Church::findOrFail(session('impersonate_church_id'));
        }

        // Super admin without impersonation - use first church as fallback
        if ($user->is_super_admin && !$user->church_id) {
            return Church::first();
        }

        $church = $user->church;

        if (!$church) {
            abort(redirect()->route('dashboard')
                ->with('error', 'Ця функція доступна тільки для користувачів з церквою.'));
        }

        return $church;
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

        if ($model->church_id !== $this->getCurrentChurch()->id) {
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
}
