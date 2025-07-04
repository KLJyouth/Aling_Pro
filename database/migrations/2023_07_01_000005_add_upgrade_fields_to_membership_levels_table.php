<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpgradeFieldsToMembershipLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('membership_levels', function (Blueprint \) {
            \->integer('upgrade_points')->default(0)->after('discount_percent');
            \->decimal('upgrade_spending', 10, 2)->default(0)->after('upgrade_points');
            \->integer('upgrade_months')->default(0)->after('upgrade_spending');
            
            // 价格拆分为月付和年付
            if (!Schema::hasColumn('membership_levels', 'price_monthly')) {
                \->decimal('price_monthly', 10, 2)->default(0)->after('description');
                \->decimal('price_yearly', 10, 2)->default(0)->after('price_monthly');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership_levels', function (Blueprint \) {
            \->dropColumn(['upgrade_points', 'upgrade_spending', 'upgrade_months']);
            
            if (Schema::hasColumn('membership_levels', 'price_monthly')) {
                \->dropColumn(['price_monthly', 'price_yearly']);
            }
        });
    }
}
