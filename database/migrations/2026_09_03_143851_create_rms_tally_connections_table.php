<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rms_tally_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('rms_owners')->cascadeOnDelete();
            $table->string('tailscale_ip');
            $table->unsignedInteger('port')->default(9000);
            $table->string('status')->nullable();
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
        Schema::dropIfExists('rms_tally_connections');
    }
};
