<?php

use App\Jobs\DailyDealJob;
use App\Jobs\SpotlightJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SpotlightJob)->everyMinute()->withoutOverlapping();

Schedule::command('queue:work --queue=spotlight --rest=0.09 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()    
    ->sendOutputTo('/home/authorsxp/mailing-queue/queue_output.txt',true);    

Schedule::job(new DailyDealJob)->dailyAt('06:07');
Schedule::command('queue:work --queue=dailydeal --rest=0.09 --stop-when-empty')
    ->dailyAt('06:07')
    ->withoutOverlapping()
    ->sendOutputTo('/home/authorsxp/mailing-queue/queue_dailydeal_output.txt',true);  