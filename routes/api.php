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

# Ruta para obtener la lista de países
Route::get('countries', 'Api\Free\ListsFreeController@country');

# Ruta para obtener la lista de países
Route::get('currencies', 'Api\Free\ListsFreeController@currency');

# Olvido de contraseña
Route::post('forgetPass', 'Api\Helpers\ValidationController@forgetPassword');

# Ruta para la validación del cambio de correo electronico
Route::post('validateNewEmail', 'Api\Helpers\ValidationController@validateNewEmail');

Route::post('getOrderStatus', 'Api\Administration\ExternalConnections\PayURequestController@getPaymentState');

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

    # Valor minimo de el costo de envio
    Route::get('cityDeliveryFee', 'Api\Client\ClientsController@deliveryFeeClient');

    # Lista de estados de la orden
    Route::get('orderStateList', 'Api\Client\ClientsController@orderStateId');

    # Vlidar existencia de los productos
    Route::post('validateProductExistence', 'Api\Client\ClientsController@validateProductExistence');

    # Validar cupones (TEST)
    Route::post('validateCupon/{code}', 'Api\Client\ClientsController@validateCupon');

    # Lista de tutoriales
    Route::get('tutorialsList', 'Api\Client\ClientsController@tutorialsList');

    # Solicitar orden
    Route::post('orders', 'Api\Client\ClientsController@requestOrder');

    # Detalle de tutoriales
    Route::get('tutorialsList/{id}', 'Api\Client\ClientsController@tutorialsDetail');

    # Crear PQRS
    Route::post('clientPQRS', 'Api\Client\ClientsController@pqrsClient');

    # Validar si tiene oferta de primera compra
    Route::post('validateOffer', 'Api\Client\ClientsController@validateSubcriber');

    # Lista de tipos de pqrs
    Route::get('pqrsType', 'Api\Client\ClientsController@pqrsType');


});


Route::middleware('auth:api')->group(function () {
    Route::apiResource('users', 'Api\Administration\UserController');
    # Get(Lista de roles), Get/id(Detalle del rol), Post(Creación del rol), PUT(Actualizar un rol)
    Route::apiResource('role', 'Api\Administration\RoleController');
    Route::apiResource('rolePermission', 'Api\Administration\RolePermissionController');
    # Get(Lista de banners), Get/id(Detalle del banner), Post(Creación del banner), DELETE(Eliminar el banner)
    Route::get('permissions', 'Api\Administration\PermissionsControler@index');
    Route::apiResource('cupons', 'Api\Administration\CuponsController');
    Route::apiResource('tutorials', 'Api\Administration\TutorialsController');
    Route::post('tutorials/{id}', 'Api\Administration\TutorialsController@Update');

    Route::apiResource('banners', 'Api\Administration\BannerController');

    # Administración de banners por categorías
    Route::apiResource('bannersByCategory', 'Api\Administration\BannersByCategoryController');
    # Actualizar banner
    Route::post('banners/{id}', 'Api\Administration\BannerController@update');
    # Actualizar banner por categorías
    Route::post('bannersByCategory/{id}', 'Api\Administration\BannersByCategoryController@update');

    # Administración de vídeos de el home
    Route::apiResource('videosHome', 'Api\Administration\VideosHomeController');

    # Administración de ciudades
    Route::apiResource('cities', 'Api\Administration\CitiesController');

    # Actualizar el minimo de costo de el envio
    Route::put('deliveryFee', 'Api\Administration\CitiesController@deliveryFee');

    # Ver el minimo de costo de el envio
    Route::get('deliveryFeeGet', 'Api\Administration\CitiesController@deliveryFeeGet');

    # Administración de transportadoras
    Route::apiResource('transportationCompanies', 'Api\Administration\TransportationCompaniesController');

    Route::get('moduleValidator', 'Api\Administration\MenuController@modulegeneral');
    # Pedidos
    Route::apiResource('orders', 'Api\Administration\OrdersController')->only(['index', 'show', 'update']);

    Route::get('orderState', 'Api\Administration\OrdersController@orderStatesList');

    # Colocar estado de entregado de la oreden
    Route::put('orderDelivered/{id}', 'Api\Administration\OrdersController@orderDelivered');

    # Colocar estado de devuelto de la oreden
    Route::put('orderReturn/{id}', 'Api\Administration\OrdersController@orderReturn');

    # Categorías Nivel 1
    Route::apiResource('categories1', 'Api\Administration\Categories1Controller');

    # Ofertas
    Route::apiResource('offers', 'Api\Administration\OffersController');

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

    # PQRS
    Route::apiResource('pqrsAdmin', 'Api\Administration\PQRSController');

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

