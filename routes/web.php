<?php

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


//admin application
use Illuminate\Support\Facades\Auth;

$adminUrl = env('ADMIN_URL');
$chatUrl = env('CHAT_URL');
Route::get('login', 'Admin\LoginController@index');
Route::group(['domain' => $adminUrl ], function()
{
    Route::group(['namespace' => 'Admin', 'middleware' => ['guest']], function () {

        Route::redirect('/', '/login');

        Route::get('login', 'LoginController@index')->name('admin.login');
        Route::post('login', 'LoginController@login');
        Route::get('password/reset', 'LoginController@showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'LoginController@sendResetLinkEmail')->name('password.email');
        Route::get('password/reset/{token}', 'LoginController@showResetForm')->name('password.reset');
        Route::post('password/reset', 'LoginController@reset')->name('password.update');
    });



    Route::group(['namespace' => 'Admin', 'middleware' => ['auth.admin']], function () {

        Route::get('/', 'HomeController@index')->name('admin.home');

        Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');

        Route::resource('permissions', 'PermissionsController');

        Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');

        Route::resource('roles', 'RolesController');

        Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');

        Route::resource('users', 'UsersController');

        Route::delete('products/destroy', 'ProductsController@massDestroy')->name('products.massDestroy');

        Route::resource('products', 'ProductsController');

        Route::post('logout', 'LoginController@logout')->name('admin.logout');


        Route::get('chat', 'ChatController@index')->name('admin.chat');
        Route::delete('room/destroy', 'ChatController@massDestroyRoom')->name('admin.chatRoom.massDestroy');

    });
});

Route::any('{all}', function () {
    return abort(404);
})->where(['all' => '.*']);



