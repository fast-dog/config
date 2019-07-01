<?php

use FastDog\Config\Models\ConfigHelp;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigHelp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_help', function (Blueprint $table) {
            $table->increments('id');
            $table->string(ConfigHelp::NAME)->comment('Название');
            $table->string(ConfigHelp::ALIAS)->comment('Псевдоним');
            $table->text(ConfigHelp::TEXT)->comment('Текст шаблона');
            $table->json(ConfigHelp::DATA)->comment('Дополнительные параметры');
            $table->tinyInteger(ConfigHelp::STATE)
                ->default(ConfigHelp::STATE_NOT_PUBLISHED)->comment('Состояние');
            $table->char(ConfigHelp::SITE_ID, 3)->default('000');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_help');
    }
}
