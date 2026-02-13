<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreExpenseRequestTest extends TestCase
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
        $request = new StoreExpenseRequest();
        $request->setUserResolver(fn () => $this->user);
        return $request->rules();
    }

    public function test_amount_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'date' => now()->toDateString(),
            'description' => 'Test',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_amount_must_be_positive(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 0,
            'date' => now()->toDateString(),
            'description' => 'Test',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_date_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'description' => 'Test',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    public function test_date_cannot_be_in_future(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->addMonth()->toDateString(),
            'description' => 'Test',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    public function test_description_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->toDateString(),
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    public function test_valid_expense_passes(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 150.50,
            'date' => now()->toDateString(),
            'description' => 'Office supplies',
        ], $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_currency_must_be_valid(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->toDateString(),
            'description' => 'Test',
            'currency' => 'GBP',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('currency', $validator->errors()->toArray());
    }

    public function test_payment_method_must_be_valid(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'amount' => 100,
            'date' => now()->toDateString(),
            'description' => 'Test',
            'payment_method' => 'bitcoin',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('payment_method', $validator->errors()->toArray());
    }
}
