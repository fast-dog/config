<?php

use FastDog\Config\Models\Translate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigTranslate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('config_translate')) {
            Schema::create('config_translate', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string(Translate::CODE)->comment('Код термина перевода, обычно путь к файлу где применяется термин');
                $table->string(Translate::KEY)->comment('Оригинальное значение');
                $table->string(Translate::VALUE)->comment('Результат перевода');
                $table->tinyInteger(Translate::STATE)->default(Translate::STATE_PUBLISHED)->comment('Состояние');
                $table->char(Translate::SITE_ID, 3)->default('000');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `config_translate` comment 'Справочник перевода текста'");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_translate');
    }
}
