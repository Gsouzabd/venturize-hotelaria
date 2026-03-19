<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$reserva = App\Models\Reserva::find(240);
if (!$reserva) {
    echo "NOT_FOUND\n";
    exit(0);
}

echo "checkin={$reserva->data_checkin}|checkout={$reserva->data_checkout}\n";
echo "cart_raw=" . $reserva->getRawOriginal('cart_serialized') . "\n";

$cart = $reserva->getCartSerializedAttribute();
if (is_array($cart)) {
    echo 'cart_keys=' . implode(',', array_keys($cart)) . "\n";
    if (isset($cart[0]) && is_array($cart[0])) {
        echo 'cart0_keys=' . implode(',', array_keys($cart[0])) . "\n";
    }
}
