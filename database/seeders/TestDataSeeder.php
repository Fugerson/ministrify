<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\User;
use App\Models\Person;
use App\Models\Ministry;
use App\Models\Group;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Expense;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    private $firstNames = [
        'Олександр', 'Максим', 'Артем', 'Дмитро', 'Андрій', 'Іван', 'Микола', 'Сергій', 'Володимир', 'Петро',
        'Олексій', 'Євген', 'Віталій', 'Юрій', 'Роман', 'Павло', 'Ігор', 'Богдан', 'Тарас', 'Олег',
        'Марія', 'Анна', 'Олена', 'Ірина', 'Наталія', 'Тетяна', 'Юлія', 'Світлана', 'Вікторія', 'Катерина',
        'Оксана', 'Людмила', 'Галина', 'Надія', 'Любов', 'Валентина', 'Софія', 'Дарина', 'Аліна', 'Діана'
    ];

    private $lastNames = [
        'Шевченко', 'Бондаренко', 'Коваленко', 'Ткаченко', 'Кравченко', 'Олійник', 'Шевчук', 'Поліщук', 'Бойко', 'Мельник',
        'Ткачук', 'Марченко', 'Савченко', 'Руденко', 'Павленко', 'Лисенко', 'Петренко', 'Кузьменко', 'Мороз', 'Левченко',
        'Гончаренко', 'Клименко', 'Пономаренко', 'Кравчук', 'Харченко', 'Захарченко', 'Семенко', 'Литвиненко', 'Романенко', 'Гриценко'
    ];

    private $streets = [
        'вул. Соборна', 'вул. Шевченка', 'вул. Незалежності', 'вул. Грушевського', 'вул. Франка',
        'вул. Лесі Українки', 'вул. Миру', 'вул. Перемоги', 'просп. Свободи', 'вул. Центральна'
    ];

    public function run(): void
    {
        $churches = Church::all();

        foreach ($churches as $church) {
            $this->seedChurchData($church);
        }

        $this->command->info('Test data seeded successfully!');
    }

    private function seedChurchData(Church $church): void
    {
        $this->command->info("Seeding data for: {$church->name}");

        // Update church with more details
        $church->update([
            'address' => $this->streets[array_rand($this->streets)] . ', ' . rand(1, 150),
            'public_phone' => '+380' . rand(50, 99) . rand(1000000, 9999999),
            'public_email' => 'info@' . \Str::slug($church->name) . '.church.ua',
            'website_url' => 'https://' . \Str::slug($church->name) . '.church.ua',
            'public_description' => 'Ласкаво просимо до нашої церкви! Ми - громада віруючих людей, які прагнуть жити за Божими заповідями та нести любов у світ.',
            'pastor_name' => $this->firstNames[array_rand(array_slice($this->firstNames, 0, 20))] . ' ' . $this->lastNames[array_rand($this->lastNames)],
            'service_times' => "Неділя: 10:00\nСереда: 19:00",
        ]);

        // Create expense categories if not exist
        $this->createExpenseCategories($church);

        // Create income categories if not exist
        $this->createIncomeCategories($church);

        // Create people
        $people = $this->createPeople($church, rand(30, 60));

        // Create ministries
        $ministries = $this->createMinistries($church, $people);

        // Create groups
        $this->createGroups($church, $people);

        // Create events
        $this->createEvents($church, $ministries);

        // Create announcements
        $this->createAnnouncements($church);

        // Create financial records
        $this->createFinancialRecords($church, $ministries);
    }

    private function createExpenseCategories(Church $church): void
    {
        $categories = ['Оренда', 'Комунальні послуги', 'Зарплати', 'Обладнання', 'Благодійність', 'Заходи', 'Транспорт', 'Інше'];

        foreach ($categories as $name) {
            ExpenseCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => $name,
            ]);
        }
    }

    private function createIncomeCategories(Church $church): void
    {
        $categories = ['Десятина', 'Пожертви', 'Спеціальні збори', 'Благодійні внески', 'Інше'];

        foreach ($categories as $name) {
            IncomeCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => $name,
            ]);
        }
    }

    private function createPeople(Church $church, int $count): array
    {
        $people = [];
        $usedEmails = [];

        for ($i = 0; $i < $count; $i++) {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $isMale = in_array($firstName, array_slice($this->firstNames, 0, 20));

            // Generate unique email
            $baseEmail = strtolower(\Str::slug($firstName . '.' . $lastName, '.'));
            $email = $baseEmail . '@example.com';
            $counter = 1;
            while (in_array($email, $usedEmails)) {
                $email = $baseEmail . $counter . '@example.com';
                $counter++;
            }
            $usedEmails[] = $email;

            $birthYear = rand(1960, 2010);

            $person = Person::create([
                'church_id' => $church->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => '+380' . rand(50, 99) . rand(1000000, 9999999),
                'birth_date' => Carbon::create($birthYear, rand(1, 12), rand(1, 28)),
                'address' => $this->streets[array_rand($this->streets)] . ', ' . rand(1, 100) . ', кв. ' . rand(1, 50),
                'joined_date' => Carbon::now()->subDays(rand(30, 1500)),
                'notes' => rand(0, 5) === 0 ? 'Активний член громади' : null,
            ]);

            $people[] = $person;
        }

        return $people;
    }

    private function createMinistries(Church $church, array $people): array
    {
        $ministryData = [
            ['name' => 'Прославлення', 'description' => 'Музичне служіння під час богослужінь', 'color' => '#8B5CF6'],
            ['name' => 'Дитяче служіння', 'description' => 'Недільна школа та заходи для дітей', 'color' => '#F59E0B'],
            ['name' => 'Молодіжне служіння', 'description' => 'Зустрічі та події для молоді', 'color' => '#10B981'],
            ['name' => 'Служіння гостинності', 'description' => 'Зустріч гостей та організація чаювань', 'color' => '#EC4899'],
            ['name' => 'Технічне служіння', 'description' => 'Звук, світло, проекція', 'color' => '#6366F1'],
            ['name' => 'Служіння милосердя', 'description' => 'Допомога нужденним', 'color' => '#EF4444'],
        ];

        $ministries = [];
        $adultPeople = array_values(array_filter($people, fn($p) => $p->birth_date && $p->birth_date->age >= 16));

        foreach ($ministryData as $data) {
            $ministry = Ministry::create([
                'church_id' => $church->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'color' => $data['color'],
            ]);

            // Add random members
            $memberCount = rand(5, 15);
            $members = array_slice($adultPeople, 0, min($memberCount, count($adultPeople)));
            shuffle($adultPeople);

            foreach ($members as $index => $person) {
                $ministry->members()->attach($person->id, [
                    'role' => $index === 0 ? 'leader' : ($index < 3 ? 'coordinator' : 'member'),
                    'joined_at' => now()->subDays(rand(30, 365)),
                ]);
            }

            $ministries[] = $ministry;
        }

        return $ministries;
    }

    private function createGroups(Church $church, array $people): void
    {
        $groupData = [
            ['name' => 'Домашня група "Надія"', 'day' => 'wednesday', 'color' => '#10B981'],
            ['name' => 'Домашня група "Віра"', 'day' => 'thursday', 'color' => '#8B5CF6'],
            ['name' => 'Домашня група "Любов"', 'day' => 'friday', 'color' => '#EC4899'],
            ['name' => 'Молодіжна група', 'day' => 'saturday', 'color' => '#F59E0B'],
            ['name' => 'Жіноче служіння', 'day' => 'tuesday', 'color' => '#EF4444'],
            ['name' => 'Чоловіче братство', 'day' => 'monday', 'color' => '#3B82F6'],
        ];

        $adultPeople = array_values(array_filter($people, fn($p) => $p->birth_date && $p->birth_date->age >= 18));
        if (empty($adultPeople)) return;

        foreach ($groupData as $data) {
            $leader = $adultPeople[array_rand($adultPeople)];

            $group = Group::create([
                'church_id' => $church->id,
                'name' => $data['name'],
                'description' => 'Зустрічі щотижня для спілкування, молитви та вивчення Біблії',
                'meeting_day' => $data['day'],
                'meeting_time' => rand(18, 19) . ':00:00',
                'location' => $this->streets[array_rand($this->streets)] . ', ' . rand(1, 50),
                'leader_id' => $leader->id,
                'color' => $data['color'],
            ]);

            // Add members
            $memberCount = rand(6, 12);
            $shuffled = $adultPeople;
            shuffle($shuffled);
            $members = array_slice($shuffled, 0, $memberCount);

            foreach ($members as $person) {
                $group->members()->attach($person->id, [
                    'joined_at' => now()->subDays(rand(30, 300)),
                ]);
            }
        }
    }

    private function createEvents(Church $church, array $ministries): void
    {
        if (empty($ministries)) return;

        // Create events for next 4 weeks
        for ($week = 0; $week < 4; $week++) {
            $sunday = Carbon::now()->next('Sunday')->addWeeks($week);

            // Sunday service
            Event::create([
                'church_id' => $church->id,
                'title' => 'Недільне богослужіння',
                'notes' => 'Запрошуємо на спільне богослужіння',
                'date' => $sunday->format('Y-m-d'),
                'time' => '10:00:00',
                'ministry_id' => $ministries[0]->id,
            ]);

            // Wednesday prayer
            $wednesday = $sunday->copy()->subDays(4);
            Event::create([
                'church_id' => $church->id,
                'title' => 'Молитовна зустріч',
                'notes' => 'Спільна молитва за церкву та потреби',
                'date' => $wednesday->format('Y-m-d'),
                'time' => '19:00:00',
                'ministry_id' => $ministries[0]->id,
            ]);
        }

        // Special events
        $specialEvents = [
            ['title' => 'Різдвяний концерт', 'days' => rand(10, 30)],
            ['title' => 'Молодіжна конференція', 'days' => rand(40, 60)],
            ['title' => 'День подяки', 'days' => rand(20, 45)],
            ['title' => 'Хрещення', 'days' => rand(15, 35)],
        ];

        foreach ($specialEvents as $index => $event) {
            $date = Carbon::now()->addDays($event['days']);
            Event::create([
                'church_id' => $church->id,
                'title' => $event['title'],
                'notes' => 'Запрошуємо на особливу подію!',
                'date' => $date->format('Y-m-d'),
                'time' => rand(10, 17) . ':00:00',
                'ministry_id' => $ministries[$index % count($ministries)]->id,
            ]);
        }
    }

    private function createAnnouncements(Church $church): void
    {
        $admin = User::where('church_id', $church->id)->where('role', 'admin')->first();
        if (!$admin) return;

        $announcements = [
            [
                'title' => 'Ласкаво просимо!',
                'content' => "Вітаємо вас у нашій церковній спільноті!\n\nМи раді, що ви з нами. Тут ви знайдете всю необхідну інформацію про життя нашої громади, події та можливості для служіння.\n\nЯкщо у вас є питання - звертайтесь до адміністрації.",
                'is_pinned' => true,
            ],
            [
                'title' => 'Розклад богослужінь',
                'content' => "Недільні богослужіння: 10:00\nМолитовні зустрічі: середа 19:00\nБіблійні студії: п'ятниця 18:30\n\nЧекаємо на вас!",
                'is_pinned' => true,
            ],
            [
                'title' => 'Збір продуктів для нужденних',
                'content' => "Наше служіння милосердя організовує збір продуктів для малозабезпечених сімей.\n\nПриносьте: крупи, консерви, олію, цукор, борошно.\n\nПункт збору: фойє церкви.",
                'is_pinned' => false,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create([
                'church_id' => $church->id,
                'author_id' => $admin->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'is_pinned' => $data['is_pinned'],
                'is_published' => true,
                'published_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    private function createFinancialRecords(Church $church, array $ministries): void
    {
        $incomeCategories = IncomeCategory::where('church_id', $church->id)->get();
        $expenseCategories = ExpenseCategory::where('church_id', $church->id)->get();

        $admin = User::where('church_id', $church->id)->where('role', 'admin')->first();
        if (!$admin || $incomeCategories->isEmpty() || $expenseCategories->isEmpty() || empty($ministries)) return;

        // Create incomes for last 3 months
        for ($month = 0; $month < 3; $month++) {
            $date = Carbon::now()->subMonths($month);

            // Weekly tithes (4 Sundays per month)
            for ($week = 0; $week < 4; $week++) {
                $titheCategory = $incomeCategories->where('name', 'Десятина')->first();
                if ($titheCategory) {
                    Income::create([
                        'church_id' => $church->id,
                        'category_id' => $titheCategory->id,
                        'user_id' => $admin->id,
                        'amount' => rand(5000, 15000),
                        'description' => 'Недільна десятина',
                        'date' => $date->copy()->startOfMonth()->addDays($week * 7),
                        'payment_method' => 'cash',
                    ]);
                }

                $donationCategory = $incomeCategories->where('name', 'Пожертви')->first();
                if ($donationCategory) {
                    Income::create([
                        'church_id' => $church->id,
                        'category_id' => $donationCategory->id,
                        'user_id' => $admin->id,
                        'amount' => rand(1000, 5000),
                        'description' => 'Пожертви',
                        'date' => $date->copy()->startOfMonth()->addDays($week * 7),
                        'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                    ]);
                }
            }
        }

        // Create expenses for last 3 months
        for ($month = 0; $month < 3; $month++) {
            $date = Carbon::now()->subMonths($month);

            // Rent
            $rentCategory = $expenseCategories->where('name', 'Оренда')->first();
            if ($rentCategory) {
                Expense::create([
                    'church_id' => $church->id,
                    'ministry_id' => $ministries[0]->id,
                    'user_id' => $admin->id,
                    'category_id' => $rentCategory->id,
                    'amount' => rand(8000, 15000),
                    'description' => 'Оренда приміщення',
                    'date' => $date->copy()->startOfMonth()->addDays(5),
                ]);
            }

            // Utilities
            $utilityCategory = $expenseCategories->where('name', 'Комунальні послуги')->first();
            if ($utilityCategory) {
                Expense::create([
                    'church_id' => $church->id,
                    'ministry_id' => $ministries[0]->id,
                    'user_id' => $admin->id,
                    'category_id' => $utilityCategory->id,
                    'amount' => rand(2000, 5000),
                    'description' => 'Комунальні платежі',
                    'date' => $date->copy()->startOfMonth()->addDays(10),
                ]);
            }

            // Random expenses
            $randomCategories = $expenseCategories->whereNotIn('name', ['Оренда', 'Комунальні послуги']);
            foreach ($randomCategories->take(rand(2, 4)) as $index => $category) {
                Expense::create([
                    'church_id' => $church->id,
                    'ministry_id' => $ministries[$index % count($ministries)]->id,
                    'user_id' => $admin->id,
                    'category_id' => $category->id,
                    'amount' => rand(500, 3000),
                    'description' => $category->name,
                    'date' => $date->copy()->startOfMonth()->addDays(rand(1, 28)),
                ]);
            }
        }
    }
}
