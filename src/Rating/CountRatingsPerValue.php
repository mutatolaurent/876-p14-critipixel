<?php

namespace App\Rating;

use App\Model\Entity\VideoGame;

interface CountRatingsPerValue
{
    public function countRatingsPerValue(VideoGame $videoGame): void;
}
