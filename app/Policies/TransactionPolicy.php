<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Can view transactions list
     */
    public function viewAny(User $user): bool
    {
        return $user->canView('finances');
    }

    /**
     * Can view specific transaction
     */
    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->church_id !== $transaction->church_id) {
            return false;
        }

        // Ministry leaders can view their ministry transactions
        if ($transaction->ministry_id && $user->isLeader() && $user->person) {
            if ($transaction->ministry->leader_id === $user->person->id) {
                return true;
            }
        }

        // Person can view their own donations
        if ($transaction->person_id && $user->person) {
            if ($transaction->person_id === $user->person->id) {
                return true;
            }
        }

        return $user->canView('finances');
    }

    /**
     * Can create income transaction
     */
    public function createIncome(User $user): bool
    {
        return $user->canCreate('incomes');
    }

    /**
     * Can create expense transaction
     */
    public function createExpense(User $user): bool
    {
        return $user->canCreate('expenses');
    }

    /**
     * Can create any transaction
     */
    public function create(User $user): bool
    {
        return $user->canCreate('incomes') || $user->canCreate('expenses');
    }

    /**
     * Can update transaction
     */
    public function update(User $user, Transaction $transaction): bool
    {
        if ($user->church_id !== $transaction->church_id) {
            return false;
        }

        // Ministry leaders can update their ministry expenses
        if ($transaction->ministry_id && $transaction->isExpense && $user->isLeader() && $user->person) {
            if ($transaction->ministry->leader_id === $user->person->id) {
                return true;
            }
        }

        return $transaction->isIncome
            ? $user->canUpdate('incomes')
            : $user->canUpdate('expenses');
    }

    /**
     * Can delete transaction
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        if ($user->church_id !== $transaction->church_id) {
            return false;
        }

        return $transaction->isIncome
            ? $user->canDelete('incomes')
            : $user->canDelete('expenses');
    }

    /**
     * Can view financial reports
     */
    public function viewReports(User $user): bool
    {
        return $user->canView('finances');
    }

    /**
     * Can export transactions
     */
    public function export(User $user): bool
    {
        return $user->canView('finances');
    }

    /**
     * Can manage categories
     */
    public function manageCategories(User $user): bool
    {
        return $user->canUpdate('finances');
    }
}
