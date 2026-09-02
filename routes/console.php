<?php

use App\Jobs\ExpirePaymentsJob;
use App\Jobs\PollBlockchainNetworksJob;
use App\Jobs\RetryDueWebhooksJob;
use App\Jobs\RunReconciliationJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PollBlockchainNetworksJob)->everyMinute()->name('poll-blockchain');
Schedule::job(new ExpirePaymentsJob)->everyMinute()->name('expire-payments');
Schedule::job(new RetryDueWebhooksJob)->everyMinute()->name('retry-webhooks');
Schedule::job(new RunReconciliationJob)->hourly()->name('reconciliation');
