<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\IncomeCategoryController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TagController;
use \App\Http\Controllers\StatisticsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UploadController;

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

#Route::get('/got-check', [BillController::class, 'hook']);


Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('admin-login', [AuthController::class, 'adminLogin']);
    Route::post('confirm-code', [AuthController::class, 'confirmCode']);
    #Route::post('set-pin', [AuthController::class, 'setPinCode'])->middleware('auth:sanctum');
    #Route::post('login-by-pin', [AuthController::class, 'loginByPinCode']);
    Route::get('token-check', [AuthController::class, 'tokenCheck'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'getMe'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::resources([
        'income-categories' => IncomeCategoryController::class,
        'expense-categories' => ExpenseCategoryController::class,
        'transactions' => TransactionController::class,
        'bills' => BillController::class,
        'expense-categories.tags' => TagController::class,
        'contacts' => ContactController::class,
    ]);

    Route::middleware('manager')->prefix("customers")->group(function () {
        Route::get("/", [CustomerController::class, 'index']);
    });
    Route::middleware('admin')->prefix("managers")->group(function () {
        Route::get("/", [ManagerController::class, 'index']);
        Route::post("/", [ManagerController::class, 'store']);
        Route::put("/{managerId}", [ManagerController::class, 'update']);
        Route::delete("/{managerId}", [ManagerController::class, 'destroy']);

        Route::resource('products', \App\Http\Controllers\ProductController::class);
    });

    Route::get('statistics/total', [StatisticsController::class, 'total']);
    Route::get('statistics/in-range', [StatisticsController::class, 'inRange']);
    Route::get('statistics', [StatisticsController::class, 'stats']);

    Route::post('upload', [UploadController::class, 'upload']);

});

