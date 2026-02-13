<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdatePersonRequest;
use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePersonRequestTest extends TestCase
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

    public function test_first_name_is_required(): void
    {
        $request = new UpdatePersonRequest();
        $request->setUserResolver(fn () => $this->user);
        $rules = $request->rules();

        $validator = Validator::make(['last_name' => 'Test'], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
    }

    public function test_valid_data_passes(): void
    {
        $request = new UpdatePersonRequest();
        $request->setUserResolver(fn () => $this->user);
        $rules = $request->rules();

        $validator = Validator::make([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
        ], $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_authorize_returns_true(): void
    {
        $request = new UpdatePersonRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_custom_messages_exist(): void
    {
        $request = new UpdatePersonRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('first_name.required', $messages);
        $this->assertArrayHasKey('last_name.required', $messages);
        $this->assertArrayHasKey('birth_date.before', $messages);
    }
}
