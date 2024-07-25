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

Broadcast::channel('users', function () {
    return true;
});

Broadcast::channel('banks', function () {
    return true;
});

Broadcast::channel('bankAccounts', function () {
    return true;
});

Broadcast::channel('types', function () {
    return true;
});

Broadcast::channel('subtypes', function () {
    return true;
});

Broadcast::channel('expenses', function () {
    return true;
});

Broadcast::channel('suppliers', function () {
    return true;
});

Broadcast::channel('taxes', function () {
    return true;
});

Broadcast::channel('incomeTaxWithholdings', function () {
    return true;
});

Broadcast::channel('incomeTaxWithholdingScales', function () {
    return true;
});

Broadcast::channel('socialSecurityTaxWithholdings', function () {
    return true;
});

Broadcast::channel('vatTaxWithholdings', function () {
    return true;
});