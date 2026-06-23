<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SmppClient;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmppClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-smpp-clients');
    }

    public function view(User $user, SmppClient $client): bool
    {
        return $user->tenant_id === $client->tenant_id &&
            $user->hasPermissionTo('view-smpp-clients');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-smpp-clients');
    }

    public function update(User $user, SmppClient $client): bool
    {
        return $user->tenant_id === $client->tenant_id &&
            $user->hasPermissionTo('update-smpp-clients');
    }

    public function delete(User $user, SmppClient $client): bool
    {
        return $user->tenant_id === $client->tenant_id &&
            $user->hasPermissionTo('delete-smpp-clients');
    }
}