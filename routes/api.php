<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\AgreementController;
use App\Http\Controllers\Api\ComponentController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DepartmentAgreementController;
use App\Http\Controllers\Api\IscedController;
use App\Http\Controllers\Api\PartnerCountryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventThemeController;
use App\Http\Controllers\Api\AdministrationController;
use App\Http\Controllers\Api\FavorisController;
use App\Http\Controllers\Api\WishAgreementController;
use App\Http\Controllers\Api\AcceptedAccountController;
use App\Http\Controllers\Api\DocumentsController;
use App\Http\Controllers\Api\ArbitrageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\DataBaseController;

/** Routes article */
Route::get('/article', [ArticleController::class, 'index']);
Route::get('/article/getbyid/{id}', [ArticleController::class, 'getById']);
Route::put('/article', [ArticleController::class, 'put']);
Route::post('/article', [ArticleController::class, 'store']);
Route::delete('/article/deletebyid/{id}', [ArticleController::class, 'deleteById']);
Route::get('/article/image/{id}', [ArticleController::class, 'getImageById']);

/** Routes action */
Route::get('/action', [ActionController::class, 'index']);
Route::get('/action/getbyid/{id}', [ActionController::class, 'getById']);
Route::get('/action/getbylogin/{login}', [ActionController::class, 'getByLogin']);
Route::post('/action', [ActionController::class, 'store']);
Route::delete('/action', [ActionController::class, 'delete']);

/** Routes agreement */
Route::get('/agreement', [AgreementController::class, 'index']);
Route::get('/agreement/random', [AgreementController::class, 'random']);
Route::get('/agreement/getbyid/{id}', [AgreementController::class, 'getById']);
Route::post('/agreement', [AgreementController::class, 'store']);
Route::put('/agreement/{id}', [AgreementController::class, 'update']);
Route::delete('/agreement/deletebyid/{id}', [AgreementController::class, 'deleteById']);
Route::get('/agreement/export', [AgreementController::class, 'export']);

/** Routes component */
Route::get('/component', [ComponentController::class, 'index']);
Route::post('/component', [ComponentController::class, 'store']);
Route::get('/component/getbyid/{id}', [ComponentController::class, 'getById']);
Route::delete('/component/deletebyid/{id}', [ComponentController::class, 'deleteById']);
Route::put('/component', [ComponentController::class, 'put']);

/** Routes department */
Route::get('/department', [DepartmentController::class, 'index']);
Route::get('/department/getbyid/{id}', [DepartmentController::class, 'getById']);
Route::post('/department', [DepartmentController::class, 'store']);
Route::delete('/department/deletebyid/{id}', [DepartmentController::class, 'deleteById']);
Route::put('/department', [DepartmentController::class, 'put']);
Route::get('/department/export', [DepartmentController::class, 'export']);

/** Routes departmentagreement */
Route::get('/departmentagreement', [DepartmentAgreementController::class, 'index']);
Route::get('/departmentagreement/getbyid/{id}', [DepartmentAgreementController::class, 'getById']);
Route::post('/departmentagreement', [DepartmentAgreementController::class, 'store']);
Route::delete('/departmentagreement/delete/{agree_id}/{dept_id}', [DepartmentAgreementController::class, 'delete']);
Route::put('/departmentagreement/changevisibility', [DepartmentAgreementController::class, 'changeVisibilityDept']);

/** Routes isced */
Route::get('/isced', [IscedController::class, 'index']);
Route::get('/isced/getbyid/{id}', [IscedController::class, 'getById']);
Route::post('/isced', [IscedController::class, 'store']);
Route::delete('/isced/deletebyid/{id}', [IscedController::class, 'deleteById']);

/** Routes partnercountry */
Route::get('/partnercountry', [PartnerCountryController::class, 'index']);
Route::get('/partnercountry/getbyid/{id}', [PartnerCountryController::class, 'getById']);

/** Routes partnercountry */
Route::get('/university', [UniversityController::class, 'index']);
Route::get('/university/getbyid/{id}', [UniversityController::class, 'getById']);
Route::post('/university', [UniversityController::class, 'store']);
Route::delete('/university/deletebyid/{id}', [UniversityController::class, 'deleteById']);

/** Routes access */
Route::get('/access', [AccessController::class, 'index']);
Route::get('/access/getrole/{login}', [AccessController::class, 'getRole']);
Route::get('/access/filtered', [AccessController::class, 'getFiltered']);
Route::get('/access/getbylogin/{login}', [AccessController::class, 'getByLogin']);
Route::post('/access', [AccessController::class, 'store']);
Route::delete('/access/delete', [AccessController::class, 'delete']);



/** Routes account */
Route::get('/account', [AccountController::class, 'index']);
Route::get('/account/students', [AccountController::class, 'students']);
Route::get('/account/getbylogin/{login}', [AccountController::class, 'getByLogin']);
Route::get('/account/getbydept/{dept_id}', [AccountController::class, 'getByDept']);
Route::post('/account', [AccountController::class, 'store']);
Route::put('/account/login/{login}', [AccountController::class, 'login']);
Route::put('/account/modif', [AccountController::class, 'modif']);
Route::delete('/account/deletebylogin/{login}', [AccountController::class, 'deleteByLogin']);
Route::put('/account/changedept/{login}/{dept_id}', [AccountController::class, 'changeDept']);
Route::put('/account/compldossier', [AccountController::class, 'complDossier']);
Route::delete('/account/removedept/{login}', [AccountController::class, 'removeDeptByLogin']);
Route::get('/account/export', [AccountController::class, 'export']);

/** Routes favoris */
Route::get('/favoris', [FavorisController::class, 'index']);
Route::get('/favoris/getbyid/{id}', [FavorisController::class, 'getById']);
Route::get('/favoris/getbylogin/{login}', [FavorisController::class, 'getByLogin']);
Route::post('/favoris', [FavorisController::class, 'store']);
Route::delete('/favoris/delete/{acc_id}/{agree_id}', [FavorisController::class, 'delete']);


/** Routes event */
Route::get('/event', [EventController::class, 'index']);
Route::get('/event/pfonly', [EventController::class, 'presentFuturOnly']);
Route::get('/event/getbyid/{id}', [EventController::class, 'getById']);
Route::get('/event/gettitlebyid/{id}', [EventController::class, 'getTitleById']);
Route::post('/event', [EventController::class, 'store']);
Route::delete('/event/deletebyid/{id}', [EventController::class, 'deleteById']);
Route::put('/event', [EventController::class, 'put']);

/** Routes wishagreement */
Route::post('/wishagreement', [WishAgreementController::class, 'save']);
Route::get('/wishagreement/getbylogin/{login}', [WishAgreementController::class, 'getByLogin']);

/** Routes eventtheme */
Route::get('/eventtheme', [EventThemeController::class, 'index']);
Route::get('/eventtheme/getbyid/{id}', [EventThemeController::class, 'getById']);
Route::delete('/eventtheme/deletebyid/{id}', [EventThemeController::class, 'deleteById']);
Route::post('/eventtheme', [EventThemeController::class, 'store']);
Route::put('/eventtheme', [EventThemeController::class, 'put']);

/** Routes admin */
Route::get('/admin', [AdministrationController::class, 'index']);
Route::put('/admin', [AdministrationController::class, 'changeDateLimite']);

/** Routes acceptedaccount */
Route::get('/acceptedaccount', [AcceptedAccountController::class, 'index']);
Route::post('/acceptedaccount', [AcceptedAccountController::class, 'store']);
Route::delete('/acceptedaccount', [AcceptedAccountController::class, 'delete']);
Route::get('/acceptedaccount/getbylogin/{login}', [AcceptedAccountController::class, 'getAcceptedByLogin']);

/** Routes arbitrage */
Route::get('/arbitrage', [ArbitrageController::class, 'index']);
Route::post('/arbitrage', [ArbitrageController::class, 'saveArbitrage']);
Route::get('/arbitrage/getbyid/{acc_id}', [ArbitrageController::class, 'showByAccId']);

/** Routes notification */
Route::get('/notification/getbylogin/{acc_id}', [NotificationController::class, 'getByLogin']);
Route::get('/notification', [NotificationController::class, 'index']);
Route::put('/notification/watch', [NotificationController::class, 'watch']);


/** Routes gestion base */
Route::delete('reset/all', [DataBaseController::class, 'resetAll']);
/** Permet de reset une table en particulier */
Route::delete('reset/{tableName}', [DataBaseController::class, 'resetTable']);


/** Routes gestion images */
Route::post('/image/upload', [ImageController::class, 'upload']);



/** Routes gestion documents */
Route::post('/documents', [DocumentsController::class, 'upload']);
Route::get('/documents/checkexist/{folder}/{filename}', [DocumentsController::class, 'checkFileExists']);
Route::get('/documents/delete/{folder}/{filename}', [DocumentsController::class, 'delete']);
Route::get('/documents/get/{folder}/{filename}', [DocumentsController::class, 'getDocument']);
