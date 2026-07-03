<?php

namespace App\Observers;

use App\Models\ConductLog;

class ConductLogObserver
{
    public function created(ConductLog $log): void {}
}
