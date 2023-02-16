<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::resource('folders', 'Admin\FoldersController');
Route::post('folders_mass_destroy', ['uses' => 'Admin\FoldersController@massDestroy', 'as' => 'folders.mass_destroy']);
Route::post('folders_restore/{id}', ['uses' => 'Admin\FoldersController@restore', 'as' => 'folders.restore']);
Route::delete('folders_perma_del/{id}', ['uses' => 'Admin\FoldersController@perma_del', 'as' => 'folders.perma_del']);
Route::resource('files', 'Admin\FilesController');
Route::get('/{id}/download', 'Admin\DownloadsController@download');
Route::post('files_mass_destroy', ['uses' => 'Admin\FilesController@massDestroy', 'as' => 'files.mass_destroy']);
Route::post('files_restore/{id}', ['uses' => 'Admin\FilesController@restore', 'as' => 'files.restore']);
Route::delete('files_perma_del/{id}', ['uses' => 'Admin\FilesController@perma_del', 'as' => 'files.perma_del']);


require __DIR__.'/auth.php';
