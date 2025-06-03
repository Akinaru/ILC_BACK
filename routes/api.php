<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
use App\Http\Controllers\MobileAppController;

/** ================================================================================================ */
/** Route Auth */
/** ================================================================================================ */
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/** ================================================================================================ */
/** Routes article */
/** ================================================================================================ */
Route::get('/article', [ArticleController::class, 'index']);
Route::get('/article/getbyid/{id}', [ArticleController::class, 'getById']);
Route::get('/article/image/{id}', [ArticleController::class, 'getImageById']);
Route::get('/article/unlinkdocuments/{id}', [ArticleController::class, 'unlinkDocuments']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::put('/article', [ArticleController::class, 'put']);
    Route::post('/article', [ArticleController::class, 'store']);
    Route::delete('/article/deletebyid/{id}', [ArticleController::class, 'deleteById']);
});

/** ================================================================================================ */
/** Routes action */
/** ================================================================================================ */
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/action', [ActionController::class, 'index']);
    Route::get('/action/getbyid/{id}', [ActionController::class, 'getById']);
    Route::get('/action/getbylogin/{login}', [ActionController::class, 'getByLogin']);
    Route::get('/action/getfivebylogin/{login}', [ActionController::class, 'getFiveByLogin']);
    Route::post('/action', [ActionController::class, 'store']);
    Route::delete('/action', [ActionController::class, 'delete']);
    Route::get('/action/paginate', [ActionController::class, 'paginateActions']);

});

/** ================================================================================================ */
/** Routes agreement */
/** ================================================================================================ */
Route::get('/agreement', [AgreementController::class, 'index']);
Route::get('/agreement/random', [AgreementController::class, 'random']);
Route::get('/agreement/getbyid/{id}', [AgreementController::class, 'getById']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/agreement/home', [AgreementController::class, 'agreementHome']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/agreement', [AgreementController::class, 'store']);
    Route::post('/agreementexp', [AgreementController::class, 'storeImport']);
    Route::put('/agreement/{id}', [AgreementController::class, 'update']);
    Route::delete('/agreement/deletebyid/{id}', [AgreementController::class, 'deleteById']);
    Route::delete('/agreement/deleteall', [AgreementController::class, 'deleteAll']);
    Route::get('/agreement/export', [AgreementController::class, 'export']);
});

/** ================================================================================================ */
/** Routes component */
/** ================================================================================================ */
Route::get('/component', [ComponentController::class, 'index']);
Route::post('/component', [ComponentController::class, 'store']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/component/getbyid/{id}', [ComponentController::class, 'getById']);
    Route::delete('/component/deletebyid/{id}', [ComponentController::class, 'deleteById']);
    Route::put('/component', [ComponentController::class, 'put']);
});

/** ================================================================================================ */
/** Routes department */
/** ================================================================================================ */
Route::get('/department', [DepartmentController::class, 'index']);
Route::get('/department/getbyid/{id}', [DepartmentController::class, 'getById']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/department', [DepartmentController::class, 'store']);
    Route::delete('/department/deletebyid/{id}', [DepartmentController::class, 'deleteById']);
    Route::put('/department', [DepartmentController::class, 'put']);
    Route::get('/department/export', [DepartmentController::class, 'export']);
});

/** ================================================================================================ */
/** Routes departmentagreement */
/** ================================================================================================ */
Route::get('/departmentagreement', [DepartmentAgreementController::class, 'index']);
Route::get('/departmentagreement/getbyid/{id}', [DepartmentAgreementController::class, 'getById']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/departmentagreement', [DepartmentAgreementController::class, 'store']);
    Route::delete('/departmentagreement/delete/{agree_id}/{dept_id}', [DepartmentAgreementController::class, 'delete']);
    Route::put('/departmentagreement/changevisibility', [DepartmentAgreementController::class, 'changeVisibilityDept']);
});

/** ================================================================================================ */
/** Routes isced */
/** ================================================================================================ */
Route::get('/isced', [IscedController::class, 'index']);
Route::get('/isced/getbyid/{id}', [IscedController::class, 'getById']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/isced', [IscedController::class, 'store']);
    Route::put('/isced', [IscedController::class, 'put']);
    Route::delete('/isced/deletebyid/{id}', [IscedController::class, 'deleteById']);
});

/** ================================================================================================ */
/** Routes partnercountry */
/** ================================================================================================ */
Route::get('/partnercountry', [PartnerCountryController::class, 'index']);
Route::get('/partnercountry/getbyid/{id}', [PartnerCountryController::class, 'getById']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/partnercountry', [PartnerCountryController::class, 'store']);
    Route::put('/partnercountry', [PartnerCountryController::class, 'put']);
    Route::delete('/partnercountry/{id}', [PartnerCountryController::class, 'deleteById']);
});

/** ================================================================================================ */
/** Routes university */
/** ================================================================================================ */
Route::get('/university', [UniversityController::class, 'index']);
Route::get('/university/getbyid/{id}', [UniversityController::class, 'getById']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/university', [UniversityController::class, 'store']);
    Route::put('/university', [UniversityController::class, 'put']);
    Route::delete('/university/deletebyid/{id}', [UniversityController::class, 'deleteById']);
});

/** ================================================================================================ */
/** Routes access */
/** ================================================================================================ */
Route::get('/access/getbylogin/{login}', [AccessController::class, 'getByLogin']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/access/filtered', [AccessController::class, 'getFiltered']);
    Route::get('/access', [AccessController::class, 'index']);
    Route::post('/access', [AccessController::class, 'store']);
    Route::delete('/access/delete', [AccessController::class, 'delete']);
});

/** ================================================================================================ */
/** Routes account */
/** ================================================================================================ */
Route::post('/account', [AccountController::class, 'store']);
Route::put('/account/login/{acc_id}', [AccountController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/account', [AccountController::class, 'index']);
    Route::get('/account/actuel', [AccountController::class, 'indexActuel']);
    Route::get('/account/students', [AccountController::class, 'students']);
    Route::get('/account/students/actuel', [AccountController::class, 'studentsActuel']);
    Route::get('/account/getbydept/{dept_id}', [AccountController::class, 'getByDept']);
    Route::get('/account/actuel/getbydept/{dept_id}', [AccountController::class, 'getByDeptActuel']);
    Route::get('/account/getbylogin/{acc_id}', [AccountController::class, 'getByLogin']);


    Route::put('/account/temoignage', [AccountController::class, 'temoignage']);
    Route::delete('/account/temoignage', [AccountController::class, 'supprimerTemoignage']);
    Route::put('/account/modifetu', [AccountController::class, 'modifEtu']);
    Route::delete('/account/selfdelete', [AccountController::class, 'selfDelete']);
    Route::delete('/account/{acc_id}', [AccountController::class, 'deleteNoAccepted']);
    Route::get('/account/export', [AccountController::class, 'export']);
    Route::put('/account/compldossier', [AccountController::class, 'complDossier']);
});

Route::middleware(['auth:sanctum', 'role:chefdept'])->group(function () {
    Route::put('/account/validatechoixcours/{login}', [AccountController::class, 'validateChoixCours']);
    Route::put('/account/modif', [AccountController::class, 'modif']);
    Route::delete('/account/deletebyid/{login}', [AccountController::class, 'deleteById']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::put('/account/changedept/{login}/{dept_id}', [AccountController::class, 'changeDept']);
    Route::delete('/account/removedept/{login}', [AccountController::class, 'removeDeptByLogin']);
});

/** ================================================================================================ */
/** Routes favoris */
/** ================================================================================================ */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favoris/me', [FavorisController::class, 'getMyFavoris']);
    Route::post('/favoris', [FavorisController::class, 'store']);
    Route::delete('/favoris/delete/{agree_id}', [FavorisController::class, 'delete']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/favoris/getbyid/{id}', [FavorisController::class, 'getById']);
    Route::get('/favoris', [FavorisController::class, 'index']);
    Route::get('/favoris/getbylogin/{login}', [FavorisController::class, 'getByLogin']);
});

/** ================================================================================================ */
/** Routes event */
/** ================================================================================================ */
Route::get('/event', [EventController::class, 'index']);
Route::get('/event/pfonly', [EventController::class, 'presentFuturOnly']);
Route::get('/event/getbyid/{id}', [EventController::class, 'getById']);
Route::get('/event/gettitlebyid/{id}', [EventController::class, 'getTitleById']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/event', [EventController::class, 'store']);
    Route::delete('/event/deletebyid/{id}', [EventController::class, 'deleteById']);
    Route::put('/event', [EventController::class, 'put']);
});

/** ================================================================================================ */
/** Routes wishagreement */
/** ================================================================================================ */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wishagreement', [WishAgreementController::class, 'save']);
});

Route::middleware(['auth:sanctum', 'role:chefdept'])->group(function () {
    Route::get('/wishagreement/getbylogin/{login}', [WishAgreementController::class, 'getByLogin']);
});

/** ================================================================================================ */
/** Routes eventtheme */
/** ================================================================================================ */
Route::get('/eventtheme', [EventThemeController::class, 'index']);
Route::get('/eventtheme/getbyid/{id}', [EventThemeController::class, 'getById']);
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::delete('/eventtheme/deletebyid/{id}', [EventThemeController::class, 'deleteById']);
    Route::post('/eventtheme', [EventThemeController::class, 'store']);
    Route::put('/eventtheme', [EventThemeController::class, 'put']);
});

/** ================================================================================================ */
/** Routes admin */
/** ================================================================================================ */
Route::get('/admin', [AdministrationController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::put('/admin/date/automne', [AdministrationController::class, 'changeDateLimiteAutomne']);
    Route::put('/admin/date/printemps', [AdministrationController::class, 'changeDateLimitePrintemps']);
    Route::put('/admin/arbitrage', [AdministrationController::class, 'changeArbitrageStatus']);
    Route::post('/admin/database', [AdministrationController::class, 'backup']);
    Route::get('/admin/download', [AdministrationController::class, 'downloadLatest']);
});

/** ================================================================================================ */
/** Routes acceptedaccount */
/** ================================================================================================ */
Route::get('/acceptedaccount/getbylogin/{login}', [AcceptedAccountController::class, 'getAcceptedByLogin']);
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/acceptedaccount', [AcceptedAccountController::class, 'index']);
    Route::post('/acceptedaccount', [AcceptedAccountController::class, 'store']);
    Route::post('/acceptedaccount/import', [AcceptedAccountController::class, 'storeImport']);
    Route::delete('/acceptedaccount', [AcceptedAccountController::class, 'delete']);
});

/** ================================================================================================ */
/** Routes arbitrage */
/** ================================================================================================ */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/arbitrage/me', [ArbitrageController::class, 'showMyArbitrage']);
});
Route::middleware(['auth:sanctum', 'role:chefdept'])->group(function () {
    Route::get('/arbitrage/getbyid/{acc_id}', [ArbitrageController::class, 'showByAccId']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/arbitrage', [ArbitrageController::class, 'index']);
    Route::get('/arbitrage/actuel', [ArbitrageController::class, 'indexActuel']);
    Route::post('/arbitrage', [ArbitrageController::class, 'saveArbitrage']);
    Route::post('/arbitrage/archiver', [ArbitrageController::class, 'archiverArbitrage']);
    Route::post('/arbitrage/valider', [ArbitrageController::class, 'validerArbitrage']);
    Route::post('/arbitrage/desarchiver', [ArbitrageController::class, 'desarchiver']);
    Route::put('/arbitrage', [ArbitrageController::class, 'modifArbitrage']);
});

/** ================================================================================================ */
/** Routes gestion images */
/** ================================================================================================ */
Route::get('/image', [ImageController::class, 'getImage']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/image/uploadarticle', [ImageController::class, 'uploadArticle']);
    Route::post('/image/upload', [ImageController::class, 'upload']);
    Route::delete('/image', [ImageController::class, 'delete']);
});

/** ================================================================================================ */
/** Routes gestion documents */
/** ================================================================================================ */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/documents', [DocumentsController::class, 'upload']);
    Route::get('/documents/checkexist/{folder}/{filename}', [DocumentsController::class, 'checkFileExists']);
    Route::get('/documents/checkexistperso/{folder}/{filename}', [DocumentsController::class, 'checkFileExistsPerso']);
    Route::get('/documents/delete/{folder}/{filename}', [DocumentsController::class, 'delete']);
    Route::get('/documents/deleteperso/{folder}/{filename}', [DocumentsController::class, 'deletePerso']);
    Route::get('/documents/get/{folder}/{filename}', [DocumentsController::class, 'getDocument']);
    Route::get('/documents/getperso/etu/{folder}/{filename}', [DocumentsController::class, 'getMyDocument']);
});

/** ================================================================================================ */
/** Routes gestion documents pour articles */
/** ================================================================================================ */
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/documents/article', [DocumentsController::class, 'uploadDocumentArticle']);
});
Route::get('/documents/article', [DocumentsController::class, 'getAllDocumentsForArticle']);
Route::get('/documents/article/{idarticle}', [DocumentsController::class, 'getDocumentArticle']);
Route::get('/documents/article/get/{filename}', [DocumentsController::class, 'downloadDocumentArticle']);

/** ================================================================================================ */
/** Routes pour l'application mobile */
/** ================================================================================================ */
Route::get('/mobileapp/exportdata', [MobileAppController::class, 'exportForMobileApp']);
Route::get('/mobileapp/assignpwd', [MobileAppController::class, 'bulkAssignToken']);