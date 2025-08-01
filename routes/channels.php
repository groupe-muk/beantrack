<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user->id === $id; // Using string comparison instead of int cast
});

// Private chat channel authorization
Broadcast::channel('chat.{id}', function ($user, $id) {
    return $user->id === $id; // Using string comparison instead of int cast
});