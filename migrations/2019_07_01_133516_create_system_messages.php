<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use FastDog\Config\Models\SystemMessages;

class CreateSystemMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('system_messages')) {
            Schema::create('system_messages', function (Blueprint $table) {
                $table->increments('id');
                $table->string(SystemMessages::NAME)->comment('Название');
                $table->string(SystemMessages::ALIAS)->comment('Псевдоним');
                $table->text(SystemMessages::TEXT)->comment('Текст шаблона');
                $table->json(SystemMessages::DATA)->comment('Дополнительные параметры');
                $table->tinyInteger(SystemMessages::STATE)
                    ->default(SystemMessages::STATE_NOT_PUBLISHED)->comment('Состояние');
                $table->char(SystemMessages::SITE_ID, 3)->default('000');
                $table->string('type', 45);
                $table->timestamps();
                $table->softDeletes();
                $table->index([SystemMessages::NAME, SystemMessages::ALIAS], 'IDX_system_email_name');
            });
            DB::statement("ALTER TABLE `system_messages` comment 'Шаблоны почтовых сообщений'");
        }
        Schema::create('system_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
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
        Schema::dropIfExists('system_messages');
    }
}
