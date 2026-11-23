<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use App\Models\User;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    private const LEVELS = [
        'mini_master' => 'mini_master',
        'master_master' => 'master_master',
        'super_master' => 'super_master',
        'agent' => 'agent_master',
    ];

    public function edit(User $user)
    {
        abort_unless(isset(self::LEVELS[$user->role]), 404);

        $modules = User::PANEL_MODULES;
        $effective = $user->effectivePanelPermissions();
        $level = self::LEVELS[$user->role];

        return view('admin.access.edit', compact('user', 'modules', 'effective', 'level'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(isset(self::LEVELS[$user->role]), 404);

        $data = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', 'in:'.implode(',', array_keys(User::PANEL_MODULES))],
        ]);

        $modules = array_values(array_unique($data['modules'] ?? []));
        $user->update(['panel_permissions' => $modules]);

        OperationLog::record(
            'PANEL ACCESS UPDATED',
            'PANEL MODULES FOR '.$user->username.': '.(empty($modules) ? 'NONE' : strtoupper(implode(', ', $modules))).' BY ['.$request->user()->agent_code.','.$request->user()->name.']',
            $request->user()
        );

        return back()->with('success', 'Panel access updated for '.$user->username.'.');
    }

    public function reset(Request $request, User $user)
    {
        abort_unless(isset(self::LEVELS[$user->role]), 404);

        $user->update(['panel_permissions' => null]);

        OperationLog::record('PANEL ACCESS RESET', 'PANEL MODULES RESET TO ROLE DEFAULTS FOR '.$user->username, $request->user());

        return back()->with('success', 'Panel access reset to role defaults for '.$user->username.'.');
    }
}
