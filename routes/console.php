<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('helpdesk:check-sla')->everyMinute();
