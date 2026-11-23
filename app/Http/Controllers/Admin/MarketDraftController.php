<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketDraft;
use App\Models\MatchGame;
use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Draft-only market configuration. Approving a draft never creates a market,
 * odds or bet — markets remain a manual admin action in the existing screen.
 */
class MarketDraftController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $query = trim($request->string('q')->toString());

        $drafts = MarketDraft::with(['match', 'creator', 'reviewer'])
            ->when(in_array($status, ['draft', 'review', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', '%'.$query.'%')
                        ->orWhere('match_label', 'like', '%'.$query.'%')
                        ->orWhere('provider_match_id', 'like', '%'.$query.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.drafts.index', [
            'drafts' => $drafts,
            'status' => $status,
            'query' => $query,
            'matches' => MatchGame::orderByDesc('start_time')->limit(50)->get(['id', 'home_team', 'away_team']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $providerMatchId = $data['provider_match_id'] ?? null;
        $match = ! empty($data['match_id']) ? MatchGame::find($data['match_id']) : null;

        $duplicate = MarketDraft::where('provider', 'cricbuzz')
            ->where('name', $data['name'])
            ->when($providerMatchId, fn ($q) => $q->where('provider_match_id', $providerMatchId), fn ($q) => $q->where('match_id', $data['match_id'] ?? null))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'A draft with this market name already exists for that match.']);
        }

        $draft = MarketDraft::create([
            'provider' => 'cricbuzz',
            'provider_match_id' => $providerMatchId,
            'match_id' => $data['match_id'] ?? null,
            'match_label' => $match?->label ?? ($data['match_label'] ?? null),
            'name' => $data['name'],
            'selections' => $this->selections($data['selections'] ?? ''),
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        OperationLog::record('MARKET DRAFT CREATED', 'DRAFT '.$draft->name.' FOR '.($draft->match_label ?? 'PROVIDER MATCH '.$draft->provider_match_id), $request->user(), ['draft_id' => $draft->id]);

        return back()->with('success', 'Market draft created. It stays a draft until an admin reviews it.');
    }

    public function update(Request $request, MarketDraft $draft)
    {
        if (! $draft->isEditable()) {
            throw ValidationException::withMessages(['draft' => 'Approved or rejected drafts are locked.']);
        }

        $data = $this->validated($request);
        $providerMatchId = $data['provider_match_id'] ?? $draft->provider_match_id;
        $match = ! empty($data['match_id']) ? MatchGame::find($data['match_id']) : $draft->match;

        $duplicate = MarketDraft::where('provider', 'cricbuzz')
            ->where('name', $data['name'])
            ->where('id', '!=', $draft->id)
            ->when($providerMatchId, fn ($q) => $q->where('provider_match_id', $providerMatchId), fn ($q) => $q->where('match_id', $data['match_id'] ?? $draft->match_id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'Another draft already uses this market name for that match.']);
        }

        $draft->update([
            'provider_match_id' => $providerMatchId,
            'match_id' => $data['match_id'] ?? $draft->match_id,
            'match_label' => $match?->label ?? ($data['match_label'] ?? $draft->match_label),
            'name' => $data['name'],
            'selections' => $this->selections($data['selections'] ?? ''),
            'notes' => $data['notes'] ?? null,
        ]);

        OperationLog::record('MARKET DRAFT UPDATED', 'DRAFT '.$draft->name.' UPDATED', $request->user(), ['draft_id' => $draft->id]);

        return back()->with('success', 'Draft updated.');
    }

    public function review(Request $request, MarketDraft $draft)
    {
        $data = $request->validate(['decision' => ['required', 'in:approve,reject']]);

        if (! $draft->isEditable()) {
            return back()->with('error', 'This draft has already been reviewed.');
        }

        $draft->update([
            'status' => $data['decision'] === 'approve' ? 'approved' : 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        OperationLog::record('MARKET DRAFT REVIEWED', 'DRAFT '.$draft->name.' '.strtoupper($draft->status).' BY ADMIN', $request->user(), ['draft_id' => $draft->id]);

        return back()->with('success', 'Draft '.$draft->status.'. No markets, odds or bets were created.');
    }

    public function destroy(Request $request, MarketDraft $draft)
    {
        if ($draft->status === 'approved') {
            return back()->with('error', 'Approved drafts cannot be deleted.');
        }

        OperationLog::record('MARKET DRAFT DELETED', 'DRAFT '.$draft->name.' DELETED', $request->user(), ['draft_id' => $draft->id]);
        $draft->delete();

        return back()->with('success', 'Draft deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'match_id' => ['nullable', 'integer', 'exists:matches,id'],
            'provider_match_id' => ['nullable', 'string', 'max:40'],
            'match_label' => ['nullable', 'string', 'max:180'],
            'name' => ['required', 'string', 'max:120'],
            'selections' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function selections(string $raw): array
    {
        $lines = preg_split('/[\r\n]+/', $raw) ?: [];
        $lines = array_map(fn ($line) => trim($line), $lines);
        $lines = array_values(array_filter($lines, fn ($line) => $line !== ''));

        return array_values(array_unique(array_slice($lines, 0, 20)));
    }
}
