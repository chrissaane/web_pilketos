<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->unique('year', 'elections_year_unique');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->unique(['election_id', 'candidate_number'], 'candidates_election_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropUnique('candidates_election_number_unique');
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->dropUnique('elections_year_unique');
        });
    }
};
