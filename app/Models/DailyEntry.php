<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyEntry extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const DONE = 'done';

    /** Pending for this many days (or more) counts as overdue. */
    public const OVERDUE_AFTER_DAYS = 2;

    protected $fillable = [
        'activity_id',
        'date',
        'status',
        'remark',
        'last_updated_by',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        // `date` stays a plain Y-m-d string so unique(activity_id, date) lookups are exact.
        return ['last_updated_at' => 'datetime'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(EntryUpdate::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDING);
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', self::DONE);
    }

    /** Pending entries from days before today, for active activities. */
    public function scopeCarriedOver(Builder $query): Builder
    {
        return $query->pending()
            ->where('date', '<', today()->toDateString())
            ->whereHas('activity', fn ($q) => $q->active());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->carriedOver()
            ->where('date', '<=', today()->subDays(self::OVERDUE_AFTER_DAYS)->toDateString());
    }

    public static function overdueCount(): int
    {
        return static::overdue()->count();
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function dateCarbon(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->date)->startOfDay();
    }

    /** Whole days this entry has been waiting (0 for today). */
    public function daysWaiting(): int
    {
        return max(0, (int) $this->dateCarbon()->diffInDays(today(), false));
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && $this->daysWaiting() >= self::OVERDUE_AFTER_DAYS;
    }
}
