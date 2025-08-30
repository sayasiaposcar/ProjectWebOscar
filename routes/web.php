<?php

use Illuminate\Support\Facades\Route;

// Halaman umum
Route::view('/', 'landing');
Route::view('/explore', 'explore');
Route::view('/submit', 'submit');

// Profil (lebih spesifik dari /group/*)
Route::view('/u/{username}', 'profile');

// Group
Route::view('/group', 'group');
Route::view('/group/create', 'group-create')->name('group.create');
Route::view('/group/{slug}/post', 'post-create')->name('group.post');
Route::view('/group/{slug}', 'group-show')->name('group.show');

// --- NEW: Messages page ---
Route::view('/messages', 'messages');
