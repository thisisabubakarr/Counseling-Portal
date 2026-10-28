<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedSlot extends Model
{
    protected $fillable = [
        'blocked_code',
        'counselor_id',
        'date',
        'slot',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'counselor_id' => 'integer',
            'date' => 'date',
        ];
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class);
    }
}
