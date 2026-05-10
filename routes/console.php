<?php

use App\Services\StreakCheckerService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Se ejecuta todos los dias a las 11:59 PM
Schedule::call(function () {
    app(StreakCheckerService::class)->checkAllGoals();
})->dailyAt('23:59');
