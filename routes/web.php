<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    User::create([
        "name" => "Admin",
        "username" => "adminuser",
        "email" => "admin@admin.com",
        "password" => Hash::make('admin'),
        "birthday" => "2005-10-10",
        "phone" => "5555555",
        "address" => "asdsadas",
        "active" => true,

    ]);
    return view('welcome');
});
