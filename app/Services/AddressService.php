<?php

namespace App\Services;

use App\Exceptions\AddressException;
use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function listForEntity(string $type, int $id): Collection
    {
        return Address::where('addressable_type', $type)
            ->where('addressable_id', $id)
            ->orderByDesc('is_primary')
            ->orderBy('type')
            ->orderBy('created_at')
            ->get();
    }

    public function createAddress(array $data): Address
    {
        try {
            return DB::transaction(function () use ($data) {
                if ($data['is_primary'] ?? false) {
                    Address::where('addressable_type', $data['addressable_type'])
                        ->where('addressable_id', $data['addressable_id'])
                        ->where('type', $data['type'])
                        ->update(['is_primary' => false]);
                }

                return Address::create($data);
            });
        } catch (\Exception $e) {
            throw AddressException::creationFailed($e->getMessage());
        }
    }

    public function updateAddress(Address $address, array $data): Address
    {
        try {
            return DB::transaction(function () use ($address, $data) {
                if (($data['is_primary'] ?? false) && ! $address->is_primary) {
                    Address::where('addressable_type', $address->addressable_type)
                        ->where('addressable_id', $address->addressable_id)
                        ->where('type', $data['type'] ?? $address->type)
                        ->where('id', '!=', $address->id)
                        ->update(['is_primary' => false]);
                }

                $address->update($data);

                return $address->fresh();
            });
        } catch (\Exception $e) {
            throw AddressException::updateFailed($e->getMessage());
        }
    }

    public function deleteAddress(Address $address): void
    {
        try {
            $address->delete();
        } catch (\Exception $e) {
            throw AddressException::deletionFailed($e->getMessage());
        }
    }

    public function setPrimary(Address $address): Address
    {
        try {
            return DB::transaction(function () use ($address) {
                Address::where('addressable_type', $address->addressable_type)
                    ->where('addressable_id', $address->addressable_id)
                    ->where('type', $address->type)
                    ->where('id', '!=', $address->id)
                    ->update(['is_primary' => false]);

                $address->update(['is_primary' => true]);

                return $address->fresh();
            });
        } catch (\Exception $e) {
            throw AddressException::updateFailed($e->getMessage());
        }
    }
}
