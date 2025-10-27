<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryData extends Model
{
    protected $fillable = [
        'story_id',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
