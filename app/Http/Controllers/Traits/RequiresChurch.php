<?php

namespace App\Http\Controllers\Traits;

trait RequiresChurch
{
    protected function getChurchOrFail()
    {
        return $this->getCurrentChurch();
    }
}
