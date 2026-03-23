<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class FinancesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $church = Church::first();
        if (! $church) {
            $this->command->info('No church found. Please run main seeder first.');

            return;
        }

        $admin = User::where('church_id', $church->id)->where('role', 'admin')->first();
        if (! $admin) {
            $this->command->info('No admin user found.');

            return;
        }

        // Create income categories
        $incomeCategories = $this->createIncomeCategories($church);

        // Get people for donations
        $people = Person::where('church_id', $church->id)->get();

        // Get ministries for expenses
        $ministries = Ministry::where('church_id', $church->id)->get();

        // Get expense categories
        $expenseCategories = ExpenseCategory::where('church_id', $church->id)->get();
        if ($expenseCategories->isEmpty()) {
            $expenseCategories = $this->createExpenseCategories($church);
        }

        // Generate data for the past 12 months
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now();

        $this->command->info('Generating financial data from '.$startDate->format('Y-m').' to '.$endDate->format('Y-m'));

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $this->generateMonthlyData(
                $church,
                $admin,
                $currentDate,
                $incomeCategories,
                $expenseCategories,
                $ministries,
                $people
            );
            $currentDate->addMonth();
        }

        $this->command->info('Demo financial data created successfully!');
    }

    private function createIncomeCategories(Church $church): array
    {
        $categories = [
            ['name' => 'Десятина', 'icon' => '💰', 'color' => '#10B981', 'is_tithe' => true],
            ['name' => 'Пожертва', 'icon' => '🙏', 'color' => '#6366F1', 'is_offering' => true],
            ['name' => 'Будівельний фонд', 'icon' => '🏗️', 'color' => '#F59E0B', 'is_donation' => true],
            ['name' => 'Місійний фонд', 'icon' => '✈️', 'color' => '#3B82F6', 'is_donation' => true],
            ['name' => 'Допомога нужденним', 'icon' => '❤️', 'color' => '#EF4444', 'is_donation' => true],
        ];

        $result = [];
        foreach ($categories as $index => $data) {
            $result[] = IncomeCategory::firstOrCreate(
                ['church_id' => $church->id, 'name' => $data['name']],
                array_merge($data, ['church_id' => $church->id, 'sort_order' => $index])
            );
        }

        return $result;
    }

    private function createExpenseCategories(Church $church): Collection
    {
        $categories = [
            'Оренда приміщення',
            'Комунальні послуги',
            'Обладнання',
            'Канцтовари',
            'Транспорт',
            'Їжа та напої',
            'Благодійність',
            'Інше',
        ];

        $result = collect();
        foreach ($categories as $name) {
            $result->push(ExpenseCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => $name,
            ]));
        }

        return $result;
    }

    private function generateMonthlyData(
        Church $church,
        User $admin,
        Carbon $month,
        array $incomeCategories,
        $expenseCategories,
        $ministries,
        $people
    ): void {
        $year = $month->year;
        $monthNum = $month->month;

        // Base amounts with seasonal variation
        $seasonalMultiplier = $this->getSeasonalMultiplier($monthNum);

        // Generate 15-30 income entries per month
        $incomeCount = rand(15, 30);
        for ($i = 0; $i < $incomeCount; $i++) {
            $category = $incomeCategories[array_rand($incomeCategories)];
            $isAnonymous = rand(1, 10) <= 3; // 30% anonymous

            $baseAmount = $category->is_tithe ? rand(500, 5000) : rand(100, 2000);
            $amount = $baseAmount * $seasonalMultiplier;

            Income::create([
                'church_id' => $church->id,
                'category_id' => $category->id,
                'user_id' => $admin->id,
                'person_id' => $isAnonymous ? null : ($people->isNotEmpty() ? $people->random()->id : null),
                'amount' => round($amount, 2),
                'date' => $month->copy()->addDays(rand(0, $month->daysInMonth - 1)),
                'payment_method' => $this->randomPaymentMethod(),
                'is_anonymous' => $isAnonymous,
                'description' => $this->randomIncomeDescription($category),
            ]);
        }

        // Generate 8-20 expense entries per month
        if ($ministries->isNotEmpty() && $expenseCategories->isNotEmpty()) {
            $expenseCount = rand(8, 20);
            for ($i = 0; $i < $expenseCount; $i++) {
                $ministry = $ministries->random();
                $category = $expenseCategories->random();

                Expense::create([
                    'church_id' => $church->id,
                    'ministry_id' => $ministry->id,
                    'category_id' => $category->id,
                    'user_id' => $admin->id,
                    'amount' => round(rand(50, 3000) * $seasonalMultiplier, 2),
                    'date' => $month->copy()->addDays(rand(0, $month->daysInMonth - 1)),
                    'description' => $this->randomExpenseDescription($category->name),
                ]);
            }
        }
    }

    private function getSeasonalMultiplier(int $month): float
    {
        // Higher donations in December (Christmas) and April (Easter)
        return match ($month) {
            12 => 1.5,  // Christmas
            4 => 1.3,   // Easter period
            1 => 0.8,   // Post-holiday
            7, 8 => 0.9, // Summer
            default => 1.0,
        };
    }

    private function randomPaymentMethod(): string
    {
        $methods = ['cash', 'card', 'transfer', 'online'];
        $weights = [40, 25, 20, 15]; // Cash most common

        $rand = rand(1, 100);
        $cumulative = 0;
        foreach ($weights as $i => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $methods[$i];
            }
        }

        return 'cash';
    }

    private function randomIncomeDescription($category): ?string
    {
        if (rand(1, 5) > 2) {
            return null;
        } // 60% no description

        $descriptions = [
            'Недільне служіння',
            'Особлива подяка',
            'На потреби церкви',
            'Молитовне служіння',
            'Щотижнева пожертва',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function randomExpenseDescription(string $category): string
    {
        $descriptions = [
            'Оренда приміщення' => ['Оренда за місяць', 'Депозит за зал', 'Оренда конференц-залу'],
            'Комунальні послуги' => ['Електроенергія', 'Водопостачання', 'Опалення', 'Інтернет'],
            'Обладнання' => ['Мікрофон', 'Кабелі', 'Лампи', 'Проектор', 'Колонки'],
            'Канцтовари' => ['Папір', 'Ручки', 'Зошити', 'Маркери', 'Фліпчарт'],
            'Транспорт' => ['Бензин', 'Оренда автобуса', 'Таксі', 'Паркування'],
            'Їжа та напої' => ['Кава', 'Чай', 'Печиво', 'Вода', 'Обід для волонтерів'],
            'Благодійність' => ['Допомога родині', 'Продуктовий набір', 'Ліки'],
            'Інше' => ['Різні витрати', 'Непередбачені витрати', 'Дрібні покупки'],
        ];

        $options = $descriptions[$category] ?? $descriptions['Інше'];

        return $options[array_rand($options)];
    }
}
