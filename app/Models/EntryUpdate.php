<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Append-only audit log. Rows are never updated or deleted (also enforced by DB triggers).
 */
class EntryUpdate extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'daily_entry_id',
        'user_id',
        'user_name',
        'user_staff_id',
        'old_status',
        'new_status',
        'remark',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('entry_updates is append-only.'));
        static::deleting(fn () => throw new LogicException('entry_updates is append-only.'));
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class, 'daily_entry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompletion(): bool
    {
        return $this->new_status === DailyEntry::DONE && $this->old_status !== DailyEntry::DONE;
    }
}
