<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    protected $table = 'policies';

    protected $fillable = [
        'policy_code',
        'slug',
        'title',
        'description',
        'body',
        'counselor_id',
        'counselor_name',
    ];

    public function counselor()
    {
        return $this->belongsTo(Counselor::class);
    }

    /**
     * Standard documents (consent/drugs/appointments) always show first, in
     * that fixed order, since students expect to find them at the top of
     * the Policy folder; everything else follows, newest first.
     */
    public function scopeOrderedForDisplay($query)
    {
        return $query
            ->orderByRaw("case slug when 'consent' then 0 when 'drugs' then 1 when 'appointments' then 2 else 3 end")
            ->orderByDesc('created_at');
    }
}
