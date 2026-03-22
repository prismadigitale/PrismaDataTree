<?php

namespace Tests\Feature;

use App\Models\DataType;
use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTypeFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_type_can_have_multiple_fields_with_sort_order(): void
    {
        $dataType = DataType::factory()->create();
        $field1 = Field::factory()->create(['name' => 'field1']);
        $field2 = Field::factory()->create(['name' => 'field2']);

        $dataType->fields()->attach($field1->id, ['sort_order' => 2]);
        $dataType->fields()->attach($field2->id, ['sort_order' => 1]);

        $fields = $dataType->refresh()->fields;

        $this->assertCount(2, $fields);
        $this->assertEquals('field2', $fields[0]->name);
        $this->assertEquals('field1', $fields[1]->name);
    }
}
