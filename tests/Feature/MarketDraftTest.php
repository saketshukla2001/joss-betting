<?php

namespace Tests\Feature;

use App\Models\MarketDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketDraftTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'username' => 'admin'.random_int(1000, 9999),
            'email' => 'admin'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function client(): User
    {
        return User::create([
            'name' => 'Client',
            'username' => 'client'.random_int(1000, 9999),
            'email' => 'client'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 100,
        ]);
    }

    public function test_drafts_are_admin_only(): void
    {
        $this->get('/admin/drafts')->assertRedirect('/login');
        $this->actingAs($this->client())->get('/admin/drafts')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/drafts')->assertOk()->assertSee('Market drafts');
    }

    public function test_admin_can_create_a_draft_without_touching_betting_tables(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/drafts', [
            'provider_match_id' => '165061',
            'name' => 'Top Batter',
            'selections' => "India\nAustralia\nDraw",
            'notes' => 'Proposal for review',
        ])->assertRedirect()->assertSessionHas('success');

        $draft = MarketDraft::firstOrFail();

        $this->assertSame('draft', $draft->status);
        $this->assertSame(['India', 'Australia', 'Draw'], $draft->selections);
        $this->assertSame($admin->id, $draft->created_by);

        $this->assertDatabaseCount('markets', 0);
        $this->assertDatabaseCount('selections', 0);
        $this->assertDatabaseCount('bets', 0);
        $this->assertDatabaseHas('operation_logs', ['operation' => 'MARKET DRAFT CREATED']);
    }

    public function test_duplicate_drafts_are_prevented(): void
    {
        $admin = $this->admin();

        $payload = [
            'provider_match_id' => '165061',
            'name' => 'Top Batter',
            'selections' => "India\nAustralia",
        ];

        $this->actingAs($admin)->post('/admin/drafts', $payload)->assertSessionHas('success');
        $this->actingAs($admin)->post('/admin/drafts', $payload)->assertSessionHasErrors('name');

        $this->assertDatabaseCount('market_drafts', 1);
    }

    public function test_approval_records_review_but_creates_no_market(): void
    {
        $admin = $this->admin();
        $draft = MarketDraft::create([
            'provider' => 'cricbuzz',
            'provider_match_id' => '165061',
            'name' => 'Top Batter',
            'selections' => ['India', 'Australia'],
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post("/admin/drafts/{$draft->id}/review", ['decision' => 'approve'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $draft->refresh();

        $this->assertSame('approved', $draft->status);
        $this->assertSame($admin->id, $draft->reviewed_by);
        $this->assertNotNull($draft->reviewed_at);

        $this->assertDatabaseCount('markets', 0);
        $this->assertDatabaseCount('selections', 0);
        $this->assertDatabaseCount('bets', 0);
    }

    public function test_reviewed_drafts_are_locked(): void
    {
        $admin = $this->admin();
        $draft = MarketDraft::create([
            'provider' => 'cricbuzz',
            'provider_match_id' => '165061',
            'name' => 'Top Batter',
            'selections' => ['India'],
            'status' => 'approved',
            'created_by' => $admin->id,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put("/admin/drafts/{$draft->id}", ['name' => 'Changed'])
            ->assertSessionHasErrors('draft');

        $this->assertSame('Top Batter', $draft->fresh()->name);

        $this->actingAs($admin)
            ->delete("/admin/drafts/{$draft->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('market_drafts', ['id' => $draft->id]);
    }
}
