<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostNofiticationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_nofitications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean("seen")->default(false);
            $table->foreignId("post_id")
                    ->constrained("posts")
                    ->onDelete("cascade");
            $table->foreignId("recipient_id")
                    ->constrained("users")
                    ->onDelete("cascade");
            $table->foreignId("actor_id")
                    ->constrained("users")
                    ->onDelete("cascade");
            $table->enum("action", ["like", "comment", "share", "tag"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_nofitications');
    }
}
