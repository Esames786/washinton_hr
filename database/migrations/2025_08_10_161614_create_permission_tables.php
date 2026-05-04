<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table names with hr_ prefix
        $tableNames = [
            'roles'                 => 'hr_roles',
            'permissions'           => 'hr_permissions',
            'model_has_permissions' => 'hr_model_has_permissions',
            'model_has_roles'       => 'hr_model_has_roles',
            'role_has_permissions'  => 'hr_role_has_permissions',
        ];

        // Permissions table
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // Roles table
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // Role_has_permissions pivot table
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        // Model_has_permissions pivot table
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('permission_id');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        // Model_has_roles pivot table
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('role_id');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = [
            'roles'                 => 'hr_roles',
            'permissions'           => 'hr_permissions',
            'model_has_permissions' => 'hr_model_has_permissions',
            'model_has_roles'       => 'hr_model_has_roles',
            'role_has_permissions'  => 'hr_role_has_permissions',
        ];

        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
