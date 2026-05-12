<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait SoftDeletable
{
    public function softDelete(): static
    {
        $this->update([
            'status' => 'deleted',
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);
        return $this;
    }

    public function restore(): static
    {
        $this->update([
            'status' => 'active',
            'deleted_by' => null,
            'deleted_at' => null,
        ]);
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->status === 'deleted';
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('status', '!=', 'deleted');
    }

    public function scopeSoftDeleted($query)
    {
        return $query->where('status', 'deleted');
    }

    public function scopeWithDeleted($query)
    {
        return $query;
    }
}
