<?php

use Illuminate\Http\Request;

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

Route::post('login', 'Api\Login\LoginController@login');


Route::post('validateEmail', 'Api\Helpers\ValidationController@validateAcount');

# Ruta para establecer tu contraseña inicial
Route::post('updateFirstPass', 'Api\Helpers\ValidationController@updateFirstPassword');

# Olvido de contraseña
Route::post('forgetPass', 'Api\Helpers\ValidationController@forgetPassword');

# Ruta para la validación del cambio de correo electronico
Route::post('validateNewEmail', 'Api\Helpers\ValidationController@validateNewEmail');

Route::middleware('auth:api')->group(function () {
    Route::apiResource('users', 'Api\Administration\UserController');
    # Get(Lista de roles), Get/id(Detalle del rol), Post(Creación del rol), PUT(Actualizar un rol)
    Route::apiResource('role', 'Api\Administration\RoleController');
    Route::apiResource('rolePermission', 'Api\Administration\RolePermissionController');
    # Get(Lista de banners), Get/id(Detalle del banner), Post(Creación del banner), DELETE(Eliminar el banner)
    Route::get('permissions', 'Api\Administration\PermissionsControler@index');

    Route::apiResource('banners', 'Api\Administration\BannerController');
    # Actualizar banner
    Route::post('banners/{id}', 'Api\Administration\BannerController@update');
    Route::get('moduleValidator', 'Api\Administration\MenuController@modulegeneral');

    Route::get('userProfile', 'Api\Administration\ProfileController@userProfile');

    # Cambio de contraseña por el usuario
    Route::post('newPassword', 'Api\Helpers\ValidationController@newPassword');

    # Cerrar sesión
    Route::post('logout', 'Api\Login\LoginController@logout');
});

