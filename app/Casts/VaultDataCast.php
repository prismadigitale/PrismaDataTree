<?php

namespace App\Casts;

use App\Services\VaultService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class VaultDataCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $data = json_decode($value ?? '{}', true) ?? [];
        
        if (empty($data)) {
            return $data;
        }

        // To avoid N+1 and unnecessary queries, we need the model's dataType
        // and its fields.
        // It's recommended to eager load 'dataType.fields' elsewhere.
        if (!$model->relationLoaded('dataType') && !isset($attributes['data_type_id'])) {
            return $data;
        }

        try {
            $vaultService = app(VaultService::class);
            $fields = $model->dataType?->fields;
            if (!$fields) {
                return $data;
            }

            $protectedFields = $fields->where('is_vault_protected', true)->pluck('name')->toArray();

            foreach ($protectedFields as $fieldName) {
                if (isset($data[$fieldName])) {
                    $data[$fieldName] = $vaultService->decrypt($data[$fieldName]);
                }
            }
        } catch (\Exception $e) {
            // Ignore for now, return data as is, or with [ERRORE_DECRIPTAZIONE] handled by the service
        }

        return $data;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $data = is_string($value) ? json_decode($value, true) : $value;
        $data = $data ?? [];

        if (empty($data)) {
            return json_encode($data);
        }

        try {
            $vaultService = app(VaultService::class);
            
            // Only encrypt if unlocked
            if (!$vaultService->isUnlocked()) {
                // If vault is locked, we shouldn't allow updating vault-protected fields!
                // Actually, if it's locked, the Filament form shows '[CRITTOGRAFATO]' which we don't want to save back.
                // Filament usually removes these fields or disables them, but just in case:
                // We should block saving if any protected field is present and being modified.
                // We will assume UI prevents this and just re-save what was there.
            }

            $fields = $model->dataType?->fields;
            if ($fields) {
                $protectedFields = $fields->where('is_vault_protected', true)->pluck('name')->toArray();

                foreach ($protectedFields as $fieldName) {
                    if (isset($data[$fieldName])) {
                        // Avoid re-encrypting the placeholder or already encrypted string if locked
                        if ($data[$fieldName] === '[CRITTOGRAFATO]' || $data[$fieldName] === '[ERRORE_DECRIPTAZIONE]') {
                            // Recover original encrypted value from raw attributes
                            $originalData = json_decode($attributes[$key] ?? '{}', true) ?? [];
                            $data[$fieldName] = $originalData[$fieldName] ?? null;
                            continue;
                        }

                        // If it's a new value, encrypt it
                        if ($vaultService->isUnlocked()) {
                            $data[$fieldName] = $vaultService->encrypt($data[$fieldName]);
                        } else {
                            // Cannot encrypt new data if locked. Ideally throw an exception.
                             throw new \Exception('Cannot save protected field while vault is locked.');
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return json_encode($data);
    }
}
