<?php

namespace App\Services;

use App\DTO\FinancialMovementDTO;
use App\Models\FinancialMovement;
use Illuminate\Support\Arr;

class FinancialMovementService
{
    public function createFinancialMovement(FinancialMovementDTO $financialMovementDTO): FinancialMovement
    {
        return FinancialMovement::create($financialMovementDTO->toCreateInDB());
    }

    /**
     * @param  array<FinancialMovementDTO> $financialMovements
     * @return bool
     */
    public function insertFinancialMovements(array $financialMovements): bool
    {
        $financialMovements = Arr::map($financialMovements, fn(FinancialMovementDTO $financialMovementDTO) => $financialMovementDTO->toCreateInDB());
        return FinancialMovement::insert($financialMovements);
    }
}
