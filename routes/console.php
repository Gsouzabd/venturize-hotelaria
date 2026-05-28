<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Marca como NO SHOW reservas cujo checkout foi às 12:00 e não houve check-in
Schedule::command('reservas:expirar')->dailyAt('12:05')->timezone('America/Sao_Paulo');
