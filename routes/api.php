<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\ContentDiscoveryController;
use App\Http\Controllers\MLDataController;
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\VideoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Content Service Routes with /content prefix
Route::prefix('content')->group(function () {

    // Exercise Management Routes
    Route::controller(ExerciseController::class)->group(function () {
        Route::get('/exercises', 'index');                                    // GET /content/exercises
        Route::get('/exercises/{id}', 'show');                               // GET /content/exercises/{id}
        Route::get('/exercise-attributes/{id}', 'getExerciseAttributes');    // GET /content/exercise-attributes/{id}
        Route::post('/exercises', 'store');                                  // POST /content/exercises
        Route::put('/exercises/{id}', 'update');                             // PUT /content/exercises/{id}
    });

    // Workout Management Routes
    Route::controller(WorkoutController::class)->group(function () {
        Route::get('/workouts/{difficulty}/{muscleGroup}', 'getFilteredWorkouts');  // GET /content/workouts/{difficulty}/{muscleGroup}
        Route::get('/workouts/search', 'searchWorkouts');                           // GET /content/workouts/search (search by criteria)
        Route::get('/workout/{id}', 'show');                                        // GET /content/workout/{id}
        Route::post('/workouts', 'store');                                          // POST /content/workouts
    });

    // Content Discovery Routes
    Route::controller(ContentDiscoveryController::class)->group(function () {
        Route::get('/exercises/by-muscle-group/{group}', 'getExercisesByMuscleGroup');  // GET /content/exercises/by-muscle-group/{group}
        Route::get('/exercises/by-difficulty/{level}', 'getExercisesByDifficulty');     // GET /content/exercises/by-difficulty/{level}
        Route::get('/search', 'searchContent');                                         // GET /content/search
    });

    // ML Data Endpoints
    Route::controller(MLDataController::class)->group(function () {
        Route::get('/all-exercises', 'getAllExercises');                           // GET /content/all-exercises
        Route::get('/exercise-features/{id}', 'getExerciseFeatures');             // GET /content/exercise-features/{id}
        Route::get('/exercise-similarity-data', 'getExerciseSimilarityData');     // GET /content/exercise-similarity-data
        Route::post('/exercise-similarity', 'calculateExerciseSimilarity');       // POST /content/exercise-similarity
    });

    // Instruction & Media Routes
    Route::controller(InstructionController::class)->group(function () {
        Route::get('/exercise-instructions/{id}', 'getExerciseInstructions');     // GET /content/exercise-instructions/{id}
        Route::post('/exercises/{id}/instructions', 'addExerciseInstructions');   // POST /content/exercises/{id}/instructions
        Route::put('/instructions/{id}', 'updateInstruction');                    // PUT /content/instructions/{id}
        Route::delete('/instructions/{id}', 'deleteInstruction');                 // DELETE /content/instructions/{id}
    });

    Route::controller(VideoController::class)->group(function () {
        Route::get('/exercise-videos/{id}', 'getExerciseVideos');                 // GET /content/exercise-videos/{id}
        Route::post('/exercises/{id}/videos', 'linkExerciseWithVideo');           // POST /content/exercises/{id}/videos
        Route::put('/videos/{id}', 'updateVideo');                                // PUT /content/videos/{id}
        Route::delete('/videos/{id}', 'deleteVideo');                             // DELETE /content/videos/{id}
    });
});
