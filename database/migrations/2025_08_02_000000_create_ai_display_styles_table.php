<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移
     * 
     * @return void
     */
    public function up()
    {
        Schema::create("ai_display_styles", function (Blueprint $table) {
            $table->id();
            $table->morphs("styleable"); // 多态关联，可关联到模型或智能体
            $table->string("font_family")->nullable(); // 字体
            $table->string("font_color")->nullable(); // 字体颜色
            $table->string("gradient_start")->nullable(); // 渐变开始颜色
            $table->string("gradient_end")->nullable(); // 渐变结束颜色
            $table->boolean("is_animated")->default(false); // 是否动态效果
            $table->string("badge_text")->nullable(); // 标签文本，如MAX、Pro等
            $table->string("badge_color")->nullable(); // 标签颜色
            $table->string("badge_icon")->nullable(); // 标签图标
            $table->text("tooltip_content")->nullable(); // 提示内容
            $table->timestamps();
        });
    }

    /**
     * 回滚迁移
     * 
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("ai_display_styles");
    }
};
