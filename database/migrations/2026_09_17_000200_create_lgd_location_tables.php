<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * India LGD location directory, laid out to match the three CSV files in the
 * project's excel/ folder column for column (the files have no header row),
 * so each file can be imported straight into its table from phpMyAdmin or
 * with `php artisan lgd:import`.
 *
 * Codes that are blank in the files (census codes, sub-district and village
 * codes) are strings, because phpMyAdmin imports a blank cell as '' and an
 * integer column would reject it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // state_for_insert.csv
        Schema::create('lgd_states', function (Blueprint $table): void {
            $table->string('sr_no', 10)->nullable();
            $table->unsignedSmallInteger('state_code')->primary();
            $table->string('version', 10)->nullable();
            $table->string('name', 100);
            $table->string('name_local', 100)->nullable();
            $table->string('census_2001_code', 10)->nullable();
            $table->string('census_2011_code', 10)->nullable();
            // 'S' = State, 'U' = Union Territory.
            $table->string('state_type', 2)->nullable();
        });

        // district_for_insert.csv
        Schema::create('lgd_districts', function (Blueprint $table): void {
            $table->unsignedSmallInteger('state_code')->index();
            $table->string('state_name', 100)->nullable();
            $table->unsignedInteger('district_code')->primary();
            $table->string('name', 150);
            $table->string('census_2001_code', 10)->nullable();
            $table->string('census_2011_code', 10)->nullable();
        });

        // Muncipal_for_insert.csv — urban local bodies with their sub-district
        // (taluka) and village; the taluka dropdown reads distinct rows here.
        Schema::create('lgd_local_bodies', function (Blueprint $table): void {
            // The file writes large serials with a thousands separator ("22,911").
            $table->string('sr_no', 20)->primary();
            $table->string('state_name', 100)->nullable();
            $table->string('local_body_code', 20)->nullable()->index();
            $table->string('local_body_name', 150)->nullable();
            $table->string('census_code', 20)->nullable();
            $table->unsignedInteger('district_code')->index();
            $table->string('district_name', 150)->nullable();
            $table->string('subdistrict_code', 20)->nullable();
            $table->string('subdistrict_name', 150)->nullable();
            $table->string('village_code', 20)->nullable();
            $table->string('village_name', 150)->nullable();
            $table->index(['district_code', 'subdistrict_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgd_local_bodies');
        Schema::dropIfExists('lgd_districts');
        Schema::dropIfExists('lgd_states');
    }
};
