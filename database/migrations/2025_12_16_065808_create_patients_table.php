<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('dob')->nullable(); // Date de naissance optionnelle; // Date de naissance
            $table->string('dossier_number')->unique(); // Numéro dossier unique
            $table->text('address')->nullable(); // Adresse optionnelle
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
};