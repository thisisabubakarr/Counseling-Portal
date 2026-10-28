<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'news_code',
        'title',
        'body',
        'event_date',
        'counselor_id',
        'counselor_name',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'published' => 'boolean',
        ];
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class);
    }
}
