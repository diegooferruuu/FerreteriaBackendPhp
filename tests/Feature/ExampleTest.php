<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Services\Siat\CodeObtaining;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_cuis_can_be_obtained()
    {
        $service = new CodeObtaining();
        $response = $service->requestCuis(['branch_code' => 0, 'pos_code' => 0]);
        $this->assertTrue(true);
        $this->assertEquals(true, $response->transaccion);
    }
}
