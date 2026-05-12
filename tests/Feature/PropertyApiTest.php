<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class PropertyApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $landlordUser;
    protected $tenantUser;
    protected $propertyCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a property category
        $this->propertyCategory = PropertyCategory::factory()->create(['name' => 'Apartment']);

        // Create a landlord user and their profile
        $this->landlordUser = User::factory()->create(['role' => 'landlord']);
        Landlord::factory()->create(['user_id' => $this->landlordUser->id]);

        // Create a tenant user and their profile
        $this->tenantUser = User::factory()->create(['role' => 'tenant']);
        Tenant::factory()->create(['user_id' => $this->tenantUser->id]);
    }
}
