<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'task_code',
        'title',
        'description',
        'date',
        'time',
        'counselor_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class);
    }
}
