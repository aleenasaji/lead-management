<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_pages_require_authentication(): void
    {
        $this->get('/leads')->assertRedirect(route('login'));
        $this->get('/leads/create')->assertRedirect(route('login'));
    }

    public function test_an_authenticated_user_can_create_a_lead(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/leads', [
                'name' => 'Jordan Lee',
                'email' => 'jordan@example.test',
                'phone' => '555-0102',
                'status' => LeadStatus::New->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'user_id' => $user->id,
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.test',
            'status' => LeadStatus::New->value,
        ]);
    }

    public function test_lead_email_is_trimmed_and_normalized(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/leads', [
            'name' => 'Jordan Lee',
            'email' => '  Jordan@Example.Test  ',
            'phone' => '555-0102',
            'status' => LeadStatus::New->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'user_id' => $user->id,
            'email' => 'jordan@example.test',
        ]);
    }

    public function test_lead_fields_and_status_are_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/leads', [
                'name' => '',
                'email' => 'not-an-email',
                'phone' => '',
                'status' => 'Pending',
            ])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'status']);
    }

    public function test_phone_number_must_have_a_valid_international_format_when_creating_and_updating(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->for($user)->create();

        $this->actingAs($user)
            ->post('/leads', [
                'name' => 'Invalid phone lead',
                'email' => 'invalid-phone@example.test',
                'phone' => '+917055555555ere',
                'status' => LeadStatus::New->value,
            ])
            ->assertSessionHasErrors('phone');

        $this->put("/leads/{$lead->id}", [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => '+917055555555ere',
            'status' => $lead->status->value,
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'phone' => $lead->phone,
        ]);
    }

    public function test_phone_number_accepts_international_formatting(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/leads', [
                'name' => 'International phone lead',
                'email' => 'international-phone@example.test',
                'phone' => '+91 (705) 555-5555',
                'status' => LeadStatus::New->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'user_id' => $user->id,
            'phone' => '+91 (705) 555-5555',
        ]);
    }

    public function test_email_must_be_unique_when_creating_and_updating_a_lead(): void
    {
        $user = User::factory()->create();
        $existing = Lead::factory()->for($user)->create(['email' => 'used@example.test']);
        $lead = Lead::factory()->for($user)->create(['email' => 'original@example.test']);

        $this->actingAs($user)
            ->post('/leads', [
                'name' => 'Another lead',
                'email' => 'used@example.test',
                'phone' => '555-0103',
                'status' => LeadStatus::Contacted->value,
            ])
            ->assertSessionHasErrors('email');

        $this->put("/leads/{$lead->id}", [
            'name' => $lead->name,
            'email' => $existing->email,
            'phone' => $lead->phone,
            'status' => $lead->status->value,
        ])->assertSessionHasErrors('email');

        $this->put("/leads/{$lead->id}", [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'status' => LeadStatus::Converted->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => LeadStatus::Converted->value,
        ]);
    }

    public function test_the_database_enforces_lead_email_uniqueness(): void
    {
        $user = User::factory()->create();
        Lead::factory()->for($user)->create(['email' => 'unique@example.test']);

        $this->expectException(QueryException::class);

        DB::table('leads')->insert([
            'user_id' => $user->id,
            'name' => 'Duplicate lead',
            'email' => 'unique@example.test',
            'phone' => '555-0104',
            'status' => LeadStatus::New->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_users_can_only_edit_and_delete_their_own_leads(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $lead = Lead::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->get("/leads/{$lead->id}/edit")
            ->assertForbidden();

        $this->put("/leads/{$lead->id}", [
            'name' => 'Unauthorized',
            'email' => 'unauthorized@example.test',
            'phone' => '555-0199',
            'status' => LeadStatus::New->value,
        ])->assertForbidden();

        $this->delete("/leads/{$lead->id}")->assertForbidden();
        $this->patch("/leads/{$lead->id}/status", ['status' => LeadStatus::Converted->value])
            ->assertForbidden();

        $this->assertDatabaseHas('leads', ['id' => $lead->id]);
    }

    public function test_a_user_can_delete_their_own_lead(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete("/leads/{$lead->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_a_user_can_update_their_lead_status_inline(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->for($user)->create(['status' => LeadStatus::New]);

        $this->actingAs($user)
            ->patch("/leads/{$lead->id}/status", ['status' => LeadStatus::Contacted->value])
            ->assertRedirect()
            ->assertSessionHas('success', 'Lead status updated successfully.');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => LeadStatus::Contacted->value,
        ]);
    }

    public function test_inline_status_update_rejects_invalid_values(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->for($user)->create(['status' => LeadStatus::New]);

        $this->actingAs($user)
            ->patch("/leads/{$lead->id}/status", ['status' => 'Pending'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => LeadStatus::New->value,
        ]);
    }

    public function test_edit_form_receives_selectable_status_options(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->for($user)->create();

        $this->actingAs($user)
            ->get("/leads/{$lead->id}/edit")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Leads/Form')
                ->where('statuses.0.value', LeadStatus::New->value)
                ->where('statuses.1.value', LeadStatus::Contacted->value)
                ->where('statuses.2.value', LeadStatus::Converted->value)
            );
    }

    public function test_registration_and_login_are_available(): void
    {
        $this->get('/register')->assertOk();

        $this->post('/register', [
            'name' => 'Taylor Smith',
            'email' => 'taylor@example.test',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'taylor@example.test',
            'password' => 'correct-horse-battery',
        ])->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_lead_listing_is_scoped_to_the_signed_in_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownLead = Lead::factory()->for($user)->create(['name' => 'Private lead']);
        Lead::factory()->for($otherUser)->create(['name' => 'Other account lead']);

        $response = $this->actingAs($user)->get('/leads');
        $filterUrl = $response->headers->get('Location');

        $this->assertIsString($filterUrl);
        $this->assertNull(parse_url($filterUrl, PHP_URL_QUERY));

        $response = $this->get($filterUrl);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Index')
            ->where('statuses.0.value', LeadStatus::New->value)
            ->where('statuses.0.label', LeadStatus::New->value)
            ->where('leads.total', 1)
            ->where('leads.data.0.id', $ownLead->id)
            ->where('leads.data.0.name', 'Private lead')
        );
    }

    public function test_status_and_created_at_filtering_has_a_composite_index(): void
    {
        $plan = DB::select(
            'EXPLAIN QUERY PLAN SELECT id FROM leads WHERE user_id = ? AND status = ? ORDER BY created_at DESC',
            [1, LeadStatus::Contacted->value],
        );
        $details = implode(' ', array_column($plan, 'detail'));

        $this->assertStringContainsString('leads_user_id_status_created_at_index', $details);
    }
}
