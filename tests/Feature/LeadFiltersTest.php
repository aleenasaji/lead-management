<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LeadFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_status_and_sort_are_applied_together_without_filter_values_in_the_url(): void
    {
        $user = User::factory()->create();
        Lead::factory()->for($user)->create([
            'name' => 'Alex Morgan',
            'email' => 'alex@example.test',
            'status' => LeadStatus::Contacted,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        Lead::factory()->for($user)->create([
            'name' => 'Alex Morgan',
            'email' => 'alex.second@example.test',
            'status' => LeadStatus::Contacted,
            'created_at' => '2026-01-02 10:00:00',
        ]);
        Lead::factory()->for($user)->create([
            'name' => 'Alex Morgan',
            'email' => 'alex.converted@example.test',
            'status' => LeadStatus::Converted,
        ]);
        Lead::factory()->for($user)->create([
            'name' => 'Taylor Reed',
            'email' => 'alex.match@example.test',
            'status' => LeadStatus::Contacted,
        ]);

        $this->actingAs($user);
        $response = $this->post('/leads/filters', [
            'search' => 'alex',
            'status' => LeadStatus::Contacted->value,
            'sort' => 'asc',
        ]);
        $filterUrl = $response->headers->get('Location');

        $this->assertIsString($filterUrl);
        $this->assertNull(parse_url($filterUrl, PHP_URL_QUERY));
        $filterKey = basename((string) parse_url($filterUrl, PHP_URL_PATH));

        $this->get($filterUrl)->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Index')
            ->where('filters.search', 'alex')
            ->where('filters.status', LeadStatus::Contacted->value)
            ->where('filters.sort', 'asc')
            ->where('leads.total', 3)
            ->where('leads.data.0.email', 'alex@example.test')
            ->where('leads.data.1.email', 'alex.second@example.test')
            ->where('leads.data.2.email', 'alex.match@example.test')
        );

        $this->assertSame(36, strlen($filterKey));
    }

    public function test_filter_state_survives_pagination_and_back_navigation(): void
    {
        $user = User::factory()->create();

        for ($index = 1; $index <= 12; $index++) {
            Lead::factory()->for($user)->create([
                'name' => sprintf('Target %02d', $index),
                'email' => sprintf('target%02d@example.test', $index),
                'status' => LeadStatus::Contacted,
                'created_at' => sprintf('2026-01-%02d 10:00:00', $index),
            ]);
        }

        Lead::factory()->for($user)->create([
            'name' => 'Unrelated lead',
            'status' => LeadStatus::New,
        ]);

        $this->actingAs($user);
        $firstFilterResponse = $this->post('/leads/filters', [
            'search' => 'Target',
            'status' => LeadStatus::Contacted->value,
            'sort' => 'asc',
        ]);
        $firstFilterUrl = $firstFilterResponse->headers->get('Location');
        $firstFilterKey = basename((string) parse_url((string) $firstFilterUrl, PHP_URL_PATH));

        $this->get("/leads/{$firstFilterKey}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'Target')
                ->where('leads.total', 12)
                ->where('leads.current_page', 1)
                ->where('leads.data.0.name', 'Target 01')
            );

        $pageTwoUrl = "/leads/{$firstFilterKey}?page=2";
        $this->assertSame('page=2', parse_url($pageTwoUrl, PHP_URL_QUERY));
        $this->get($pageTwoUrl)->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'Target')
            ->where('filters.status', LeadStatus::Contacted->value)
            ->where('leads.current_page', 2)
            ->where('leads.data.0.name', 'Target 11')
        );

        $secondFilterResponse = $this->post('/leads/filters', [
            'search' => '',
            'status' => LeadStatus::New->value,
            'sort' => 'desc',
        ]);
        $secondFilterUrl = $secondFilterResponse->headers->get('Location');
        $secondFilterKey = basename((string) parse_url((string) $secondFilterUrl, PHP_URL_PATH));

        $this->assertNotSame($firstFilterKey, $secondFilterKey);
        $this->get("/leads/{$firstFilterKey}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'Target')
                ->where('filters.status', LeadStatus::Contacted->value)
                ->where('leads.total', 12)
            );
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/leads/filters', [
                'search' => str_repeat('x', 101),
                'status' => 'Pending',
                'sort' => 'random',
            ])
            ->assertSessionHasErrors(['search', 'status', 'sort']);
    }
}
