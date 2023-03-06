<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_integrations', function (Blueprint $table) {
            $table->id();            
            $table->string('name');
            $table->char('short_name', 10);
            $table->string('documentation_link')->nullable();
            $table->json('payload')->nullable();
            $table->boolean('active')->nullable()->default(0);
            $table->morphs('owner');
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
        Schema::dropIfExists('service_integrations');
    }
}
