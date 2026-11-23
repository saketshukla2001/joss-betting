<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationLog extends Model
{
    protected $fillable = ['user_id', 'operation', 'description', 'is_demo', 'meta'];

    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $operation, string $description, ?User $actor = null, array $meta = []): self
    {
        return self::create([
            'user_id' => $actor?->id,
            'operation' => strtoupper($operation),
            'description' => strtoupper($description),
            'is_demo' => false,
            'meta' => $meta,
        ]);
    }
}
