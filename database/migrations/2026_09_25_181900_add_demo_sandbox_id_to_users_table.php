<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LauroGuedes\DemoMode\Sandbox\Sandbox;

/**
 * Which visitor of the demo created this user, if any.
 *
 * Null means everybody's: the fifty-three accounts the seeder makes carry no
 * sandbox, which is what lets a scoped demo look like a populated application
 * rather than an empty one. A user a visitor creates through the Users screen
 * carries theirs, and only they see it.
 *
 * Inert off a demo. The column is nullable, BelongsToSandbox adds no scope unless
 * demo.sandbox.driver is 'scoped', and nothing in this application writes to it.
 * On a normal deployment of this starter kit it is one unused nullable string.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string(Sandbox::COLUMN)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['demo_sandbox_id']);
            $table->dropColumn(Sandbox::COLUMN);
        });
    }
};
