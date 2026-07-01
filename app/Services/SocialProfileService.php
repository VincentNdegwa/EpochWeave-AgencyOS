<?php

namespace App\Services;

use App\Exceptions\SocialProfileException;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Collection;

class SocialProfileService
{
    public function listForEntity(string $type, int $id): Collection
    {
        return SocialProfile::where('profileable_type', $type)
            ->where('profileable_id', $id)
            ->orderBy('platform')
            ->get();
    }

    public function createSocialProfile(array $data): SocialProfile
    {
        try {
            return SocialProfile::create($data);
        } catch (\Exception $e) {
            throw SocialProfileException::creationFailed($e->getMessage());
        }
    }

    public function updateSocialProfile(SocialProfile $socialProfile, array $data): SocialProfile
    {
        try {
            $socialProfile->update($data);

            return $socialProfile->fresh();
        } catch (\Exception $e) {
            throw SocialProfileException::updateFailed($e->getMessage());
        }
    }

    public function deleteSocialProfile(SocialProfile $socialProfile): void
    {
        try {
            $socialProfile->delete();
        } catch (\Exception $e) {
            throw SocialProfileException::deletionFailed($e->getMessage());
        }
    }
}
