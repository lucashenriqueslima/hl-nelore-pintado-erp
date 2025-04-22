<?php

namespace App\DTO;

use App\Enums\FinancialMovementStatus;
use App\Enums\FinancialMovementType;
use App\Models\FinancialMovement;


class FinancialMovementDTO
{


    public function __construct(
        public string|int $installmentNumber,
        public string|int $totalInstallments,
        public FinancialMovementStatus|string $status,
        public FinancialMovementType|string $type,
        public float|string $value,
        public string $financialMovementGroupId,
        public string $financialMovementCategoryId,
        public ?string $dealerId,
        public ?string $movementableType,
        public ?string $movementableId,
        public ?string $description,
        public ?string $dueDate,
        public ?string $paymentDate,
    ) {}

    public static function fromFilamentForm(array $data): self
    {
        return new self(
            installmentNumber: $data['installment_number'],
            totalInstallments: $data['total_installments'],
            status: $data['status'],
            type: $data['type'],
            value: $data['value'],
            financialMovementGroupId: $data['financial_movement_group_id'],
            financialMovementCategoryId: $data['financial_movement_category_id'],
            dealerId: $data['dealer_id'] ?? null,
            movementableType: $data['movementable_type'] ?? null,
            movementableId: $data['movementable_id'] ?? null,
            description: $data['description'] ?? null,
            dueDate: $data['due_date'] ?? null,
            paymentDate: $data['payment_date'] ?? null,
        );
    }

    public function toCreateInDB(): array
    {
        return [
            'movementable_type' => $this->movementableType,
            'movementable_id' => $this->movementableId,
            'financial_movement_category_id' => $this->financialMovementCategoryId,
            'financial_movement_group_id' => $this->financialMovementGroupId,
            'installment_number' => $this->installmentNumber,
            'total_installments' => $this->totalInstallments,
            'description' => $this->description,
            'dealer_id' => $this->dealerId,
            'value' => $this->value,
            'due_date' => $this->dueDate,
            'payment_date' => $this->paymentDate,
            'status' => $this->status,
            'type' => $this->type
        ];
    }
}
