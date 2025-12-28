<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\ExpenseCategory;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo church
        $church = Church::create([
            'name' => 'Церква "Благодать"',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
            'settings' => [
                'notifications' => [
                    'reminder_day_before' => true,
                    'reminder_same_day' => true,
                    'notify_leader_on_decline' => true,
                ],
            ],
        ]);

        // Create admin user
        $admin = User::create([
            'church_id' => $church->id,
            'name' => 'Адміністратор',
            'email' => 'admin@ministrify.app',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create tags
        $tags = [
            ['name' => 'Волонтер', 'color' => '#3b82f6'],
            ['name' => 'Лідер', 'color' => '#22c55e'],
            ['name' => 'Новий', 'color' => '#f59e0b'],
            ['name' => 'Член церкви', 'color' => '#8b5cf6'],
        ];

        foreach ($tags as $tag) {
            Tag::create(['church_id' => $church->id, ...$tag]);
        }

        // Create expense categories
        $categories = ['Обладнання', 'Витратні матеріали', 'Їжа та напої', 'Оренда', 'Транспорт', 'Інше'];
        foreach ($categories as $category) {
            ExpenseCategory::create(['church_id' => $church->id, 'name' => $category]);
        }

        // Create people
        $people = [
            ['first_name' => 'Анна', 'last_name' => 'Коваль', 'phone' => '+380671234567'],
            ['first_name' => 'Петро', 'last_name' => 'Шевченко', 'phone' => '+380672345678'],
            ['first_name' => 'Марія', 'last_name' => 'Бойко', 'phone' => '+380673456789'],
            ['first_name' => 'Іван', 'last_name' => 'Мельник', 'phone' => '+380674567890'],
            ['first_name' => 'Світлана', 'last_name' => 'Лисенко', 'phone' => '+380675678901'],
        ];

        $createdPeople = [];
        foreach ($people as $personData) {
            $createdPeople[] = Person::create(['church_id' => $church->id, ...$personData]);
        }

        // Create ministries with positions
        $ministries = [
            [
                'name' => 'Worship',
                'icon' => '🎵',
                'color' => '#3b82f6',
                'monthly_budget' => 5000,
                'positions' => ['Worship leader', 'Вокал', 'Гітара', 'Клавіші', 'Бас', 'Барабани', 'Звук', 'Проектор'],
            ],
            [
                'name' => 'Дитяче служіння',
                'icon' => '👶',
                'color' => '#22c55e',
                'monthly_budget' => 3000,
                'positions' => ['Вчитель', 'Помічник', 'Реєстрація'],
            ],
            [
                'name' => 'Молодіжне служіння',
                'icon' => '🎤',
                'color' => '#8b5cf6',
                'monthly_budget' => 2000,
                'positions' => ['Лідер', 'Worship', 'Техніка'],
            ],
        ];

        foreach ($ministries as $index => $ministryData) {
            $positions = $ministryData['positions'];
            unset($ministryData['positions']);

            $ministry = Ministry::create([
                'church_id' => $church->id,
                'leader_id' => $createdPeople[$index]->id ?? null,
                ...$ministryData,
            ]);

            foreach ($positions as $order => $positionName) {
                Position::create([
                    'ministry_id' => $ministry->id,
                    'name' => $positionName,
                    'sort_order' => $order,
                ]);
            }

            // Add some members
            foreach ($createdPeople as $person) {
                if (rand(0, 1)) {
                    $positionIds = $ministry->positions->random(rand(1, 2))->pluck('id')->toArray();
                    $ministry->members()->attach($person->id, [
                        'position_ids' => json_encode($positionIds),
                    ]);
                }
            }
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Login: admin@ministrify.app / password');
    }
}
