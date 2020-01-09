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
    /*
     * Tumblr OAuth
     */
    Route::get('login/tumblr', 'Auth\Tumblr@redirectToProvider');
    Route::get('login/tumblr/callback', 'Auth\Tumblr@handleProviderCallback');
});


/*
 * Test
 */
Route::get('test/hello', 'Test@hello');
Route::get('test/api-limits', 'Test@apiLimits');
Route::get('test/syllabes', 'Test@isAlexandrine');
Route::get('test/poem', 'Test@poem');
Route::get('test/last', 'Test@last');
Route::get('test/tumblr', 'Test@tumblr');
Route::get('test/twitter', 'Test@twitter');

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

/*\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
    Log::info( json_encode($query->sql) );
    Log::info( json_encode($query->bindings) );
    Log::info( json_encode($query->time)   );
});*/

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});
