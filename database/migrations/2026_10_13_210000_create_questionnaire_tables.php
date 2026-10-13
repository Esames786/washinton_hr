<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The onboarding questionnaire (client workflow step 4).
 *
 * The questions themselves were never supplied, so rather than wait for them — or worse,
 * guess and hardcode them — they are DATA. An admin writes the questions in the portal,
 * exactly as they already do for daily-activity fields and document types.
 *
 * That also means the questionnaire can change over time without a deployment, and can
 * differ between brands.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hr_questionnaire_questions')) {
            Schema::create('hr_questionnaire_questions', function (Blueprint $table) {
                $table->id();
                $table->string('question', 500);
                $table->string('help_text', 500)->nullable();
                // Mirrors hr_daily_activity_fields so the admin UI feels familiar.
                $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'select', 'radio', 'yes_no'])
                      ->default('textarea');
                // Newline-separated choices for select/radio.
                $table->text('options')->nullable();
                $table->boolean('is_required')->default(1);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('status')->default(1);
                // Null = both brands, matching hr_document_settings.
                $table->string('brand', 20)->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hr_employee_questionnaire_answers')) {
            Schema::create('hr_employee_questionnaire_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->unsignedBigInteger('question_id')->index();
                $table->text('answer')->nullable();
                $table->timestamps();

                // One answer per question per employee; re-submitting updates it.
                $table->unique(['employee_id', 'question_id'], 'emp_question_unique');
            });
        }

        // When the questionnaire was completed — drives the move to In Progress.
        if (! Schema::hasColumn('hr_employees', 'questionnaire_submitted_at')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                $table->timestamp('questionnaire_submitted_at')->nullable()->after('nda_signed_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_questionnaire_answers');
        Schema::dropIfExists('hr_questionnaire_questions');

        if (Schema::hasColumn('hr_employees', 'questionnaire_submitted_at')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                $table->dropColumn('questionnaire_submitted_at');
            });
        }
    }
};
