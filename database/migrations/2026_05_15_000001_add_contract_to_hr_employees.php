<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractToHrEmployees extends Migration
{
    public function up()
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->longText('contract')->nullable()->after('country');
            $table->timestamp('contract_updated_at')->nullable()->after('contract');
            $table->timestamp('contract_accepted_at')->nullable()->after('contract_updated_at');
        });
    }

    public function down()
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn(['contract', 'contract_updated_at', 'contract_accepted_at']);
        });
    }
}
