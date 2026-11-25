<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['name', 'value'];

    protected static ?array $cache = null;

    public const DEFAULTS = [
        'site_name' => 'BetZone',
        'tagline' => 'Sports Betting Exchange',
        'currency' => '₹',
        'min_stake' => '100',
        'max_stake' => '100000',
        'signup_bonus' => '500',
        'support_email' => 'support@betzone.test',
        'announcement' => 'Welcome to BetZone — live sports betting with instant wallet payouts.',
        'odds_auto_move' => '1',
        'min_deposit' => '500',
        'min_withdrawal' => '100',
        'maintenance_message' => '',
        'rules' => 'Bet responsibly. 18+ only. All bets are subject to market settlement and platform rules.',
        'panel_primary' => '#1e6b1e',
        'panel_primary_dark' => '#155315',
        'panel_primary_mid' => '#2a7a2a',
        'panel_accent' => '#ffb74d',
        'panel_soft' => '#eef5ee',

        // Cricket / live visibility controls
        'live_only_sport' => 'cricket',
        'live_stale_hours' => '6',
        'cricket_feed_enabled' => '1',
        'cricket_board_enabled' => '1',
        'cricket_discovery_mode' => '1',
        'cricket_fallback_ids' => '',
        'cricket_hidden_match_ids' => '',
        'cricket_stale_minutes' => '15',
        'cricket_detail_limit' => '6',
        'cricket_discovery_ttl' => '120',
        'cricket_live_limit' => '10',
        'cricket_upcoming_limit' => '8',
        'cricket_recent_limit' => '12',
        'legacy_opencricket_enabled' => '0',

        // Live cricket Yes/No markets. Off until an admin turns them on.
        'cricket_ai_markets_enabled' => '0',
        'cricket_ai_approval_required' => '0',
        'cricket_ai_use_model' => '0',
        'cricket_ai_generation_seconds' => '60',
        'cricket_ai_yes_odds' => '1.85',
        'cricket_ai_no_odds' => '1.85',
        'cricket_ai_templates' => 'runs_by_over,over_runs,wicket_in_over',
        'cricket_sportmonks_enabled' => '0',
    ];

    public static function allCached(): array
    {
        if (static::$cache === null) {
            try {
                static::$cache = static::query()->pluck('value', 'name')->all();
            } catch (Throwable) {
                static::$cache = [];
            }
        }

        return static::$cache;
    }

    public static function get(string $name, mixed $default = null): mixed
    {
        $all = static::allCached();

        if (array_key_exists($name, $all) && $all[$name] !== null && $all[$name] !== '') {
            return $all[$name];
        }

        return $default ?? (static::DEFAULTS[$name] ?? null);
    }

    public static function set(string $name, mixed $value): void
    {
        static::updateOrCreate(['name' => $name], ['value' => $value === null ? null : (string) $value]);
        static::$cache = null;
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
