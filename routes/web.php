<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('frontend');
});

Route::get('/app/{any?}', function () {
    return view('frontend');
})->where('any', '.*');
