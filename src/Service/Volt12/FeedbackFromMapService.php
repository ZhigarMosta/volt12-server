<?php

namespace App\Service\Volt12;

use App\Provider\ProductCodeProvider;
use App\Repository\FeedbackFromMapRepository;

class FeedbackFromMapService
{
    public function __construct(
        private FeedbackFromMapRepository $feedbackFromMapRepository
    )
    {
    }

    public function getWithLimit(): array
    {
        return $this->feedbackFromMapRepository->findWithLimit([ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY]);
    }

}
