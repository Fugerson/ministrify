<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreIncomeRequest;
use App\Models\Church;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreIncomeRequestTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $this->user = User::factory()->create(['church_id' => $this->church->id]);
        $this->actingAs($this->user);
    }

    private function getRules(): array
    {
        $request = new StoreIncomeRequest();
        $request->setUserResolver(fn () => $this->user);
        return $request->rules();
    }

    public function test_category_id_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_amount_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_payment_method_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->toDateString(),
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('payment_method', $validator->errors()->toArray());
    }

    public function test_payment_method_must_be_valid(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->toDateString(),
            'payment_method' => 'bitcoin',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('payment_method', $validator->errors()->toArray());
    }

    public function test_date_cannot_be_in_future(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->addMonth()->toDateString(),
            'payment_method' => 'cash',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    public function test_valid_income_passes(): void
    {
        $category = TransactionCategory::factory()->forChurch($this->church)->create([
            'type' => 'income',
        ]);

        $rules = $this->getRules();
        $validator = Validator::make([
            'category_id' => $category->id,
            'amount' => 500,
            'date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreIncomeRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('category_id.required', $messages);
        $this->assertArrayHasKey('amount.required', $messages);
        $this->assertArrayHasKey('payment_method.required', $messages);
    }
}
