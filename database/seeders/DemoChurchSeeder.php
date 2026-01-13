<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\EventResponsibility;
use App\Models\ExpenseCategory;
use App\Models\FamilyRelationship;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\MinistryType;
use App\Models\Person;
use App\Models\Position;
use App\Models\ServicePlanItem;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoChurchSeeder extends Seeder
{
    private Church $church;
    private array $people = [];
    private array $ministries = [];
    private array $positions = [];
    private array $groups = [];
    private array $events = [];
    private array $tags = [];
    private array $ministryTypes = [];
    private User $admin;

    public function run(): void
    {
        $this->command->info('Creating Demo Church with complete data...');

        // 1. Create Church
        $this->createChurch();

        // 2. Create Church Roles
        $this->createChurchRoles();

        // 3. Create Tags
        $this->createTags();

        // 4. Create Admin User
        $this->createAdminUser();

        // 5. Create People (20+ with various statuses)
        $this->createPeople();

        // 6. Create Ministry Types
        $this->createMinistryTypes();

        // 7. Create Ministries with Positions
        $this->createMinistries();

        // 8. Create Groups
        $this->createGroups();

        // 9. Create Events with Service Plans
        $this->createEvents();

        // 10. Create Assignments
        $this->createAssignments();

        // 11. Create Transaction Categories
        $this->createTransactionCategories();

        // 12. Create Transactions (Tithes, Offerings, Expenses)
        $this->createTransactions();

        // 13. Create Donation Campaign
        $this->createDonationCampaigns();

        // 14. Create Attendance Records
        $this->createAttendanceRecords();

        // 15. Create Announcements
        $this->createAnnouncements();

        // 16. Create Family Relationships
        $this->createFamilyRelationships();

        $this->command->info('Demo Church created successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Email: demo@ministrify.church');
        $this->command->info('Password: demo2024');
        $this->command->info('Public URL: ' . url('/church/' . $this->church->slug));
    }

    private function createChurch(): void
    {
        $this->command->info('Creating church...');

        $this->church = Church::create([
            'name' => 'Церква "Благодать"',
            'slug' => 'demo-blagodat',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
            'primary_color' => '#3b82f6',
            'theme' => 'modern',
            'public_site_enabled' => true,
            'public_template' => 'modern',
            'public_description' => 'Ласкаво просимо до церкви "Благодать"! Ми - дружня громада віруючих людей, які прагнуть жити за Божим Словом та нести любов і надію в наш світ. Приєднуйтесь до нас на богослужіннях та заходах!',
            'public_email' => 'info@blagodat.church',
            'public_phone' => '+380 44 123 45 67',
            'website_url' => 'https://blagodat.church',
            'facebook_url' => 'https://facebook.com/blagodat.church',
            'instagram_url' => 'https://instagram.com/blagodat.church',
            'youtube_url' => 'https://youtube.com/@blagodat.church',
            'service_times' => "Неділя: 10:00 - Ранкове богослужіння\nНеділя: 18:00 - Вечірнє богослужіння\nСереда: 19:00 - Молитовна зустріч\nП'ятниця: 19:00 - Молодіжна зустріч",
            'pastor_name' => 'Олександр Петренко',
            'pastor_message' => 'Дорогі друзі! Ми раді вітати вас у нашій церкві. Тут ви знайдете тепло, підтримку та справжню духовну сім\'ю. Приходьте такими, якими є - Бог любить кожного з нас!',
            'shepherds_enabled' => true,
            'attendance_enabled' => true,
            'initial_balance' => 50000,
            'initial_balance_date' => Carbon::now()->subYear()->startOfYear(),
            'public_site_settings' => [
                'sections' => [
                    ['id' => 'hero', 'enabled' => true, 'order' => 0],
                    ['id' => 'service_times', 'enabled' => true, 'order' => 1],
                    ['id' => 'about', 'enabled' => true, 'order' => 2],
                    ['id' => 'pastor_message', 'enabled' => true, 'order' => 3],
                    ['id' => 'events', 'enabled' => true, 'order' => 4],
                    ['id' => 'ministries', 'enabled' => true, 'order' => 5],
                    ['id' => 'groups', 'enabled' => true, 'order' => 6],
                    ['id' => 'donations', 'enabled' => true, 'order' => 7],
                    ['id' => 'contact', 'enabled' => true, 'order' => 8],
                ],
                'about' => [
                    'mission' => 'Наша місія - допомогти людям зустріти Бога та зростати у вірі через живе спілкування та служіння.',
                    'vision' => 'Бачимо церкву, яка змінює життя людей та громади через любов Христа.',
                    'values' => ['Віра', 'Любов', 'Служіння', 'Спільнота', 'Зростання'],
                ],
                'colors' => [
                    'primary' => '#3b82f6',
                    'secondary' => '#10b981',
                ],
            ],
        ]);
    }

    private function createChurchRoles(): void
    {
        $this->command->info('Creating church roles...');

        $roles = [
            ['name' => 'Член церкви', 'slug' => 'member', 'color' => '#6b7280', 'is_admin_role' => false, 'sort_order' => 1],
            ['name' => 'Служитель', 'slug' => 'servant', 'color' => '#3b82f6', 'is_admin_role' => false, 'sort_order' => 2],
            ['name' => 'Лідер служіння', 'slug' => 'leader', 'color' => '#8b5cf6', 'is_admin_role' => false, 'sort_order' => 3],
            ['name' => 'Диякон', 'slug' => 'deacon', 'color' => '#f59e0b', 'is_admin_role' => false, 'sort_order' => 4],
            ['name' => 'Пресвітер', 'slug' => 'presbyter', 'color' => '#ef4444', 'is_admin_role' => true, 'sort_order' => 5],
            ['name' => 'Пастор', 'slug' => 'pastor', 'color' => '#10b981', 'is_admin_role' => true, 'sort_order' => 6],
        ];

        foreach ($roles as $role) {
            ChurchRole::create(array_merge($role, ['church_id' => $this->church->id]));
        }
    }

    private function createTags(): void
    {
        $this->command->info('Creating tags...');

        $tagData = [
            ['name' => 'Волонтер', 'color' => '#3b82f6'],
            ['name' => 'Новачок', 'color' => '#f59e0b'],
            ['name' => 'Потребує допомоги', 'color' => '#ef4444'],
            ['name' => 'Музикант', 'color' => '#8b5cf6'],
            ['name' => 'Молитовник', 'color' => '#10b981'],
            ['name' => 'Учитель', 'color' => '#ec4899'],
        ];

        foreach ($tagData as $tag) {
            $this->tags[] = Tag::create(array_merge($tag, ['church_id' => $this->church->id]));
        }
    }

    private function createAdminUser(): void
    {
        $this->command->info('Creating admin user...');

        $this->admin = User::create([
            'name' => 'Демо Адміністратор',
            'email' => 'demo@ministrify.church',
            'password' => Hash::make('demo2024'),
            'church_id' => $this->church->id,
            'role' => 'admin',
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);
    }

    private function createPeople(): void
    {
        $this->command->info('Creating people...');

        $peopleData = [
            // Пастор / Лідери
            ['first_name' => 'Олександр', 'last_name' => 'Петренко', 'gender' => 'male', 'church_role' => 'pastor', 'membership_status' => 'active', 'is_shepherd' => true, 'email' => 'pastor@blagodat.church', 'phone' => '+380501234567', 'birth_date' => '1975-03-15', 'marital_status' => 'married', 'baptism_date' => '1995-06-20', 'joined_date' => '2010-01-01'],
            ['first_name' => 'Наталія', 'last_name' => 'Петренко', 'gender' => 'female', 'church_role' => 'deacon', 'membership_status' => 'active', 'email' => 'natalia@blagodat.church', 'phone' => '+380501234568', 'birth_date' => '1978-07-22', 'marital_status' => 'married', 'baptism_date' => '1998-08-15', 'joined_date' => '2010-01-01'],

            // Пресвітери
            ['first_name' => 'Михайло', 'last_name' => 'Коваленко', 'gender' => 'male', 'church_role' => 'presbyter', 'membership_status' => 'active', 'is_shepherd' => true, 'email' => 'mikhail@blagodat.church', 'phone' => '+380502345678', 'birth_date' => '1968-11-08', 'marital_status' => 'married', 'baptism_date' => '1990-04-12', 'joined_date' => '2012-03-15'],
            ['first_name' => 'Людмила', 'last_name' => 'Коваленко', 'gender' => 'female', 'church_role' => 'deacon', 'membership_status' => 'active', 'email' => 'ludmila@blagodat.church', 'phone' => '+380502345679', 'birth_date' => '1970-05-18', 'marital_status' => 'married', 'baptism_date' => '1992-06-28', 'joined_date' => '2012-03-15'],

            // Дякони
            ['first_name' => 'Андрій', 'last_name' => 'Шевченко', 'gender' => 'male', 'church_role' => 'deacon', 'membership_status' => 'active', 'is_shepherd' => true, 'email' => 'andrii@blagodat.church', 'phone' => '+380503456789', 'birth_date' => '1985-02-28', 'marital_status' => 'married', 'baptism_date' => '2005-09-10', 'joined_date' => '2015-06-01'],
            ['first_name' => 'Оксана', 'last_name' => 'Шевченко', 'gender' => 'female', 'church_role' => 'servant', 'membership_status' => 'active', 'email' => 'oksana@blagodat.church', 'phone' => '+380503456790', 'birth_date' => '1988-09-14', 'marital_status' => 'married', 'baptism_date' => '2008-05-25', 'joined_date' => '2015-06-01'],

            // Лідер молоді
            ['first_name' => 'Дмитро', 'last_name' => 'Бондаренко', 'gender' => 'male', 'church_role' => 'servant', 'membership_status' => 'active', 'email' => 'dmytro@blagodat.church', 'phone' => '+380504567890', 'birth_date' => '1995-12-03', 'marital_status' => 'single', 'baptism_date' => '2013-08-18', 'joined_date' => '2018-01-15'],

            // Лідер прославлення
            ['first_name' => 'Катерина', 'last_name' => 'Мельник', 'gender' => 'female', 'church_role' => 'servant', 'membership_status' => 'active', 'email' => 'kateryna@blagodat.church', 'phone' => '+380505678901', 'birth_date' => '1992-04-25', 'marital_status' => 'married', 'baptism_date' => '2010-07-14', 'joined_date' => '2016-09-01'],

            // Активні члени
            ['first_name' => 'Віктор', 'last_name' => 'Ткаченко', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'active', 'email' => 'viktor@example.com', 'phone' => '+380506789012', 'birth_date' => '1980-06-17', 'marital_status' => 'married', 'baptism_date' => '2002-03-24', 'joined_date' => '2019-02-10'],
            ['first_name' => 'Ірина', 'last_name' => 'Ткаченко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'active', 'email' => 'iryna@example.com', 'phone' => '+380506789013', 'birth_date' => '1983-10-30', 'marital_status' => 'married', 'baptism_date' => '2004-11-07', 'joined_date' => '2019-02-10'],
            ['first_name' => 'Сергій', 'last_name' => 'Олійник', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'active', 'email' => 'serhii@example.com', 'phone' => '+380507890123', 'birth_date' => '1990-01-12', 'marital_status' => 'single', 'baptism_date' => '2012-04-08', 'joined_date' => '2020-03-01'],
            ['first_name' => 'Марія', 'last_name' => 'Савченко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'active', 'email' => 'mariia@example.com', 'phone' => '+380508901234', 'birth_date' => '1997-08-05', 'marital_status' => 'single', 'baptism_date' => '2016-06-19', 'joined_date' => '2020-05-15'],

            // Члени церкви
            ['first_name' => 'Петро', 'last_name' => 'Руденко', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'member', 'email' => 'petro@example.com', 'phone' => '+380509012345', 'birth_date' => '1972-03-28', 'marital_status' => 'married', 'baptism_date' => '1994-09-11', 'joined_date' => '2021-01-10'],
            ['first_name' => 'Галина', 'last_name' => 'Руденко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'member', 'email' => 'halyna@example.com', 'phone' => '+380509012346', 'birth_date' => '1975-11-15', 'marital_status' => 'married', 'baptism_date' => '1996-05-26', 'joined_date' => '2021-01-10'],
            ['first_name' => 'Юлія', 'last_name' => 'Павленко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'member', 'email' => 'yulia@example.com', 'phone' => '+380510123456', 'birth_date' => '2000-07-22', 'marital_status' => 'single', 'first_visit_date' => '2022-06-05', 'joined_date' => '2022-09-01'],

            // Новоприбулі
            ['first_name' => 'Олег', 'last_name' => 'Литвиненко', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'newcomer', 'email' => 'oleh@example.com', 'phone' => '+380511234567', 'birth_date' => '1987-04-10', 'marital_status' => 'married', 'first_visit_date' => '2023-10-15'],
            ['first_name' => 'Тетяна', 'last_name' => 'Литвиненко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'newcomer', 'email' => 'tetiana@example.com', 'phone' => '+380511234568', 'birth_date' => '1990-12-08', 'marital_status' => 'married', 'first_visit_date' => '2023-10-15'],

            // Гості
            ['first_name' => 'Роман', 'last_name' => 'Кравчук', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'guest', 'phone' => '+380512345678', 'birth_date' => '1994-06-30', 'first_visit_date' => '2024-01-07'],
            ['first_name' => 'Анна', 'last_name' => 'Гончаренко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'guest', 'email' => 'anna@example.com', 'first_visit_date' => '2024-01-14'],

            // Молодь
            ['first_name' => 'Максим', 'last_name' => 'Бойко', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'active', 'email' => 'maxim@example.com', 'phone' => '+380513456789', 'birth_date' => '2002-02-14', 'marital_status' => 'single', 'baptism_date' => '2020-08-16', 'joined_date' => '2019-09-01'],
            ['first_name' => 'Софія', 'last_name' => 'Марченко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'active', 'email' => 'sofia@example.com', 'phone' => '+380514567890', 'birth_date' => '2003-09-20', 'marital_status' => 'single', 'baptism_date' => '2021-07-11', 'joined_date' => '2020-01-15'],

            // Діти (для статистики)
            ['first_name' => 'Даніїл', 'last_name' => 'Шевченко', 'gender' => 'male', 'church_role' => 'member', 'membership_status' => 'member', 'birth_date' => '2015-03-08'],
            ['first_name' => 'Єва', 'last_name' => 'Шевченко', 'gender' => 'female', 'church_role' => 'member', 'membership_status' => 'member', 'birth_date' => '2018-11-25'],
        ];

        foreach ($peopleData as $data) {
            $data['church_id'] = $this->church->id;

            if (isset($data['birth_date'])) {
                $data['birth_date'] = Carbon::parse($data['birth_date']);
            }
            if (isset($data['baptism_date'])) {
                $data['baptism_date'] = Carbon::parse($data['baptism_date']);
            }
            if (isset($data['joined_date'])) {
                $data['joined_date'] = Carbon::parse($data['joined_date']);
            }
            if (isset($data['first_visit_date'])) {
                $data['first_visit_date'] = Carbon::parse($data['first_visit_date']);
            }

            $person = Person::create($data);
            $this->people[$person->last_name . '_' . $person->first_name] = $person;
        }

        // Link admin user to pastor (Person has user_id, not User has person_id)
        $pastor = $this->people['Петренко_Олександр'];
        $pastor->update(['user_id' => $this->admin->id]);

        // Assign tags
        $this->people['Бондаренко_Дмитро']->tags()->attach([$this->tags[0]->id, $this->tags[3]->id]); // Волонтер, Музикант
        $this->people['Мельник_Катерина']->tags()->attach([$this->tags[3]->id]); // Музикант
        $this->people['Савченко_Марія']->tags()->attach([$this->tags[0]->id, $this->tags[4]->id]); // Волонтер, Молитовник
        $this->people['Кравчук_Роман']->tags()->attach([$this->tags[1]->id]); // Новачок
        $this->people['Гончаренко_Анна']->tags()->attach([$this->tags[1]->id]); // Новачок
        $this->people['Петренко_Наталія']->tags()->attach([$this->tags[5]->id]); // Учитель
        $this->people['Коваленко_Людмила']->tags()->attach([$this->tags[5]->id, $this->tags[4]->id]); // Учитель, Молитовник

        // Assign shepherds
        $this->people['Шевченко_Андрій']->update(['shepherd_id' => $this->people['Петренко_Олександр']->id]);
        $this->people['Бондаренко_Дмитро']->update(['shepherd_id' => $this->people['Коваленко_Михайло']->id]);
        $this->people['Литвиненко_Олег']->update(['shepherd_id' => $this->people['Шевченко_Андрій']->id]);
        $this->people['Литвиненко_Тетяна']->update(['shepherd_id' => $this->people['Шевченко_Андрій']->id]);
    }

    private function createMinistryTypes(): void
    {
        $this->command->info('Creating ministry types...');

        $types = [
            ['name' => 'Музичне служіння', 'sort_order' => 1],
            ['name' => 'Молодіжне служіння', 'sort_order' => 2],
            ['name' => 'Дитяче служіння', 'sort_order' => 3],
            ['name' => 'Технічне служіння', 'sort_order' => 4],
            ['name' => 'Служіння гостинності', 'sort_order' => 5],
            ['name' => 'Молитовне служіння', 'sort_order' => 6],
        ];

        foreach ($types as $index => $type) {
            $ministryType = MinistryType::create(array_merge($type, ['church_id' => $this->church->id]));
            $this->ministryTypes[$index + 1] = $ministryType;
        }
    }

    private function createMinistries(): void
    {
        $this->command->info('Creating ministries with positions...');

        $ministryData = [
            [
                'name' => 'Прославлення',
                'description' => 'Музичне служіння під час богослужінь. Вокал, інструменти, звук.',
                'icon' => 'musical-note',
                'color' => '#8b5cf6',
                'leader' => 'Мельник_Катерина',
                'type' => 1,
                'is_public' => true,
                'monthly_budget' => 5000,
                'positions' => [
                    ['name' => 'Вокаліст', 'sort_order' => 1],
                    ['name' => 'Гітарист', 'sort_order' => 2],
                    ['name' => 'Клавішник', 'sort_order' => 3],
                    ['name' => 'Басист', 'sort_order' => 4],
                    ['name' => 'Барабанщик', 'sort_order' => 5],
                ],
                'members' => ['Бондаренко_Дмитро', 'Олійник_Сергій', 'Савченко_Марія', 'Бойко_Максим', 'Марченко_Софія'],
            ],
            [
                'name' => 'Молодіжне служіння',
                'description' => 'Зустрічі та події для молоді 16-35 років. Дискусії, ігри, спілкування.',
                'icon' => 'users',
                'color' => '#3b82f6',
                'leader' => 'Бондаренко_Дмитро',
                'type' => 2,
                'is_public' => true,
                'monthly_budget' => 3000,
                'positions' => [
                    ['name' => 'Ведучий', 'sort_order' => 1],
                    ['name' => 'Організатор ігор', 'sort_order' => 2],
                    ['name' => 'Відповідальний за чай', 'sort_order' => 3],
                ],
                'members' => ['Савченко_Марія', 'Павленко_Юлія', 'Бойко_Максим', 'Марченко_Софія', 'Олійник_Сергій'],
            ],
            [
                'name' => 'Дитяче служіння',
                'description' => 'Недільна школа для дітей від 3 до 12 років. Уроки, творчість, ігри.',
                'icon' => 'academic-cap',
                'color' => '#f59e0b',
                'leader' => 'Петренко_Наталія',
                'type' => 3,
                'is_public' => true,
                'monthly_budget' => 2000,
                'positions' => [
                    ['name' => 'Вчитель', 'sort_order' => 1],
                    ['name' => 'Помічник вчителя', 'sort_order' => 2],
                    ['name' => 'Аніматор', 'sort_order' => 3],
                ],
                'members' => ['Коваленко_Людмила', 'Шевченко_Оксана', 'Ткаченко_Ірина'],
            ],
            [
                'name' => 'Технічне служіння',
                'description' => 'Звук, світло, проекція, трансляції. Технічне забезпечення богослужінь.',
                'icon' => 'computer-desktop',
                'color' => '#6366f1',
                'leader' => 'Олійник_Сергій',
                'type' => 4,
                'is_public' => false,
                'monthly_budget' => 4000,
                'positions' => [
                    ['name' => 'Звукорежисер', 'sort_order' => 1],
                    ['name' => 'Оператор проекції', 'sort_order' => 2],
                    ['name' => 'Оператор трансляції', 'sort_order' => 3],
                ],
                'members' => ['Ткаченко_Віктор', 'Бойко_Максим'],
            ],
            [
                'name' => 'Служіння гостинності',
                'description' => 'Зустріч гостей, організація чаювань, допомога новачкам.',
                'icon' => 'heart',
                'color' => '#ec4899',
                'leader' => 'Коваленко_Людмила',
                'type' => 5,
                'is_public' => true,
                'monthly_budget' => 1500,
                'positions' => [
                    ['name' => 'Зустрічаючий', 'sort_order' => 1],
                    ['name' => 'Відповідальний за чай', 'sort_order' => 2],
                ],
                'members' => ['Руденко_Галина', 'Шевченко_Оксана', 'Ткаченко_Ірина', 'Савченко_Марія'],
            ],
        ];

        foreach ($ministryData as $data) {
            $ministry = Ministry::create([
                'church_id' => $this->church->id,
                'type_id' => $this->ministryTypes[$data['type']]->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'icon' => $data['icon'],
                'color' => $data['color'],
                'leader_id' => $this->people[$data['leader']]->id,
                'monthly_budget' => $data['monthly_budget'],
                'is_public' => $data['is_public'],
                'slug' => Str::slug($data['name']),
                'public_description' => $data['description'],
            ]);

            $this->ministries[$data['name']] = $ministry;

            // Create positions
            foreach ($data['positions'] as $posData) {
                $position = Position::create(array_merge($posData, ['ministry_id' => $ministry->id]));
                $this->positions[$ministry->name . '_' . $posData['name']] = $position;
            }

            // Add members with positions
            foreach ($data['members'] as $index => $memberKey) {
                $person = $this->people[$memberKey];
                $positionIds = [];

                // Assign first position to first member, etc.
                if (isset($data['positions'][$index])) {
                    $posKey = $ministry->name . '_' . $data['positions'][$index]['name'];
                    if (isset($this->positions[$posKey])) {
                        $positionIds[] = $this->positions[$posKey]->id;
                    }
                }

                $ministry->members()->attach($person->id, [
                    'position_ids' => json_encode($positionIds),
                ]);
            }
        }
    }

    private function createGroups(): void
    {
        $this->command->info('Creating groups...');

        $groupsData = [
            [
                'name' => 'Домашня група "Надія"',
                'description' => 'Щотижневі зустрічі для вивчення Біблії, спілкування та молитви.',
                'leader' => 'Шевченко_Андрій',
                'meeting_day' => 'tuesday',
                'meeting_time' => '19:00',
                'location' => 'вул. Хрещатик, 25, кв. 12',
                'color' => '#10b981',
                'is_public' => true,
                'members' => ['Шевченко_Оксана', 'Ткаченко_Віктор', 'Ткаченко_Ірина', 'Олійник_Сергій', 'Литвиненко_Олег', 'Литвиненко_Тетяна'],
            ],
            [
                'name' => 'Домашня група "Віра"',
                'description' => 'Вивчення Слова Божого та взаємна підтримка.',
                'leader' => 'Коваленко_Михайло',
                'meeting_day' => 'thursday',
                'meeting_time' => '18:30',
                'location' => 'вул. Франка, 10, кв. 5',
                'color' => '#8b5cf6',
                'is_public' => true,
                'members' => ['Коваленко_Людмила', 'Руденко_Петро', 'Руденко_Галина', 'Савченко_Марія'],
            ],
            [
                'name' => 'Молодіжна група',
                'description' => 'Неформальні зустрічі молоді для спілкування та зростання у вірі.',
                'leader' => 'Бондаренко_Дмитро',
                'meeting_day' => 'saturday',
                'meeting_time' => '17:00',
                'location' => 'Церква, молодіжна кімната',
                'color' => '#3b82f6',
                'is_public' => true,
                'members' => ['Павленко_Юлія', 'Бойко_Максим', 'Марченко_Софія'],
            ],
        ];

        foreach ($groupsData as $data) {
            $group = Group::create([
                'church_id' => $this->church->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'leader_id' => $this->people[$data['leader']]->id,
                'meeting_day' => $data['meeting_day'],
                'meeting_time' => $data['meeting_time'],
                'location' => $data['location'],
                'color' => $data['color'],
                'is_public' => $data['is_public'],
                'slug' => Str::slug($data['name']),
                'status' => 'active',
            ]);

            $this->groups[$data['name']] = $group;

            // Add members
            foreach ($data['members'] as $memberKey) {
                $group->members()->attach($this->people[$memberKey]->id, [
                    'role' => 'member',
                    'joined_at' => now()->subMonths(rand(1, 12)),
                ]);
            }
        }
    }

    private function createEvents(): void
    {
        $this->command->info('Creating events...');

        // Past Sunday services (last 4 weeks)
        for ($i = 4; $i >= 1; $i--) {
            $date = Carbon::now()->subWeeks($i)->startOfWeek()->addDays(6); // Sunday
            $event = Event::create([
                'church_id' => $this->church->id,
                'ministry_id' => $this->ministries['Прославлення']->id,
                'title' => 'Недільне богослужіння',
                'date' => $date,
                'time' => '10:00',
                'is_service' => true,
                'service_type' => Event::SERVICE_SUNDAY,
                'is_public' => true,
                'track_attendance' => true,
                'location' => 'Головний зал',
                'notes' => 'Регулярне недільне богослужіння',
            ]);
            $this->events['sunday_past_' . $i] = $event;

            // Add service plan
            $this->createServicePlan($event);
        }

        // Upcoming events (next 4 weeks)
        for ($i = 0; $i <= 3; $i++) {
            $date = Carbon::now()->addWeeks($i)->startOfWeek()->addDays(6); // Sunday

            $event = Event::create([
                'church_id' => $this->church->id,
                'ministry_id' => $this->ministries['Прославлення']->id,
                'title' => 'Недільне богослужіння',
                'date' => $date,
                'time' => '10:00',
                'is_service' => true,
                'service_type' => Event::SERVICE_SUNDAY,
                'is_public' => true,
                'track_attendance' => true,
                'location' => 'Головний зал',
                'notes' => 'Регулярне недільне богослужіння',
            ]);
            $this->events['sunday_' . $i] = $event;

            if ($i === 0) {
                $this->createServicePlan($event);
            }
        }

        // Youth meeting (every Friday)
        $fridayDate = Carbon::now()->next('Friday');
        $youthEvent = Event::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministries['Молодіжне служіння']->id,
            'title' => 'Молодіжна зустріч',
            'date' => $fridayDate,
            'time' => '19:00',
            'is_service' => false,
            'is_public' => true,
            'allow_registration' => true,
            'registration_limit' => 50,
            'location' => 'Молодіжна кімната',
            'public_description' => 'Запрошуємо всіх молодих людей на наші щотижневі зустрічі!',
            'notes' => 'Тема: "Віра в дії"',
        ]);
        $this->events['youth'] = $youthEvent;

        // Prayer meeting (Wednesday)
        $wednesdayDate = Carbon::now()->next('Wednesday');
        Event::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministries['Прославлення']->id,
            'title' => 'Молитовна зустріч',
            'date' => $wednesdayDate,
            'time' => '19:00',
            'is_service' => true,
            'service_type' => Event::SERVICE_PRAYER,
            'is_public' => false,
            'location' => 'Молитовна кімната',
            'notes' => 'Спільна молитва за церкву та потреби',
        ]);

        // Special event - Conference
        Event::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministries['Прославлення']->id,
            'title' => 'Весняна конференція "Нове життя"',
            'date' => Carbon::now()->addMonths(2),
            'time' => '10:00',
            'is_service' => true,
            'service_type' => Event::SERVICE_SPECIAL,
            'is_public' => true,
            'allow_registration' => true,
            'registration_limit' => 200,
            'location' => 'Головний зал',
            'public_description' => 'Запрошуємо на щорічну весняну конференцію! Три дні потужних проповідей, прославлення та спілкування.',
            'notes' => 'Запросити гостей-проповідників. Організувати обіди.',
        ]);

        // Baptism
        Event::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministries['Прославлення']->id,
            'title' => 'Хрещення',
            'date' => Carbon::now()->addMonths(1)->endOfMonth()->previous('Sunday'),
            'time' => '11:00',
            'is_service' => true,
            'service_type' => Event::SERVICE_SPECIAL,
            'is_public' => true,
            'location' => 'Головний зал',
            'public_description' => 'Святкове богослужіння з хрещенням нових членів церкви.',
            'notes' => 'Підготувати басейн. Запросити охочих до хрещення.',
        ]);
    }

    private function createServicePlan(Event $event): void
    {
        $planItems = [
            ['title' => 'Вступне слово', 'type' => 'announcement', 'duration' => 5, 'sort_order' => 1],
            ['title' => 'Прославлення (блок 1)', 'type' => 'song', 'duration' => 15, 'sort_order' => 2],
            ['title' => 'Молитва', 'type' => 'prayer', 'duration' => 5, 'sort_order' => 3],
            ['title' => 'Прославлення (блок 2)', 'type' => 'song', 'duration' => 10, 'sort_order' => 4],
            ['title' => 'Оголошення', 'type' => 'announcement', 'duration' => 5, 'sort_order' => 5],
            ['title' => 'Десятина та пожертви', 'type' => 'offering', 'duration' => 5, 'sort_order' => 6],
            ['title' => 'Проповідь', 'type' => 'sermon', 'duration' => 40, 'sort_order' => 7],
            ['title' => 'Заключна молитва', 'type' => 'prayer', 'duration' => 5, 'sort_order' => 8],
            ['title' => 'Благословення', 'type' => 'blessing', 'duration' => 2, 'sort_order' => 9],
        ];

        $startTime = Carbon::parse($event->time);

        foreach ($planItems as $item) {
            ServicePlanItem::create([
                'event_id' => $event->id,
                'title' => $item['title'],
                'type' => $item['type'],
                'start_time' => $startTime->format('H:i'),
                'end_time' => $startTime->copy()->addMinutes($item['duration'])->format('H:i'),
                'sort_order' => $item['sort_order'],
                'status' => 'planned',
                'responsible_id' => $item['type'] === 'sermon' ? $this->people['Петренко_Олександр']->id : null,
            ]);
            $startTime->addMinutes($item['duration']);
        }
    }

    private function createAssignments(): void
    {
        $this->command->info('Creating assignments...');

        // Assignments for upcoming Sunday
        if (isset($this->events['sunday_0'])) {
            $event = $this->events['sunday_0'];
            $ministry = $this->ministries['Прославлення'];

            $assignments = [
                ['position' => 'Вокаліст', 'person' => 'Мельник_Катерина', 'status' => 'confirmed'],
                ['position' => 'Вокаліст', 'person' => 'Савченко_Марія', 'status' => 'confirmed'],
                ['position' => 'Гітарист', 'person' => 'Бондаренко_Дмитро', 'status' => 'confirmed'],
                ['position' => 'Клавішник', 'person' => 'Марченко_Софія', 'status' => 'pending'],
                ['position' => 'Барабанщик', 'person' => 'Бойко_Максим', 'status' => 'confirmed'],
            ];

            foreach ($assignments as $data) {
                $positionKey = $ministry->name . '_' . $data['position'];
                if (isset($this->positions[$positionKey])) {
                    Assignment::create([
                        'event_id' => $event->id,
                        'position_id' => $this->positions[$positionKey]->id,
                        'person_id' => $this->people[$data['person']]->id,
                        'status' => $data['status'],
                        'notified_at' => $data['status'] === 'confirmed' ? now()->subDays(3) : null,
                        'responded_at' => $data['status'] === 'confirmed' ? now()->subDays(2) : null,
                    ]);
                }
            }
        }

        // Event responsibilities
        if (isset($this->events['sunday_0'])) {
            $event = $this->events['sunday_0'];

            $responsibilities = [
                ['name' => 'Ведучий', 'person' => 'Шевченко_Андрій', 'status' => 'confirmed'],
                ['name' => 'Читання Писання', 'person' => 'Коваленко_Людмила', 'status' => 'confirmed'],
                ['name' => 'Молитва за хворих', 'person' => 'Петренко_Наталія', 'status' => 'pending'],
            ];

            foreach ($responsibilities as $data) {
                EventResponsibility::create([
                    'event_id' => $event->id,
                    'name' => $data['name'],
                    'person_id' => $this->people[$data['person']]->id,
                    'status' => $data['status'],
                    'notified_at' => $data['status'] === 'confirmed' ? now()->subDays(3) : null,
                    'responded_at' => $data['status'] === 'confirmed' ? now()->subDays(2) : null,
                ]);
            }
        }
    }

    private function createTransactionCategories(): void
    {
        $this->command->info('Creating transaction categories...');

        $categories = [
            // Income
            ['name' => 'Десятина', 'type' => TransactionCategory::TYPE_INCOME, 'is_tithe' => true, 'sort_order' => 1, 'icon' => 'heart', 'color' => '#10b981'],
            ['name' => 'Пожертва', 'type' => TransactionCategory::TYPE_INCOME, 'is_offering' => true, 'sort_order' => 2, 'icon' => 'gift', 'color' => '#3b82f6'],
            ['name' => 'Цільовий збір', 'type' => TransactionCategory::TYPE_INCOME, 'sort_order' => 3, 'icon' => 'collection', 'color' => '#8b5cf6'],
            ['name' => 'Інші надходження', 'type' => TransactionCategory::TYPE_INCOME, 'sort_order' => 4, 'icon' => 'dots-horizontal', 'color' => '#6b7280'],
            // Expenses
            ['name' => 'Оренда приміщення', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 1, 'icon' => 'home', 'color' => '#ef4444'],
            ['name' => 'Комунальні послуги', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 2, 'icon' => 'lightning-bolt', 'color' => '#f59e0b'],
            ['name' => 'Зарплата', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 3, 'icon' => 'cash', 'color' => '#14b8a6'],
            ['name' => 'Обладнання', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 4, 'icon' => 'desktop-computer', 'color' => '#6366f1'],
            ['name' => 'Канцтовари', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 5, 'icon' => 'pencil', 'color' => '#ec4899'],
            ['name' => 'Благодійність', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 6, 'icon' => 'heart', 'color' => '#f43f5e'],
            ['name' => 'Служіння', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 7, 'icon' => 'users', 'color' => '#a855f7'],
            ['name' => 'Інші витрати', 'type' => TransactionCategory::TYPE_EXPENSE, 'sort_order' => 8, 'icon' => 'dots-horizontal', 'color' => '#6b7280'],
        ];

        foreach ($categories as $cat) {
            TransactionCategory::create(array_merge($cat, ['church_id' => $this->church->id]));
        }
    }

    private function createTransactions(): void
    {
        $this->command->info('Creating transactions...');

        $incomeCategories = TransactionCategory::where('church_id', $this->church->id)
            ->forIncome()
            ->get()
            ->keyBy('name');

        $expenseCategories = TransactionCategory::where('church_id', $this->church->id)
            ->forExpense()
            ->get()
            ->keyBy('name');

        // Generate 12 months of data
        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);
            $year = $date->year;
            $monthNum = $date->month;

            // Weekly tithes and offerings (4 Sundays)
            for ($week = 1; $week <= 4; $week++) {
                $sundayDate = Carbon::create($year, $monthNum, 1)->nthOfMonth($week, Carbon::SUNDAY);
                if ($sundayDate->month != $monthNum) continue;

                // Tithes from regular givers
                $givers = ['Петренко_Олександр', 'Коваленко_Михайло', 'Шевченко_Андрій', 'Ткаченко_Віктор', 'Олійник_Сергій'];
                foreach ($givers as $giverKey) {
                    if (rand(1, 10) <= 8) { // 80% chance
                        Transaction::create([
                            'church_id' => $this->church->id,
                            'direction' => 'in',
                            'source_type' => Transaction::SOURCE_TITHE,
                            'amount' => rand(500, 3000),
                            'currency' => 'UAH',
                            'date' => $sundayDate,
                            'category_id' => $incomeCategories['Десятина']->id,
                            'person_id' => $this->people[$giverKey]->id,
                            'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                            'status' => Transaction::STATUS_COMPLETED,
                            'recorded_by' => $this->admin->id,
                        ]);
                    }
                }

                // Anonymous offerings
                for ($i = 0; $i < rand(3, 8); $i++) {
                    Transaction::create([
                        'church_id' => $this->church->id,
                        'direction' => 'in',
                        'source_type' => Transaction::SOURCE_OFFERING,
                        'amount' => rand(50, 500),
                        'currency' => 'UAH',
                        'date' => $sundayDate,
                        'category_id' => $incomeCategories['Пожертва']->id,
                        'is_anonymous' => true,
                        'payment_method' => 'cash',
                        'status' => Transaction::STATUS_COMPLETED,
                        'recorded_by' => $this->admin->id,
                    ]);
                }
            }

            // Monthly expenses
            $monthStart = Carbon::create($year, $monthNum, 1);

            // Rent
            Transaction::create([
                'church_id' => $this->church->id,
                'direction' => 'out',
                'source_type' => Transaction::SOURCE_EXPENSE,
                'amount' => 15000,
                'currency' => 'UAH',
                'date' => $monthStart->copy()->addDays(4),
                'category_id' => $expenseCategories['Оренда приміщення']->id,
                'description' => 'Оренда приміщення за ' . $date->translatedFormat('F Y'),
                'payment_method' => 'transfer',
                'status' => Transaction::STATUS_COMPLETED,
                'recorded_by' => $this->admin->id,
            ]);

            // Utilities
            Transaction::create([
                'church_id' => $this->church->id,
                'direction' => 'out',
                'source_type' => Transaction::SOURCE_EXPENSE,
                'amount' => rand(2000, 4000),
                'currency' => 'UAH',
                'date' => $monthStart->copy()->addDays(rand(10, 15)),
                'category_id' => $expenseCategories['Комунальні послуги']->id,
                'description' => 'Комунальні платежі',
                'payment_method' => 'transfer',
                'status' => Transaction::STATUS_COMPLETED,
                'recorded_by' => $this->admin->id,
            ]);

            // Salary
            Transaction::create([
                'church_id' => $this->church->id,
                'direction' => 'out',
                'source_type' => Transaction::SOURCE_EXPENSE,
                'amount' => 25000,
                'currency' => 'UAH',
                'date' => $monthStart->copy()->endOfMonth(),
                'category_id' => $expenseCategories['Зарплата']->id,
                'description' => 'Зарплата пастора',
                'payment_method' => 'transfer',
                'status' => Transaction::STATUS_COMPLETED,
                'recorded_by' => $this->admin->id,
            ]);

            // Random ministry expenses
            if (rand(1, 3) === 1) {
                Transaction::create([
                    'church_id' => $this->church->id,
                    'direction' => 'out',
                    'source_type' => Transaction::SOURCE_EXPENSE,
                    'amount' => rand(500, 2000),
                    'currency' => 'UAH',
                    'date' => $monthStart->copy()->addDays(rand(1, 28)),
                    'category_id' => $expenseCategories['Служіння']->id,
                    'ministry_id' => array_values($this->ministries)[rand(0, count($this->ministries) - 1)]->id,
                    'description' => 'Витрати на служіння',
                    'payment_method' => 'cash',
                    'status' => Transaction::STATUS_COMPLETED,
                    'recorded_by' => $this->admin->id,
                ]);
            }

            // Charity help (occasionally)
            if (rand(1, 4) === 1) {
                Transaction::create([
                    'church_id' => $this->church->id,
                    'direction' => 'out',
                    'source_type' => Transaction::SOURCE_EXPENSE,
                    'amount' => rand(1000, 5000),
                    'currency' => 'UAH',
                    'date' => $monthStart->copy()->addDays(rand(1, 28)),
                    'category_id' => $expenseCategories['Благодійність']->id,
                    'description' => 'Допомога нужденним',
                    'payment_method' => 'cash',
                    'status' => Transaction::STATUS_COMPLETED,
                    'recorded_by' => $this->admin->id,
                ]);
            }
        }
    }

    private function createDonationCampaigns(): void
    {
        $this->command->info('Creating donation campaigns...');

        // Active campaign
        DonationCampaign::create([
            'church_id' => $this->church->id,
            'title' => 'Ремонт церковного приміщення',
            'description' => 'Збираємо кошти на капітальний ремонт нашого приміщення: заміна вікон, оновлення системи опалення та косметичний ремонт.',
            'goal_amount' => 150000,
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->addMonths(4),
            'status' => 'active',
        ]);

        // Completed campaign
        DonationCampaign::create([
            'church_id' => $this->church->id,
            'title' => 'Нове звукове обладнання',
            'description' => 'Збір на оновлення звукового обладнання для покращення якості богослужінь.',
            'goal_amount' => 50000,
            'start_date' => Carbon::now()->subMonths(6),
            'end_date' => Carbon::now()->subMonths(2),
            'status' => 'completed',
        ]);
    }

    private function createAttendanceRecords(): void
    {
        $this->command->info('Creating attendance records...');

        // Get all active members
        $activeMembers = Person::where('church_id', $this->church->id)
            ->whereIn('membership_status', ['active', 'member'])
            ->get();

        // Attendance for past Sunday services
        foreach (['sunday_past_4', 'sunday_past_3', 'sunday_past_2', 'sunday_past_1'] as $key) {
            if (!isset($this->events[$key])) continue;

            $event = $this->events[$key];

            $attendance = Attendance::create([
                'church_id' => $this->church->id,
                'attendable_type' => Event::class,
                'attendable_id' => $event->id,
                'type' => Attendance::TYPE_SERVICE,
                'date' => $event->date,
                'time' => $event->time,
                'location' => $event->location,
                'guests_count' => rand(2, 8),
                'recorded_by' => $this->admin->id,
            ]);

            // Mark members as present (70-90% attendance)
            $presentCount = 0;
            foreach ($activeMembers as $member) {
                $isPresent = rand(1, 100) <= 85;
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => $isPresent,
                    'checked_in_at' => $isPresent ? '09:' . str_pad(rand(30, 59), 2, '0', STR_PAD_LEFT) : null,
                ]);
                if ($isPresent) $presentCount++;
            }

            $attendance->update([
                'members_present' => $presentCount,
                'total_count' => $presentCount + $attendance->guests_count,
            ]);
        }

        // Attendance for groups (last 4 weeks)
        foreach ($this->groups as $group) {
            $groupMembers = $group->members()->get();

            for ($week = 4; $week >= 1; $week--) {
                $meetingDay = match($group->meeting_day) {
                    'tuesday' => Carbon::TUESDAY,
                    'thursday' => Carbon::THURSDAY,
                    'saturday' => Carbon::SATURDAY,
                    default => Carbon::TUESDAY,
                };

                $date = Carbon::now()->subWeeks($week)->startOfWeek()->addDays($meetingDay - 1);

                $attendance = Attendance::create([
                    'church_id' => $this->church->id,
                    'attendable_type' => Group::class,
                    'attendable_id' => $group->id,
                    'type' => Attendance::TYPE_GROUP,
                    'date' => $date,
                    'time' => $group->meeting_time,
                    'location' => $group->location,
                    'guests_count' => rand(0, 2),
                    'recorded_by' => $this->admin->id,
                ]);

                $presentCount = 0;
                foreach ($groupMembers as $member) {
                    $isPresent = rand(1, 100) <= 75;
                    AttendanceRecord::create([
                        'attendance_id' => $attendance->id,
                        'person_id' => $member->id,
                        'present' => $isPresent,
                    ]);
                    if ($isPresent) $presentCount++;
                }

                $attendance->update([
                    'members_present' => $presentCount,
                    'total_count' => $presentCount + $attendance->guests_count,
                ]);
            }
        }
    }

    private function createAnnouncements(): void
    {
        $this->command->info('Creating announcements...');

        $announcements = [
            [
                'title' => 'Ласкаво просимо до системи Ministrify!',
                'content' => "Шановні брати та сестри!\n\nВітаємо вас у нашій новій системі управління церквою Ministrify. Тут ви зможете:\n\n- Переглядати розклад богослужінь та подій\n- Реєструватися на заходи\n- Бачити своє служіння та розклад\n- Отримувати важливі оголошення\n\nЯкщо у вас є питання - звертайтесь до адміністрації церкви.",
                'is_pinned' => true,
                'days_ago' => 30,
            ],
            [
                'title' => 'Весняна конференція "Нове життя"',
                'content' => "Запрошуємо всіх на нашу щорічну весняну конференцію!\n\nДата: " . Carbon::now()->addMonths(2)->format('d.m.Y') . "\n\nУ програмі:\n- Потужні проповіді від запрошених спікерів\n- Прославлення\n- Семінари\n- Спілкування\n\nРеєстрація відкрита!",
                'is_pinned' => true,
                'days_ago' => 7,
            ],
            [
                'title' => 'Збір на ремонт приміщення',
                'content' => "Дорогі друзі!\n\nНагадуємо про наш збір коштів на капітальний ремонт церковного приміщення. Наразі зібрано близько 30% від необхідної суми.\n\nДякуємо всім, хто вже долучився! Разом ми зможемо оновити наше місце для богослужінь.",
                'is_pinned' => false,
                'days_ago' => 3,
            ],
            [
                'title' => 'Зміни в розкладі молодіжних зустрічей',
                'content' => "Увага молоді!\n\nНагадуємо, що наші молодіжні зустрічі тепер проходять щоп'ятниці о 19:00.\n\nТема цього місяця: \"Віра в дії\"\n\nЗапрошуйте друзів!",
                'is_pinned' => false,
                'days_ago' => 5,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create([
                'church_id' => $this->church->id,
                'author_id' => $this->admin->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'is_pinned' => $data['is_pinned'],
                'is_published' => true,
                'published_at' => now()->subDays($data['days_ago']),
            ]);
        }
    }

    private function createFamilyRelationships(): void
    {
        $this->command->info('Creating family relationships...');

        $families = [
            // Petrenki family
            ['person' => 'Петренко_Олександр', 'related' => 'Петренко_Наталія', 'type' => 'spouse'],

            // Kovalenki family
            ['person' => 'Коваленко_Михайло', 'related' => 'Коваленко_Людмила', 'type' => 'spouse'],

            // Shevchenko family with children
            ['person' => 'Шевченко_Андрій', 'related' => 'Шевченко_Оксана', 'type' => 'spouse'],
            ['person' => 'Шевченко_Андрій', 'related' => 'Шевченко_Даніїл', 'type' => 'child'],
            ['person' => 'Шевченко_Андрій', 'related' => 'Шевченко_Єва', 'type' => 'child'],
            ['person' => 'Шевченко_Оксана', 'related' => 'Шевченко_Даніїл', 'type' => 'child'],
            ['person' => 'Шевченко_Оксана', 'related' => 'Шевченко_Єва', 'type' => 'child'],
            ['person' => 'Шевченко_Даніїл', 'related' => 'Шевченко_Єва', 'type' => 'sibling'],

            // Tkachenko family
            ['person' => 'Ткаченко_Віктор', 'related' => 'Ткаченко_Ірина', 'type' => 'spouse'],

            // Rudenko family
            ['person' => 'Руденко_Петро', 'related' => 'Руденко_Галина', 'type' => 'spouse'],

            // Lytvynenko family
            ['person' => 'Литвиненко_Олег', 'related' => 'Литвиненко_Тетяна', 'type' => 'spouse'],
        ];

        foreach ($families as $rel) {
            if (isset($this->people[$rel['person']]) && isset($this->people[$rel['related']])) {
                FamilyRelationship::create([
                    'person_id' => $this->people[$rel['person']]->id,
                    'related_person_id' => $this->people[$rel['related']]->id,
                    'relationship_type' => $rel['type'],
                ]);
            }
        }
    }
}
