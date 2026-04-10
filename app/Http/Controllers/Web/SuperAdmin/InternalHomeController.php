<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InternalHomeController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()?->loadMissing(['roles', 'permissions', 'roles.permissions']);

        return view('super-admin.internal-home', [
            'internalUser' => $user,
            'permissionCodes' => $user?->permissionCodes()->sort()->values() ?? collect(),
        ]);
    }
}
