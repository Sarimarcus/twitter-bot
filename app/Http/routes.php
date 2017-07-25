<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'Dashboard@homepage');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});


/*
 * Test
 */
Route::get('test/hello', 'Test@hello');
Route::get('test/api-limits', 'Test@apiLimits');
Route::get('test/syllabes', 'Test@isAlexandrine');
Route::get('test/poem', 'Test@poem');
Route::get('test/last', 'Test@last');

/*
 * Stats
 */
Route::get('stats', 'Stats@test');

/*
 * Wurstify
 */
Route::get('wurstify', 'Wurstify@make');

/*
 * Poem Bundler
 */
Route::get('poem', 'PoemBundler@index');
Route::get('poem/next/{limit}', 'PoemBundler@next');
//Route::get('poem-create', 'PoemBundler@create');

/*
 * Tumblr OAuth
 */
Route::get('login/tumblr', 'Auth\Tumblr@redirectToProvider');
Route::get('login/tumblr/callback', 'Auth\Tumblr@handleProviderCallback');