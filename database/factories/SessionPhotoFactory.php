<?php

namespace Database\Factories;

use App\Models\FishingSession;
use App\Models\SessionPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionPhoto>
 */
class SessionPhotoFactory extends Factory
{
    protected $model = SessionPhoto::class;

    public function definition(): array
    {
        return [
            'fishing_session_id' => FishingSession::factory(),
            'image_path' => 'session-photos/example.jpg',
        ];
    }
}
