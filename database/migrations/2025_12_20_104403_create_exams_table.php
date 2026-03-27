<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->unique();
 // ex. 'biochemie', 'serologie'
            $table->string('name'); // Nom affiché
            $table->json('params')->nullable(); // JSON array [{param: 'Glycémie', norme: '0.74 - 1.10 g/l'}, ...]
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
};