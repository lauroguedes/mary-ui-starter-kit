<?php

declare(strict_types=1);

namespace App\Traits;

trait ClearsFilters
{
    public function updated($property): void
    {
        if (! is_array($property) && $property !== '') {
            $this->resetPage();
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success(__('Filters cleared.'), position: 'toast-bottom');
    }
}
