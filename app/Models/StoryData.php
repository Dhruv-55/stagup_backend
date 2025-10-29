<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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


// Add accessor to return full URLs
public function getImagesAttribute($value)
{
    $images = is_array($value) ? $value : json_decode($value, true);
    
    if (!is_array($images)) {
        return [];
    }
    
    // Convert paths to full URLs
    return array_map(function($path) {
        // If it's already a full URL, return as is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        // Otherwise, convert storage path to URL
        return Storage::url($path);
        // Or use: asset('storage/' . $path);
    }, $images);
}
}
