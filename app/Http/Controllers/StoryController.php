<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
public function addOrUpdate(Request $request)
{
    // Validate the request
    $validation = Validator::make($request->all(), [
        'images' => 'required|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB max
    ]);

    if ($validation->fails()) {
        return ResponseHelper::error($validation->errors()->first());
    }

    $story = Story::where('user_id', $request->user()->id)
        ->whereDate('created_at', Carbon::today())
        ->first();

    if ($story) {
        return $this->update($request, $story);
    } else {
        return $this->add($request);
    }
}

protected function add(Request $request)
{
    $story = Story::create([
        'user_id' => $request->user()->id,
    ]);

    $imagesPath = $this->saveUploadedImages($request->file('images'));

    if (!empty($imagesPath)) {
        StoryData::create([
            'story_id' => $story->id,
            'images' => $imagesPath,
        ]);
    }

    return ResponseHelper::success('Story added successfully');
}

protected function update(Request $request, $story)
{
    $story->update([
        'updated_at' => now(),
    ]);

    $newImages = $this->saveUploadedImages($request->file('images'));

    if (!empty($newImages)) {
        $storyData = StoryData::where('story_id', $story->id)->first();

        if ($storyData) {
            // Get existing images as array
            $existingImages = is_array($storyData->images)
                ? $storyData->images
                : json_decode($storyData->images, true) ?? [];

            // Merge old + new
            $mergedImages = array_merge($existingImages, $newImages);

            // Update with merged images
            $storyData->update([
                'images' => $mergedImages,
            ]);
        } else {
            StoryData::create([
                'story_id' => $story->id,
                'images' => $newImages,
            ]);
        }
    }

    return ResponseHelper::success('Story updated successfully');
}

// FIX THIS METHOD - This is likely where the problem is
protected function saveUploadedImages($images)
{
    $imagePaths = [];

    foreach ($images as $image) {
        // Save to storage/app/public/stories instead of storage/stories
        $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        
        // Use 'public' disk - this saves to storage/app/public/stories
        $path = $image->storeAs('stories', $filename, 'public');
        

        // Store only the relative path (stories/filename.webp)
        // NOT the full URL
        $imagePaths[] = env('APP_URL')."storage/".$path;
    }

    return $imagePaths;
}
    /**
     * Save uploaded images to storage
     * 
     * @param array $images Array of UploadedFile objects
     * @return array Array of image URLs
     */

    /**
     * Save a single uploaded image
     * 
     * @param \Illuminate\Http\UploadedFile $image
     * @return string Image URL
     */
   
    /**
     * Delete a story (optional - for cleanup)
     */
    public function deleteStory(Request $request, $storyId)
    {
        $story = Story::where('id', $storyId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$story) {
            return ResponseHelper::error('Story not found', 404);
        }

        // Get story data to delete images
        $storyData = StoryData::where('story_id', $story->id)->first();
        
        if ($storyData && !empty($storyData->images)) {
            $images = is_array($storyData->images) 
                ? $storyData->images 
                : json_decode($storyData->images, true) ?? [];

            // Delete each image file
            foreach ($images as $imageUrl) {
                $path = str_replace(asset('storage/'), '', $imageUrl);
                Storage::disk('public')->delete($path);
            }

            $storyData->delete();
        }

        $story->delete();

        return ResponseHelper::success('Story deleted successfully');
    }

    /**
     * Load all stories
     */
    public function loadStories(Request $request)
    {
        $stories = Story::with(['user.profile', 'storyData','user.followers'])
            ->whereDate('created_at', Carbon::today())
            ->where('user_id', '!=', $request->user()->id) 
            ->orWhere(function($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                      ->whereDate('created_at', Carbon::today());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseHelper::success( $stories);
    }



    // public function loadStories(Request $request)
    // {
    //     $stories = Story::whereDate("created_at", Carbon::today())->with('user.profile','user.following','storyData')
    //     ->whereHas('user.following', function ($query) use ($request) {
    //         $query->where('following_id', $request->user()->id);
    //     })
    //     ->orWhere('user_id', $request->user()->id)
    //     ->get();
    //     return ResponseHelper::success($stories);
    // }
}
