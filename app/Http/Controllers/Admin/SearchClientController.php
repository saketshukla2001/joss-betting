<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SearchClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'client')->with('parent')->withCount('bets');

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('agent_code', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('secure_code', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.settings.search-client', compact('clients'));
    }
}
