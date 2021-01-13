<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowNofiticationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follow_nofitications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean("seen")->default(false);
            $table->foreignId("recipient_id")
                    ->constrained("users")
                    ->onDelete("cascade");
            $table->foreignId("actor_id")
                    ->constrained("users")
                    ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('follow_nofitications');
    }
}
