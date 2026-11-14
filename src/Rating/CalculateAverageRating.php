<?php

namespace App\Rating;

use App\Model\Entity\VideoGame;

interface CalculateAverageRating
{
    public function calculateAverage(VideoGame $videoGame): void;
}
