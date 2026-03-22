<?php

namespace Tests\Feature;

use App\Filament\Resources\DataTypes\Pages\CreateDataType;
use App\Filament\Resources\DataTypes\Pages\ListDataTypes;
use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Models\DataType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_data_types(): void
    {
        $dataTypes = DataType::factory()->count(3)->create();

        Livewire::test(ListDataTypes::class)
            ->assertCanSeeTableRecords($dataTypes);
    }

    public function test_can_create_data_type(): void
    {
        Livewire::test(CreateDataType::class)
            ->fillForm([
                'name' => 'Test Type',
                'slug' => 'test-type',
                'icon' => 'heroicon-o-cpu-chip',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('data_types', ['slug' => 'test-type']);
    }

    public function test_can_list_settings(): void
    {
        $settings = Setting::factory()->count(3)->create();

        Livewire::test(ManageSettings::class)
            ->assertCanSeeTableRecords($settings);
    }
}
