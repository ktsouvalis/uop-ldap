<?php

use LdapRecord\Models\OpenLDAP\User;
use Illuminate\Support\Facades\Route;
use LdapRecord\Models\OpenLDAP\Entry;

Route::get('/', function () {
    // return json_encode(User::all());
    return json_encode(Entry::all());
});

Route::get('/test', function(){
    $user = User::create([
        'description' => 'This is a test user',
        'sn'        => 'Bauman',
        'cn'        => 'Steve Bauman',
    ]);
    
    return $user;
});
