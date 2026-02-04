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
        Schema::create('impressoras', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('ip', 45); // Suporta IPv4 e IPv6
            $table->integer('porta')->default(9100);
            $table->boolean('ativo')->default(true);
            $table->enum('tipo', ['termica', 'convencional'])->default('termica');
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
            
            // Índices para performance
            $table->index('ip');
            $table->index('ativo');
            $table->index('ordem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impressoras');
    }
};

