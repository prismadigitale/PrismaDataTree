<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VaultService
{
    private const SESSION_KEY = 'vault_key';
    
    // Default PBKDF2 iterations
    private const KDF_ITERATIONS = 100000;

    /**
     * Check if the vault is currently unlocked for the session.
     */
    public function isUnlocked(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /**
     * Lock the vault (remove key from session).
     */
    public function lock(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Retrieve the current session encrypter if unlocked.
     */
    private function getEncrypter(): ?Encrypter
    {
        $key = Session::get(self::SESSION_KEY);
        if (!$key) {
            return null;
        }

        return new Encrypter(base64_decode($key), config('app.cipher'));
    }

    /**
     * Encrypt a value using the user's vault key.
     */
    public function encrypt($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (!$this->isUnlocked()) {
            throw new \Exception('Cannot encrypt: Vault is locked.');
        }

        return $this->getEncrypter()?->encrypt($value, false);
    }

    /**
     * Decrypt a value using the user's vault key.
     */
    public function decrypt($payload)
    {
        if (is_null($payload)) {
            return null;
        }

        if (!$this->isUnlocked()) {
            return '[CRITTOGRAFATO]';
        }

        try {
            return $this->getEncrypter()?->decrypt($payload, false);
        } catch (\Exception $e) {
            return '[ERRORE_DECRIPTAZIONE]';
        }
    }

    /**
     * Setup a new vault for the user with a master passphrase.
     */
    public function setupVault(User $user, string $passphrase): void
    {
        $cipher = config('app.cipher');
        
        // Ensure cipher is valid
        if (!in_array($cipher, ['AES-128-CBC', 'AES-256-CBC'])) {
            throw new InvalidArgumentException("Unsupported cipher: {$cipher}");
        }

        // 1. Generate salt
        $salt = Str::random(16);

        // 2. Derive Master Key using PBKDF2
        $keyLength = $cipher === 'AES-128-CBC' ? 16 : 32;
        $masterKey = hash_pbkdf2('sha256', $passphrase, $salt, self::KDF_ITERATIONS, $keyLength, true);

        // 3. Generate a random Vault Key
        $vaultKey = random_bytes($keyLength);

        // 4. Encrypt Vault Key with Master Key
        $masterEncrypter = new Encrypter($masterKey, $cipher);
        $encryptedVaultKey = $masterEncrypter->encrypt(base64_encode($vaultKey), false);

        // 5. Store in database
        $user->vault()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'kdf_salt' => $salt,
                'encrypted_vault_key' => $encryptedVaultKey,
                'kdf_iterations' => self::KDF_ITERATIONS,
            ]
        );

        // 6. Automatically unlock
        Session::put(self::SESSION_KEY, base64_encode($vaultKey));
    }

    /**
     * Unlock vault for the user using their master passphrase.
     */
    public function unlock(User $user, string $passphrase): bool
    {
        $vault = $user->vault;

        if (!$vault) {
            return false;
        }

        $cipher = config('app.cipher');
        $keyLength = $cipher === 'AES-128-CBC' ? 16 : 32;

        try {
            // Derive Master Key
            $masterKey = hash_pbkdf2('sha256', $passphrase, $vault->kdf_salt, $vault->kdf_iterations, $keyLength, true);

            // Attempt to decrypt Vault Key
            $masterEncrypter = new Encrypter($masterKey, $cipher);
            $vaultKeyBase64 = $masterEncrypter->decrypt($vault->encrypted_vault_key, false);

            $vaultKeyDetails = base64_decode($vaultKeyBase64, true);

            // Verify it's a valid key
            if ($vaultKeyDetails === false || strlen($vaultKeyDetails) !== $keyLength) {
                return false;
            }

            // Put Vault Key in session
            Session::put(self::SESSION_KEY, $vaultKeyBase64);

            return true;
        } catch (\Exception $e) {
            // Decryption failed (wrong passphrase)
            return false;
        }
    }

    /**
     * Change the vault master passphrase.
     */
    public function changePassphrase(User $user, string $currentPassphrase, string $newPassphrase): bool
    {
        $vault = $user->vault;

        if (!$vault) {
            return false;
        }

        $cipher = config('app.cipher');
        $keyLength = $cipher === 'AES-128-CBC' ? 16 : 32;

        try {
            // 1. Verify current passphrase & decrypt Vault Key
            $oldMasterKey = hash_pbkdf2('sha256', $currentPassphrase, $vault->kdf_salt, $vault->kdf_iterations, $keyLength, true);
            $oldMasterEncrypter = new Encrypter($oldMasterKey, $cipher);
            $vaultKeyBase64 = $oldMasterEncrypter->decrypt($vault->encrypted_vault_key, false);

            if (base64_decode($vaultKeyBase64, true) === false) {
                return false;
            }

            // 2. Generate new salt and derive new Master Key
            $newSalt = Str::random(16);
            $newMasterKey = hash_pbkdf2('sha256', $newPassphrase, $newSalt, self::KDF_ITERATIONS, $keyLength, true);

            // 3. Encrypt the same Vault Key with the new Master Key
            $newMasterEncrypter = new Encrypter($newMasterKey, $cipher);
            $newEncryptedVaultKey = $newMasterEncrypter->encrypt($vaultKeyBase64, false);

            // 4. Save new credentials to DB
            $vault->update([
                'kdf_salt' => $newSalt,
                'encrypted_vault_key' => $newEncryptedVaultKey,
                'kdf_iterations' => self::KDF_ITERATIONS,
            ]);

            // 5. Unlock the vault if it wasn't already
            Session::put(self::SESSION_KEY, $vaultKeyBase64);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
