<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Akademisi;
use Illuminate\Auth\Access\HandlesAuthorization;

class AkademisiPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_any_akademisi');
    }

    public function view(User $user, Akademisi $akademisi)
    {
        return $user->can('view_akademisi');
    }

    public function create(User $user)
    {
        return $user->can('create_akademisi');
    }

    public function update(User $user, Akademisi $akademisi)
    {
        return $user->can('update_akademisi');
    }

    public function delete(User $user, Akademisi $akademisi)
    {
        return $user->can('delete_akademisi');
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_akademisi');
    }
}
