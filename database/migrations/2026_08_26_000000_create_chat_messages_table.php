<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('role', ['user', 'assistant']);
            $table->text('message');
            $table->boolean('is_ai')->default(false);
            $table->timestamps();

            $table->index('session_id');
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
