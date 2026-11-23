<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'method', 'account_name', 'account_number',
        'ifsc', 'upi_id', 'status', 'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'upi' => 'UPI',
            'bank' => 'Bank Transfer',
            'usdt' => 'USDT',
            default => strtoupper($this->method),
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'muted',
        };
    }
}
