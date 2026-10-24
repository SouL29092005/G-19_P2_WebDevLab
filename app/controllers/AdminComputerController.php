<?php

declare(strict_types=1);

final class AdminComputerController
{
    public function __construct(
        private readonly ComputerRepository $computerRepository
    ) {
    }

    public function index(): array
    {
        return [
            'pageTitle' => 'Manage Computers',
            'computers' => $this->computerRepository
                ->getAllComputers(),
        ];
    }
}
