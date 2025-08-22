<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing');
Route::view('/explore', 'explore');
Route::view('/u/{username}', 'profile');
Route::view('/submit', 'submit');


