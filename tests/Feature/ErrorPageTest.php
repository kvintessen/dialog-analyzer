<?php

namespace Tests\Feature;

use App\Models\Dialog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * With APP_DEBUG on (local/testing default), Laravel's own debug page is
 * more useful and is left alone. In production (debug off) these statuses
 * should render the SPA's own Error.vue instead of a bare Laravel page, so
 * the error stays inside the app's design system.
 */
class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbidden_response_renders_the_inertia_error_page_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $manager = User::factory()->manager()->create();
        $dialog = Dialog::factory()->create();

        $this->actingAs($manager)
            ->get(route('dialogs.show', $dialog))
            ->assertForbidden()
            ->assertInertia(fn ($page) => $page
                ->component('Error')
                ->where('status', 403)
            );
    }

    public function test_not_found_response_renders_the_inertia_error_page_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dialogs/999999')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page
                ->component('Error')
                ->where('status', 404)
            );
    }

    public function test_forbidden_response_uses_the_default_debug_page_when_debug_is_on(): void
    {
        config(['app.debug' => true]);

        $manager = User::factory()->manager()->create();
        $dialog = Dialog::factory()->create();

        $response = $this->actingAs($manager)->get(route('dialogs.show', $dialog));

        $response->assertForbidden();
        $response->assertHeaderMissing('X-Inertia');
    }
}
