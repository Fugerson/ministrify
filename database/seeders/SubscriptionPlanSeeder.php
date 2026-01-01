<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Старт',
                'description' => 'Для малих церков',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_people' => 50,
                'max_ministries' => 1,
                'max_events_per_month' => 10,
                'max_users' => 2,
                'has_telegram_bot' => false,
                'has_finances' => false,
                'has_forms' => true,
                'has_website_builder' => false,
                'has_custom_domain' => false,
                'has_api_access' => false,
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'slug' => 'church',
                'name' => 'Церква',
                'description' => 'Для зростаючих церков',
                'price_monthly' => 49900, // 499 грн
                'price_yearly' => 419200, // 4192 грн (економія 30%)
                'max_people' => 500,
                'max_ministries' => 0, // unlimited
                'max_events_per_month' => 0, // unlimited
                'max_users' => 0, // unlimited
                'has_telegram_bot' => true,
                'has_finances' => true,
                'has_forms' => true,
                'has_website_builder' => false,
                'has_custom_domain' => false,
                'has_api_access' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'cathedral',
                'name' => 'Собор',
                'description' => 'Для великих церков',
                'price_monthly' => 99900, // 999 грн
                'price_yearly' => 839200, // 8392 грн (економія 30%)
                'max_people' => 0, // unlimited
                'max_ministries' => 0,
                'max_events_per_month' => 0,
                'max_users' => 0,
                'has_telegram_bot' => true,
                'has_finances' => true,
                'has_forms' => true,
                'has_website_builder' => true,
                'has_custom_domain' => true,
                'has_api_access' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('Subscription plans seeded successfully.');
    }
}
