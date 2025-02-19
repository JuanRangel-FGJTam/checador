<?php

# use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

# Artisan::command('inspire', fn()=> $this->comment(Inspiring::quote()) )->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:remove-temporary-files')->daily();

Schedule::command('app:incident-create')->daily()->timezone('America/Mexico_City')->at('01:00');