<?php

use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\SaleOverviewController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Daily Check at 18:00 (6 PM)
Schedule::call(function () {
    $controller = new SaleOverviewController();

    // A. Weekly Report (Every Sunday)
    if (now()->isSunday()) {
        $req = new Request(['type' => 'weekly']);
        $controller->sendReport($req);
    }

    // B. Monthly Report (Last Day of Month)
    if (now()->isLastOfMonth()) {
        $req = new Request(['type' => 'monthly']);
        $controller->sendReport($req);
    }
    
    // Note: If today is Sunday AND Last of Month, BOTH run correctly.

})->dailyAt('18:00');
