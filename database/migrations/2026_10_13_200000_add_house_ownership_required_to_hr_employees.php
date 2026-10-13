<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Item 4 — "make own or rent house optional at the end of admin".
 *
 * NULL  = decide automatically: ask only when this agent's brand actually has documents
 *         that depend on the answer.
 * 1     = always ask this agent
 * 0     = never ask this agent
 *
 * The automatic default matters: all three own/rent documents (Bill, Rental Agreement,
 * Landlord CNIC) are CrazyRays-only, so a Hello agent answering the question unlocks
 * nothing — yet today they are forced to answer it, and it locks permanently afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hr_employees', 'house_ownership_required')) {
            return;
        }

        Schema::table('hr_employees', function (Blueprint $table) {
            $table->boolean('house_ownership_required')->nullable()->after('house_ownership');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('hr_employees', 'house_ownership_required')) {
            return;
        }

        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn('house_ownership_required');
        });
    }
};
