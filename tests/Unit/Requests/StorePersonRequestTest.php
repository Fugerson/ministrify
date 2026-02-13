<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StorePersonRequest;
use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePersonRequestTest extends TestCase
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
        $request = new StorePersonRequest();
        $request->setUserResolver(fn () => $this->user);
        return $request->rules();
    }

    public function test_first_name_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make(['last_name' => 'Test'], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
    }

    public function test_last_name_is_required(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make(['first_name' => 'Test'], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    public function test_valid_data_passes(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
        ], $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_email_must_be_valid(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'first_name' => 'Test',
            'last_name' => 'Test',
            'email' => 'not-an-email',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_birth_date_must_be_in_past(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'first_name' => 'Test',
            'last_name' => 'Test',
            'birth_date' => now()->addYear()->toDateString(),
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('birth_date', $validator->errors()->toArray());
    }

    public function test_gender_must_be_valid(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'first_name' => 'Test',
            'last_name' => 'Test',
            'gender' => 'invalid_gender',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_membership_status_must_be_valid(): void
    {
        $rules = $this->getRules();
        $validator = Validator::make([
            'first_name' => 'Test',
            'last_name' => 'Test',
            'membership_status' => 'invalid_status',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('membership_status', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new StorePersonRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('first_name.required', $messages);
        $this->assertArrayHasKey('last_name.required', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
    }
}
