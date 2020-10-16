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

# Ruta para obtener la lista de idiomas
Route::get('languages', 'Api\Free\ListsFreeController@language');

# Olvido de contraseña
Route::post('forgetPass', 'Api\Helpers\ValidationController@forgetPassword');

# Ruta para la validación del cambio de correo electronico
Route::post('validateNewEmail', 'Api\Helpers\ValidationController@validateNewEmail');


# Rutas para el cliente
Route::middleware('clint')->group(function () {
    # Lista de categorías
    Route::get('categoriesList', 'Api\Client\ClientsController@categoriesList');
    # Lista de categorías 1
    Route::get('categoriesList/{id}', 'Api\Client\ClientsController@categories2List');
    # Lista de productos
    Route::get('productsList', 'Api\Client\ClientsController@productsList');

    # Min y Max de precios
    Route::get('minAndMax', 'Api\Client\ClientsController@minAndMax');

    # Detalle de productos
    Route::get('productsList/{id}', 'Api\Client\ClientsController@productsListDetail');

    # Lista de marcas
    Route::get('brandsList', 'Api\Client\ClientsController@brandsList');

    # Lista de banners
    Route::get('bannersList', 'Api\Client\ClientsController@bannersList');

    # Lista de videos
    Route::get('videoHomeList', 'Api\Client\ClientsController@videoHomeList');

    # Suscribirse
    Route::post('clientEmail', 'Api\Client\ClientsController@clientEmail');

    # Lista de banners
    Route::get('bannersByCategoryList/{id}', 'Api\Client\ClientsController@bannersByCatgoryList');

    # Lista de ciudades
    Route::get('citiesList', 'Api\Client\ClientsController@citiesList');

});


Route::middleware('auth:api')->group(function () {
    Route::apiResource('users', 'Api\Administration\UserController');
    # Get(Lista de roles), Get/id(Detalle del rol), Post(Creación del rol), PUT(Actualizar un rol)
    Route::apiResource('role', 'Api\Administration\RoleController');
    Route::apiResource('rolePermission', 'Api\Administration\RolePermissionController');
    # Get(Lista de banners), Get/id(Detalle del banner), Post(Creación del banner), DELETE(Eliminar el banner)
    Route::get('permissions', 'Api\Administration\PermissionsControler@index');

    Route::apiResource('banners', 'Api\Administration\BannerController');

    # Administración de banners por categorías
    Route::apiResource('bannersByCategory', 'Api\Administration\BannersByCategoryController');
    # Actualizar banner
    Route::post('banners/{id}', 'Api\Administration\BannerController@update');
    # Actualizar banner por categorías
    Route::post('bannersByCategory/{id}', 'Api\Administration\BannersByCategoryController@update');

    # Administración de vídeos de el home
    Route::apiResource('videosHome', 'Api\Administration\VideosHomeController');

    Route::get('moduleValidator', 'Api\Administration\MenuController@modulegeneral');
    # Categorías Nivel 1
    Route::apiResource('categories1', 'Api\Administration\Categories1Controller');

    # Categorías Nivel 2
    Route::apiResource('categories2', 'Api\Administration\Categories2Controller');

    # Categorías Nivel 2 Update image
    Route::post('categories2/{id}', 'Api\Administration\Categories2Controller@update');

    # Categorías Nivel 3
    Route::apiResource('categories3', 'Api\Administration\Categories3Controller');

    # Marcas
    Route::post('brands/{id}', 'Api\Administration\BrandsController@update');
    Route::apiResource('brands', 'Api\Administration\BrandsController');

    # Productos
    Route::apiResource('products', 'Api\Administration\ProductsController');
    # Suscriciones
    Route::apiResource('subscribers', 'Api\Administration\SubscribersController');
    Route::post('products/{id}', 'Api\Administration\ProductsController@update');
    Route::get('masterProducts/{id}', 'Api\Administration\ProductsController@showMaster');
    Route::put('masterProducts/{id}', 'Api\Administration\ProductsController@updateMaster');

    Route::post('orderProducts', 'Api\Administration\ProductsController@orderProducts');
    Route::get('variantsByCategory', 'Api\Administration\ProductsController@variantListCategory');


    # Listar variantes de el producto
    Route::get('productVariants/{id}', 'Api\Administration\ProductsController@variantList');

    # Crear variante de el producto
    Route::post('productVariants', 'Api\Administration\ProductsController@productVariants');

    Route::get('userProfile', 'Api\Administration\ProfileController@userProfile');

    # Cambio de contraseña por el usuario
    Route::post('newPassword', 'Api\Helpers\ValidationController@newPassword');

    # Cerrar sesión
    Route::post('logout', 'Api\Login\LoginController@logout');
});

