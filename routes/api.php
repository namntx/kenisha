<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('lottery')->group(function () {
    Route::get('fetch', [App\Http\Controllers\LotteryApiController::class, 'fetchResults']);
    Route::get('fetch-all', [App\Http\Controllers\LotteryApiController::class, 'fetchAllResults']);
    Route::get('results', [App\Http\Controllers\LotteryApiController::class, 'getResults']);
});

Route::get('/provinces', function(Request $request) {
    $region = $request->input('region');
    
    if (!$region) {
        return response()->json([]);
    }
    
    $regionModel = \App\Models\Region::where('code', $region)->first();
    
    if (!$regionModel) {
        return response()->json([]);
    }
    
    $provinces = \App\Models\Province::where('region_id', $regionModel->id)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'code']);
    
    return response()->json($provinces);
});