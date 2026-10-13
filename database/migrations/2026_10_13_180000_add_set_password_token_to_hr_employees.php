<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A one-time token so a shortlisted applicant can set their own password.
 *
 * The alternative — generating a password and emailing it — puts a working credential in
 * an inbox forever and means we hold a password the person never chose. A link that
 * expires avoids both.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (! Schema::hasColumn('hr_employees', 'set_password_token')) {
                $table->string('set_password_token', 64)->nullable()->index()->after('password');
            }
            if (! Schema::hasColumn('hr_employees', 'set_password_expires_at')) {
                $table->timestamp('set_password_expires_at')->nullable()->after('set_password_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            foreach (['set_password_token', 'set_password_expires_at'] as $c) {
                if (Schema::hasColumn('hr_employees', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
