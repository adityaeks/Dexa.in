<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_any_customer');
    }

    public function view(User $user, Customer $customer)
    {
        return $user->can('view_customer');
    }

    public function create(User $user)
    {
        return $user->can('create_customer');
    }

    public function update(User $user, Customer $customer)
    {
        return $user->can('update_customer');
    }

    public function delete(User $user, Customer $customer)
    {
        return $user->can('delete_customer');
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_customer');
    }
}
