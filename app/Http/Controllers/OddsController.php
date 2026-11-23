<?php

namespace App\Http\Controllers;

use App\Models\Selection;
use Illuminate\Http\Request;

class OddsController extends Controller
{
    public function bulk(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->take(150)
            ->values();

        $selections = Selection::with('market')
            ->whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn (Selection $selection) => [
                $selection->id => [
                    'odds' => round((float) $selection->odds, 2),
                    'status' => $selection->status,
                    'market_status' => $selection->market?->status,
                ],
            ]);

        return response()->json([
            'selections' => $selections,
            'server_time' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }
}
