<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuartoPlanoPrecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quarto_plano_precos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarto_id')->constrained()->onDelete('cascade');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('is_default')->default(false);
            $table->decimal('preco_segunda', 8, 2);
            $table->decimal('preco_terca', 8, 2);
            $table->decimal('preco_quarta', 8, 2);
            $table->decimal('preco_quinta', 8, 2);
            $table->decimal('preco_sexta', 8, 2);
            $table->decimal('preco_sabado', 8, 2);
            $table->decimal('preco_domingo', 8, 2);
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
        Schema::dropIfExists('quarto_plano_precos');
    }
}