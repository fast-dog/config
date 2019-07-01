<?php


use FastDog\Config\Models\Emails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('config_emails')) {
            Schema::create('config_emails', function (Blueprint $table) {
                $table->increments('id');
                $table->string(Emails::NAME)->comment('Название');
                $table->string(Emails::ALIAS)->comment('Псевдоним');
                $table->text(Emails::TEXT)->comment('Текст шаблона');
                $table->json(Emails::DATA)->comment('Дополнительные параметры')->nullable();
                $table->tinyInteger(Emails::STATE)->default(Emails::STATE_NOT_PUBLISHED)->comment('Состояние');
                $table->char(Emails::SITE_ID, 3)->default('000');
                $table->string('type', 45)->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index([Emails::NAME, Emails::ALIAS], 'IDX_system_emails_name');
                $table->index(Emails::ALIAS, 'IDX_system_emails_alias');
            });
            DB::statement("ALTER TABLE `config_emails` comment 'Шаблоны почтовых сообщений'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_emails');
    }
}
