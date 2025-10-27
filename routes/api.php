<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GenrealPurposeController;
use App\Http\Controllers\Organizer\VenueController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\HomeController;

Route::get('login', function () {
    return response()->json([
        'redirect_url' => env('FRONTEND_URL')
    ]);
});


Route::group([ 'prefix' => 'auth', 'controller' => AuthController::class ], function () {
    Route::get('username-exists', 'usernameExistsOrNot');
    Route::post('login', 'login');
    Route::post('register', 'register');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::group([ 'prefix' => 'auth', 'controller' => AuthController::class ], function () { 
        Route::get('logout', 'logout');
    });

    Route::group([ 'prefix' => 'profile', 'controller' => ProfileController::class ], function () {
        Route::get('index', 'index');
        Route::post('update', 'update');
        Route::get('data/{user_id?}', 'profileData');
        Route::get('posts/{user_id?}', 'posts');
        Route::get('followers/{user_id?}', 'followersList');
        Route::get('following/{user_id?}', 'followingList');
        Route::get('suggestions', 'suggestionList');
    });

    Route::group([ 'prefix' => 'general', 'controller' => GenrealPurposeController::class ], function () {
        Route::get('search-user', 'searchUser');
        Route::post("follow",'followUser');
        Route::post("unfollow",'unfollowUser');
        Route::post("likeOrDislike",'likeOrDislike');
        Route::get("explore",'explore');
    });   
    
    
    Route::group([ 'prefix' => 'venue', 'controller' => VenueController::class ], function () {
        Route::get('/', 'venues');
        Route::post('add', 'venueAdd');
        Route::get('edit/{id}', 'venueEdit');
        Route::put('update/{id}', 'venueUpdate');
        Route::delete('delete/{id}', 'venueDelete');
    });   

    Route::group([ 'prefix' => 'event', 'controller' => EventController::class ], function () {
        Route::get('/', 'events');
        Route::post('add', 'eventAdd');
        Route::get('edit/{id}', 'eventEdit');
        Route::post('update/{id}', 'eventUpdate');
        Route::delete('delete/{id}', 'eventDelete');
    });   


    Route::group([ 'prefix' => 'home', 'controller' => HomeController::class ], function () {
        Route::get('/', 'index');
    });

    Route::group([ 'prefix' => 'story', 'controller' => StoryController::class ], function () {
        Route::post('add-or-update', 'addOrUpdate');
        Route::get('load-stories', 'loadStories');
    });

    
});
