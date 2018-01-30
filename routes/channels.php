<?php

Broadcast::channel('App.User.*', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

