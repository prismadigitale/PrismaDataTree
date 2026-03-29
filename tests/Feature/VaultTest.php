<?php

namespace Tests\Feature;

use App\Models\DataType;
use App\Models\Field;
use App\Models\Node;
use App\Models\User;
use App\Services\VaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class VaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_vault_setup_and_unlock()
    {
        $user = User::factory()->create();
        $service = app(VaultService::class);
        $passphrase = 'super_secret_master_passphrase';

        // 1. Setup
        $service->setupVault($user, $passphrase);
        
        $this->assertDatabaseHas('user_vaults', [
            'user_id' => $user->id,
        ]);
        $this->assertTrue($service->isUnlocked());

        // 2. Lock
        $service->lock();
        $this->assertFalse($service->isUnlocked());

        // 3. Unlock with wrong passphrase
        $result = $service->unlock($user, 'wrong_passphrase');
        $this->assertFalse($result);
        $this->assertFalse($service->isUnlocked());

        // 4. Unlock with correct passphrase
        $result = $service->unlock($user, $passphrase);
        $this->assertTrue($result);
        $this->assertTrue($service->isUnlocked());
    }

    public function test_vault_encryption_decryption()
    {
        $user = User::factory()->create();
        $service = app(VaultService::class);
        
        $service->setupVault($user, 'secret123');

        $plaintext = 'Confidential Information';
        $encrypted = $service->encrypt($plaintext);

        $this->assertNotEquals($plaintext, $encrypted);
        
        $decrypted = $service->decrypt($encrypted);
        $this->assertEquals($plaintext, $decrypted);

        // Lock vault and try to decrypt
        $service->lock();
        $this->assertEquals('[CRITTOGRAFATO]', $service->decrypt($encrypted));
    }

    public function test_vault_change_passphrase()
    {
        $user = User::factory()->create();
        $service = app(VaultService::class);
        
        $service->setupVault($user, 'oldPassphrase');
        $oldEncryptedKey = $user->vault->encrypted_vault_key;

        // Try changing with wrong current passphrase
        $result = $service->changePassphrase($user, 'wrongPassphrase', 'newPassphrase');
        $this->assertFalse($result);

        // Change successfully
        $result = $service->changePassphrase($user, 'oldPassphrase', 'newPassphrase');
        $this->assertTrue($result);

        // Vault should be unlocked and encrypted key should have changed
        $this->assertTrue($service->isUnlocked());
        $user->vault->refresh();
        $this->assertNotEquals($oldEncryptedKey, $user->vault->encrypted_vault_key);

        // The old pass should no longer unlock it
        $service->lock();
        $this->assertFalse($service->unlock($user, 'oldPassphrase'));
        
        // The new pass should unlock it
        $this->assertTrue($service->unlock($user, 'newPassphrase'));
    }

    public function test_node_vault_data_cast()
    {
        $user = User::factory()->create();
        $service = app(VaultService::class);
        $service->setupVault($user, 'secret123');

        $dataType = DataType::create(['name' => 'Secret Document', 'slug' => 'secret-document']);

        // Create normal and protected fields
        $fieldNormal = Field::create([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
        ]);
        $fieldSecret = Field::create([
            'name' => 'secret_content',
            'label' => 'Secret Content',
            'type' => 'textarea',
            'is_vault_protected' => true,
        ]);

        $dataType->fields()->attach($fieldNormal->id, ['sort_order' => 1]);
        $dataType->fields()->attach($fieldSecret->id, ['sort_order' => 2]);

        // Create a Node while vault is unlocked
        $node = Node::create([
            'data_type_id' => $dataType->id,
            'title' => 'My Secret Node',
            'data' => [
                'title' => 'Public Title',
                'secret_content' => 'This is a vault protected string',
            ],
        ]);

        // Access raw DB to see what was actually saved
        $rawNode = \Illuminate\Support\Facades\DB::table('nodes')->where('id', $node->id)->first();
        $rawData = json_decode($rawNode->data, true);

        $this->assertEquals('Public Title', $rawData['title']);
        $this->assertNotEquals('This is a vault protected string', $rawData['secret_content']);

        // Fetch using Eloquent (unlocked)
        $fetchedNode = Node::with('dataType.fields')->find($node->id);
        $this->assertEquals('Public Title', $fetchedNode->data['title']);
        $this->assertEquals('This is a vault protected string', $fetchedNode->data['secret_content']);

        // Lock vault and fetch
        $service->lock();
        $lockedNode = Node::with('dataType.fields')->find($node->id);
        $this->assertEquals('Public Title', $lockedNode->data['title']);
        $this->assertEquals('[CRITTOGRAFATO]', $lockedNode->data['secret_content']);

        // Try to update locked node (should preserve existing encrypted data)
        // Wait, if it's locked, Filament won't send back [CRITTOGRAFATO], but let's test simulating that
        $lockedNode->update([
            'data' => [
                'title' => 'Updated Public Title',
                'secret_content' => '[CRITTOGRAFATO]', // Simulate Filament disabled field
            ]
        ]);

        // Verify data wasn't corrupted
        $rawNode2 = \Illuminate\Support\Facades\DB::table('nodes')->where('id', $node->id)->first();
        $rawData2 = json_decode($rawNode2->data, true);
        
        $this->assertEquals('Updated Public Title', $rawData2['title']);
        $this->assertEquals($rawData['secret_content'], $rawData2['secret_content'], 'Encrypted payload should not be overwritten.');
    }
}
