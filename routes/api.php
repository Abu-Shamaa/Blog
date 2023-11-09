<?php

use App\Http\Controllers\Articles\ArticleController;
use App\Http\Controllers\Articles\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Groups\GroupController;
use App\Http\Controllers\Media\MediaController;
use App\Http\Controllers\Quiz\CourseController;
use App\Http\Controllers\Quiz\InstructorController;
use App\Http\Controllers\Quiz\LevelController;
use App\Http\Controllers\Quiz\QuestionController;
use App\Http\Controllers\Quiz\QuizattemptController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\Quiz\ResultController;
use App\Http\Controllers\Quiz\TopicController;
use App\Http\Controllers\Quiz\UserCourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::put('/change/password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::get('/users/{email}', [AuthController::class, 'checkUser']);
Route::get('/category', [CategoryController::class, 'catIndex']);
// ******ADMIN *******

//Articles
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/article/{slug}', [ArticleController::class, 'checkSlug']);
    Route::get('/articles/trash', [ArticleController::class, 'trash'])
        ->middleware('can:aa_staff2');
    Route::post('/add/article', [ArticleController::class, 'store'])
        ->middleware('can:aa_staff1');
    Route::post('/slug/create', [ArticleController::class, 'slugCreate'])
        ->middleware('can:aa_staff1');
    Route::get('/edit/articles/{id}', [ArticleController::class, 'edit'])
        ->middleware('can:aa_staff2');
    Route::put('/update/articles/{id}', [ArticleController::class, 'update'])
        ->middleware('can:aa_staff1');
    Route::delete('/delete/articles/{id}', [ArticleController::class, 'destroy'])
        ->middleware('can:aa_staff1');
    Route::get('/restore/articles/{id}', [ArticleController::class, 'restore'])
        ->middleware('can:aa_staff1');
    Route::delete('/forceDelete/articles/{id}', [ArticleController::class, 'forceDelete'])
        ->middleware('can:aa_mgmt');
    Route::put('/appropve/articles/{id}', [ArticleController::class, 'approveArticle'])
        ->middleware('can:aa_mgmt');
});

// Media
Route::middleware('auth:sanctum')->prefix('media')->namespace('Media')->group(function () {
    Route::get('/', [MediaController::class, 'index']);
    Route::get('/{media}', [MediaController::class, 'show']);
    Route::post('/', [MediaController::class, 'store']);
    Route::delete('/{media}', [MediaController::class, 'destroy']);
});

//category
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/add/category', [CategoryController::class, 'catStore']);

    Route::get('/show/category/{id}', [CategoryController::class, 'catShow']);

    Route::put('/update/category/{id}', [CategoryController::class, 'catUpdate']);

    Route::delete('/delete/category/{id}', [CategoryController::class, 'catDestroy']);
    Route::get('/category/{slug}', [CategoryController::class, 'checkSlug']);
    Route::post('/category/slug', [CategoryController::class, 'slugCreate']);
});

//groups
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/groups', [GroupController::class, 'gindex']);
    Route::post('/add/group', [GroupController::class, 'gstore']);

    Route::get('/edit/groups/{id}', [GroupController::class, 'gedit']);

    Route::put('/update/groups/{id}', [GroupController::class, 'gupdate']);

    Route::delete('/delete/groups/{id}', [GroupController::class, 'gdestroy']);
});


Route::get('/user/articles', [ArticleController::class, 'articlesIndex']);

Route::get('/show/articles/{slug}', [ArticleController::class, 'show']);

