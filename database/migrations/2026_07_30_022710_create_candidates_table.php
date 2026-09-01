<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('elections')->onDelete('cascade');
            $table->integer('candidate_number')->comment('Nomor Urut 1, 2, atau 3');
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->string('class');
            $table->string('major');
            $table->text('biodata')->nullable();
            $table->text('vision');
            $table->text('mission');
            $table->text('motto')->nullable();
            $table->text('achievements')->nullable()->comment('Prestasi');
            $table->text('organizations')->nullable()->comment('Pengalaman Organisasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};