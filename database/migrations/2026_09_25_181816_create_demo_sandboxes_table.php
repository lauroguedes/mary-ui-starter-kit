<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use LauroGuedes\DemoMode\Sandbox\Sandbox;

/**
 * Where a visitor's corner of the demonstration is recorded.
 *
 * Only needed by the 'scoped' sandbox driver. The 'shared' default -- everyone
 * sees the same data -- uses none of this.
 */
return new class extends Migration
{
    public function up(): void
    {
        Sandbox::createTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_sandboxes');
    }
};
