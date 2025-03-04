<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('my-channel', function ($user) {
    return true; // Replace with your authorization logic
});