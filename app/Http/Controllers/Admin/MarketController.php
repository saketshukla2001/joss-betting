<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Selection;
use App\Services\BettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MarketController extends Controller
{
    public function store(Request $request, \App\Models\MatchGame $match)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'auto_settle' => ['nullable', 'boolean'],
        ]);

        $match->markets()->create([
            'name' => $data['name'],
            'auto_settle' => $request->boolean('auto_settle'),
            'status' => 'open',
            'sort_order' => (int) $match->markets()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Market added.');
    }

    public function update(Request $request, Market $market)
    {
        if (in_array($market->status, ['settled', 'void'], true)) {
            throw ValidationException::withMessages(['market' => 'Settled markets cannot be edited.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:open,suspended'],
            'auto_settle' => ['nullable', 'boolean'],
        ]);

        $data['auto_settle'] = $request->boolean('auto_settle');
        $market->update($data);

        return back()->with('success', 'Market updated.');
    }

    public function destroy(Market $market)
    {
        if ($market->bets()->exists()) {
            return back()->withErrors(['market' => 'This market has bets and cannot be deleted. Void it instead.']);
        }

        $market->delete();

        return back()->with('success', 'Market deleted.');
    }

    public function storeSelection(Request $request, Market $market)
    {
        if (in_array($market->status, ['settled', 'void'], true)) {
            throw ValidationException::withMessages(['selection' => 'Settled markets cannot be changed.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'odds' => ['required', 'numeric', 'min:1.01', 'max:1000'],
            'result_key' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'in:active,suspended'],
        ]);

        $market->selections()->create([
            'name' => $data['name'],
            'odds' => round((float) $data['odds'], 2),
            'result_key' => $data['result_key'] ?? null,
            'status' => $data['status'] ?? 'active',
            'sort_order' => (int) $market->selections()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Selection added.');
    }

    public function updateSelection(Request $request, Selection $selection)
    {
        if (in_array($selection->market->status, ['settled', 'void'], true)) {
            throw ValidationException::withMessages(['selection' => 'Settled markets cannot be changed.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'odds' => ['required', 'numeric', 'min:1.01', 'max:1000'],
            'result_key' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $data['odds'] = round((float) $data['odds'], 2);
        $selection->update($data);

        return back()->with('success', 'Selection updated.');
    }

    public function destroySelection(Selection $selection)
    {
        if ($selection->bets()->exists()) {
            return back()->withErrors(['selection' => 'This selection has bets and cannot be deleted.']);
        }

        $selection->delete();

        return back()->with('success', 'Selection deleted.');
    }

    public function settle(Request $request, Market $market, BettingService $betting)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:win,void'],
            'winning_selection_id' => ['nullable', 'integer', 'exists:selections,id'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        if ($data['mode'] === 'win') {
            if (empty($data['winning_selection_id'])) {
                throw ValidationException::withMessages(['winning_selection_id' => 'Select the winning selection.']);
            }

            $selection = Selection::whereKey($data['winning_selection_id'])->where('market_id', $market->id)->first();

            if (! $selection) {
                throw ValidationException::withMessages(['winning_selection_id' => 'That selection does not belong to this market.']);
            }

            $count = $betting->settleMarket($market, $selection->id);
        } else {
            $count = $betting->settleMarket($market, null, true);
        }

        return back()->with('success', $count.' bet(s) settled. Wallet payouts and refunds are complete.');
    }
}
