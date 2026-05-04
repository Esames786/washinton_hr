<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email',175)->unique();
            $table->string('password');
            $table->string('profile_path')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_admins');
    }
};
