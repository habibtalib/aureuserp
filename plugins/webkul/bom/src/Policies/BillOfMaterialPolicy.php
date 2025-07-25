<?php

namespace Webkul\BOM\Policies;

use App\Models\User;
use Webkul\BOM\Models\BillOfMaterial;

class BillOfMaterialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_bill::of::material');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('view_bill::of::material');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_bill::of::material');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('update_bill::of::material');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('delete_bill::of::material');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_bill::of::material');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('force_delete_bill::of::material');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_bill::of::material');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('restore_bill::of::material');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_bill::of::material');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('replicate_bill::of::material');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_bill::of::material');
    }

    /**
     * Determine whether the user can activate BOMs.
     */
    public function activate(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('activate_bill::of::material');
    }

    /**
     * Determine whether the user can make BOMs obsolete.
     */
    public function makeObsolete(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('make_obsolete_bill::of::material');
    }

    /**
     * Determine whether the user can explode BOMs.
     */
    public function explode(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('explode_bill::of::material');
    }

    /**
     * Determine whether the user can view where-used analysis.
     */
    public function whereUsed(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $user->can('where_used_bill::of::material');
    }
}