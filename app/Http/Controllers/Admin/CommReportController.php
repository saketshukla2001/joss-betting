<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use Illuminate\Http\Request;

class CommReportController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentCommission::with(['agent.parent', 'bet', 'player'])
            ->orderByDesc('created_at');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('agent')) {
            $search = trim($request->string('agent'));
            $query->whereHas('agent', fn ($q) => $q->where('username', 'like', "%{$search}%"));
        }

        $entries = $query->get();
        $rows = [];

        foreach ($entries as $entry) {
            $agent = $entry->agent;

            if (! $agent) {
                continue;
            }

            $date = $entry->created_at->toDateString();
            $key = $date.'|'.$agent->id;

            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'date' => $date,
                    'agent_id' => $agent->id,
                    'username' => $agent->username,
                    'received' => ['m' => 0.0, 's' => 0.0, 'c' => 0.0, 'matka' => 0.0, 't' => 0.0],
                    'given' => ['m' => 0.0, 's' => 0.0, 'c' => 0.0, 'matka' => 0.0, 't' => 0.0],
                ];
            }

            $amount = round((float) $entry->amount, 2);
            $rows[$key]['received']['m'] += $amount;
            $rows[$key]['received']['t'] += $amount;

            $parent = $agent->parent;

            if ($parent) {
                $parentEntry = AgentCommission::where('agent_id', $parent->id)
                    ->where('bet_id', $entry->bet_id)
                    ->first();

                if ($parentEntry) {
                    $given = round((float) $parentEntry->amount, 2);
                    $rows[$key]['given']['m'] += $given;
                    $rows[$key]['given']['t'] += $given;
                }
            }
        }

        foreach ($rows as &$row) {
            foreach (['received', 'given'] as $side) {
                foreach (['m', 's', 'c', 'matka', 't'] as $bucket) {
                    $row[$side][$bucket] = round($row[$side][$bucket], 2);
                }
            }
        }
        unset($row);

        usort($rows, fn ($a, $b) => [$b['date'], $a['username']] <=> [$a['date'], $b['username']]);

        $totals = [
            'received' => ['m' => 0.0, 's' => 0.0, 'c' => 0.0, 'matka' => 0.0, 't' => 0.0],
            'given' => ['m' => 0.0, 's' => 0.0, 'c' => 0.0, 'matka' => 0.0, 't' => 0.0],
        ];

        foreach ($rows as $row) {
            foreach (['received', 'given'] as $side) {
                foreach (['m', 's', 'c', 'matka', 't'] as $bucket) {
                    $totals[$side][$bucket] += $row[$side][$bucket];
                }
            }
        }

        return view('admin.classic.comm-report', compact('rows', 'totals'));
    }
}
