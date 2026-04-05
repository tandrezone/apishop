<?php

declare(strict_types=1);

namespace App\Entity;

class Order
{
    private ?int $id = null;
    private int $userId;
    private float $totalAmount;
    private string $status; // 'pending', 'processing', 'completed', 'cancelled'
    private array $items = []; // Array of order items
    private \DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct(
        int $userId,
        float $totalAmount,
        string $status = 'pending'
    ) {
        $this->userId = $userId;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function addItem(array $item): void
    {
        $this->items[] = $item;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'items' => $this->items,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
