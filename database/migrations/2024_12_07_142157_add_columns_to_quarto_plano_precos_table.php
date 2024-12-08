<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToQuartoPlanoPrecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quarto_plano_precos', function (Blueprint $table) {
            $table->boolean('is_duplo')->default(false);
            $table->boolean('is_triplo')->default(false);
            $table->boolean('is_individual')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quarto_plano_precos', function (Blueprint $table) {
            $table->dropColumn(['is_duplo', 'is_triplo', 'is_individual']);
        });
    }
}