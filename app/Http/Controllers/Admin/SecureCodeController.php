<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use App\Models\User;
use Illuminate\Http\Request;

class SecureCodeController extends Controller
{
    public function index(Request $request)
    {
        $users = User::whereRaw('1 = 0')->paginate(25);

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $users = User::with('parent')
                ->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('secure_code', 'like', "%{$search}%")
                        ->orWhere('agent_code', 'like', "%{$search}%");
                })
                ->orderBy('username')
                ->paginate(25)
                ->withQueryString();
        }

        return view('admin.classic.secure-codes', compact('users'));
    }

    public function regenerate(Request $request, User $user)
    {
        $user->update(['secure_code' => User::generateSecureCode()]);

        OperationLog::record('SECURE CODE UPDATED', 'SECURE CODE REGENERATED FOR '.$user->username.' BY ['.$request->user()->agent_code.','.$request->user()->name.']', $request->user());

        return back()->with('success', 'New secure code generated for '.$user->username.'.');
    }
}
