<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminConfigTable extends Migration
{
    public function getConnection()
    {
        return config('database.connection') ?: config('database.default');
    }

    public function up()
    {
        if (! Schema::hasTable('admin_config')) {
            Schema::create('admin_config', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('value')->nullable();
                $table->string('name')->nullable();
                $table->text('help')->nullable();
                $table->string('element')->nullable();
                $table->tinyInteger('order')->default(1);
                $table->json('options')->nullable();
                $table->text('rule')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('admin_config');
    }
}
