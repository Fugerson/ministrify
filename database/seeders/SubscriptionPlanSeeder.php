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
                'name' => 'Free',
                'description' => 'Для початку роботи',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_people' => 50,
                'max_ministries' => 3,
                'max_events_per_month' => 10,
                'max_users' => 3,
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
                'slug' => 'basic',
                'name' => 'Basic',
                'description' => 'Для зростаючих церков',
                'price_monthly' => 9900, // 99 грн
                'price_yearly' => 83200, // 832 грн (економія 30%)
                'max_people' => 200,
                'max_ministries' => 10,
                'max_events_per_month' => 30,
                'max_users' => 10,
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
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'Повний функціонал без обмежень',
                'price_monthly' => 24900, // 249 грн
                'price_yearly' => 209200, // 2092 грн (економія 30%)
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
