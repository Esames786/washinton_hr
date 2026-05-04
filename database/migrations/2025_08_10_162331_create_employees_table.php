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
        Schema::create('hr_employees', function (Blueprint $table) {
                $table->id();

                // Auth info
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email',75)->unique();
                $table->string('password');
                $table->rememberToken();

                // Job info
                $table->string('employee_code',75)->unique();
                $table->string('department')->nullable();
                $table->string('designation')->nullable();
                $table->date('joining_date')->nullable();
                $table->date('resignation_date')->nullable();
                $table->string('working_hours')->nullable();
                $table->decimal('basic_salary', 12, 2)->nullable();
                $table->integer('employment_type_id');
                $table->integer('employee_status_id');

                // Personal info
                $table->string('father_name')->nullable();
                $table->string('mother_name')->nullable();
                $table->string('cnic')->nullable();
                $table->date('dob')->nullable();
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
                $table->integer('kids_count')->nullable();
                $table->text('skills')->nullable();

                // Contact info
                $table->string('phone')->nullable();
                $table->string('phone2')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('emergency_contact')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();

                $table->unsignedBigInteger('role_id')->nullable();
                $table->unsignedInteger('shift_id')->nullable();
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
        Schema::dropIfExists('hr_employees');
    }
};
