<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Harga;
use Illuminate\Auth\Access\HandlesAuthorization;

class HargaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_any_harga');
    }

    public function view(User $user, Harga $harga)
    {
        return $user->can('view_harga');
    }

    public function create(User $user)
    {
        return $user->can('create_harga');
    }

    public function update(User $user, Harga $harga)
    {
        return $user->can('update_harga');
    }

    public function delete(User $user, Harga $harga)
    {
        return $user->can('delete_harga');
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_harga');
    }
}
