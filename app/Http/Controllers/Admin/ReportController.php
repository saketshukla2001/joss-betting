<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $status] = $this->filters($request);

        $query = Bet::with('user')->whereBetween('created_at', [$from, $to]);

        if ($status) {
            $query->where('status', $status);
        }

        $summary = [
            'bets' => (clone $query)->count(),
            'stake' => (float) (clone $query)->sum('stake'),
            'payout' => (float) (clone $query)->sum('payout'),
            'pending_stake' => (float) (clone $query)->where('status', 'pending')->sum('stake'),
        ];
        $summary['ggr'] = round($summary['stake'] - $summary['payout'], 2);

        $bySport = (clone $query)
            ->selectRaw('sport_name, COUNT(*) as bets_count, SUM(stake) as total_stake, SUM(payout) as total_payout')
            ->groupBy('sport_name')
            ->orderByDesc('total_stake')
            ->get();

        $topUsers = (clone $query)
            ->selectRaw('user_id, COUNT(*) as bets_count, SUM(stake) as total_stake, SUM(payout) as total_payout')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_stake')
            ->limit(8)
            ->get();

        $dailyRows = (clone $query)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as bets_count, SUM(stake) as total_stake, SUM(payout) as total_payout')
            ->groupBy('day')
            ->pluck('total_stake', 'day');

        $daily = [];
        $cursor = $from->copy()->startOfDay();
        $limit = 0;

        while ($cursor->lte($to) && $limit < 92) {
            $key = $cursor->toDateString();
            $daily[] = [
                'date' => $cursor->format('d M'),
                'stake' => (float) ($dailyRows[$key] ?? 0),
            ];
            $cursor->addDay();
            $limit++;
        }

        $dailyMax = max(1, collect($daily)->max('stake') ?: 1);

        return view('admin.reports.index', compact(
            'from', 'to', 'status', 'summary', 'bySport', 'topUsers', 'daily', 'dailyMax'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to, $status] = $this->filters($request);

        $query = Bet::with('user')->whereBetween('created_at', [$from, $to])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $filename = 'betzone-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Bet Code', 'Placed At', 'User', 'Sport', 'League', 'Match', 'Market',
                'Selection', 'Odds', 'Stake', 'Potential Return', 'Status', 'Payout',
            ]);

            $query->chunk(500, function ($bets) use ($out) {
                foreach ($bets as $bet) {
                    fputcsv($out, [
                        $bet->bet_code,
                        $bet->created_at?->format('Y-m-d H:i:s'),
                        $bet->user?->username,
                        $bet->sport_name,
                        $bet->league,
                        $bet->match_label,
                        $bet->market_name,
                        $bet->selection_name,
                        number_format($bet->odds, 2, '.', ''),
                        number_format($bet->stake, 2, '.', ''),
                        number_format($bet->potential_return, 2, '.', ''),
                        $bet->status,
                        number_format($bet->payout, 2, '.', ''),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function filters(Request $request): array
    {
        try {
            $from = Carbon::parse($request->input('from', now()->subDays(29)->toDateString()))->startOfDay();
            $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        } catch (\Throwable) {
            $from = now()->subDays(29)->startOfDay();
            $to = now()->endOfDay();
        }

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        if ($from->diffInDays($to) > 92) {
            $from = $to->copy()->subDays(92)->startOfDay();
        }

        $status = in_array($request->input('status'), ['pending', 'won', 'lost', 'void'], true)
            ? $request->input('status')
            : null;

        return [$from, $to, $status];
    }
}
