<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $amount = (float) $amount;
        $sign = $amount < 0 ? '−' : '';
        $symbol = $withSymbol ? (string) setting('currency', '₹') : '';

        return $sign.$symbol.number_format(abs($amount), 2);
    }
}

if (! function_exists('short_money')) {
    function short_money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $sign = $amount < 0 ? '−' : '';
        $abs = abs($amount);

        $formatted = match (true) {
            $abs >= 10000000 => number_format($abs / 10000000, 2).'Cr',
            $abs >= 100000 => number_format($abs / 100000, 2).'L',
            $abs >= 1000 => number_format($abs / 1000, 1).'K',
            default => number_format($abs, 0),
        };

        return $sign.setting('currency', '₹').$formatted;
    }
}

if (! function_exists('odds')) {
    function odds(float|int|string|null $value): string
    {
        return number_format((float) $value, 2);
    }
}

if (! function_exists('live_only_sport')) {
    function live_only_sport(): string
    {
        $value = (string) setting('live_only_sport', 'cricket');

        return in_array($value, ['all', 'cricket'], true) ? $value : 'cricket';
    }
}

if (! function_exists('live_only_cricket')) {
    function live_only_cricket(): bool
    {
        return live_only_sport() === 'cricket';
    }
}

if (! function_exists('setting_enabled')) {
    function setting_enabled(string $key, bool $default = true): bool
    {
        $value = setting($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }
}
