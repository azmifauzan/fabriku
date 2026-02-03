<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleLog extends Model
{
    protected $fillable = [
        'command',
        'status',
        'output',
        'error',
        'started_at',
        'finished_at',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function scopeCommand(Builder $query, string $command): Builder
    {
        return $query->where('command', $command);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function start(string $command): self
    {
        return self::create([
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markCompleted(?string $output = null): void
    {
        $this->update([
            'status' => 'completed',
            'output' => $output,
            'finished_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
            'finished_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }
}
