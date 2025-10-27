<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helper\ResponseHelper;
use App\Models\Story;
use App\Models\StoryData;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function addOrUpdate(Request $request)
    {
        if ($story = Story::where('user_id', $request->user()->id)->whereDate('created_at', Carbon::today())->first()) {
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

        $imagesPath = $this->saveBase64Images($request->images);

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
            'user_id' => $request->user()->id,
        ]);

        $newImages = $this->saveBase64Images($request->images);

        if (!empty($newImages)) {
            $storyData = StoryData::where('story_id', $story->id)->first();

            if ($storyData) {
                // Get existing images as array
                $existingImages = is_array($storyData->images)
                    ? $storyData->images
                    : $storyData->images;

                if (!is_array($existingImages)) {
                    $existingImages = [];
                }

                // Merge old + new
                $mergedImages = array_merge($existingImages, $newImages);

                // Save with unescaped slashes
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


    private function saveBase64Images($images)
    {
        $paths = [];

        if (is_array($images)) {
            foreach ($images as $img) {
                if (isset($img['data'])) {
                    $paths[] = $this->saveBase64Image($img['data']);
                }
            }
        }

        return $paths;
    }

    private function saveBase64Image($base64Image)
    {
        // extract mime type
        preg_match('/data:image\/(\w+);base64,/', $base64Image, $matches);
        $extension = isset($matches[1]) ? $matches[1] : 'png';

        $image = str_replace('data:image/' . $extension . ';base64,', '', $base64Image);
        $image = str_replace(' ', '+', $image);

        $fileName = 'stories/' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($fileName, base64_decode($image));

        return asset('storage/' . $fileName);
    }


    public function loadStories(Request $request)
    {
        $stories = Story::whereDate("created_at", Carbon::today())->with('user.profile','user.following')
        ->whereHas('user.following', function ($query) use ($request) {
            $query->where('following_id', $request->user()->id);
        })
        ->orWhere('user_id', $request->user()->id)
        ->get();
        return ResponseHelper::success($stories);
    }
}
