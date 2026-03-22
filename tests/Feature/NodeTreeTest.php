<?php

namespace Tests\Feature;

use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NodeTreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_nodes_have_automatic_ordering(): void
    {
        $node1 = Node::factory()->create(['parent_id' => null, 'order' => null]);
        $node2 = Node::factory()->create(['parent_id' => null, 'order' => null]);

        $this->assertEquals(1, $node1->order);
        $this->assertEquals(2, $node2->order);
    }

    public function test_child_nodes_are_linked_to_parent(): void
    {
        $parent = Node::factory()->create();
        $child = Node::factory()->create(['parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertTrue($parent->children->contains($child));
    }

    public function test_cascading_deletion(): void
    {
        $parent = Node::factory()->create();
        $child = Node::factory()->create(['parent_id' => $parent->id]);
        $grandchild = Node::factory()->create(['parent_id' => $child->id]);

        $parent->delete();

        $this->assertDatabaseMissing('nodes', ['id' => $parent->id]);
        $this->assertDatabaseMissing('nodes', ['id' => $child->id]);
        $this->assertDatabaseMissing('nodes', ['id' => $grandchild->id]);
    }

    public function test_to_tree_method_generates_nested_array(): void
    {
        $parent = Node::factory()->create(['title' => 'Parent']);
        Node::factory()->create(['parent_id' => $parent->id, 'title' => 'Child']);

        $tree = $parent->toTree();

        $this->assertIsArray($tree);
        $this->assertCount(1, $tree);
        $this->assertEquals('Parent', $tree[0]['title']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertEquals('Child', $tree[0]['children'][0]['title']);
    }
}
