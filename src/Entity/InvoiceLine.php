<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class InvoiceLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $lineId;

    #[ORM\Column(type: "string", length: 255)]
    private string $globalId;

    #[ORM\Column(type: "string", length: 255)]
    private string $sellerId;

    #[ORM\Column(type: "string", length: 255)]
    private string $productName;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "json", nullable: true)]
    private array $characteristics = [];

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $grossPrice = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $netPrice = null;

    #[ORM\Column(type: "float")]
    private float $quantity = 1;

    #[ORM\Column(type: "string", length: 10)]
    private string $unit = 'NAR';

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $taxRate = null;

    #[ORM\Column(type: "string", length: 2)]
    private string $taxCategory = 'S';

    #[ORM\Column(type: "json", nullable: true)]
    private array $allowances = [];

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $note = null;

    // ----------------------
    // Getters et Setters
    // ----------------------

    public function getLineId(): string
    {
        return $this->lineId;
    }

    public function setLineId(string $lineId): self
    {
        $this->lineId = $lineId;
        return $this;
    }

    public function getGlobalId(): string
    {
        return $this->globalId;
    }

    public function setGlobalId(string $globalId): self
    {
        $this->globalId = $globalId;
        return $this;
    }

    public function getSellerId(): string
    {
        return $this->sellerId;
    }

    public function setSellerId(string $sellerId): self
    {
        $this->sellerId = $sellerId;
        return $this;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCharacteristics(): array
    {
        return $this->characteristics;
    }

    public function setCharacteristics(array $characteristics): self
    {
        $this->characteristics = $characteristics;
        return $this;
    }

    public function addCharacteristic(string $desc, string $value): self
    {
        $this->characteristics[] = ['desc' => $desc, 'value' => $value];
        return $this;
    }

    public function getGrossPrice(): ?float
    {
        return $this->grossPrice;
    }

    public function setGrossPrice(?float $grossPrice): self
    {
        $this->grossPrice = $grossPrice;
        return $this;
    }

    public function getNetPrice(): ?float
    {
        return $this->netPrice;
    }

    public function setNetPrice(?float $netPrice): self
    {
        $this->netPrice = $netPrice;
        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function getTaxRate(): ?float
    {
        return $this->taxRate;
    }

    public function setTaxRate(?float $taxRate): self
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function getTaxCategory(): string
    {
        return $this->taxCategory;
    }

    public function setTaxCategory(string $taxCategory): self
    {
        $this->taxCategory = $taxCategory;
        return $this;
    }

    public function getAllowances(): array
    {
        return $this->allowances;
    }

    public function setAllowances(array $allowances): self
    {
        $this->allowances = $allowances;
        return $this;
    }

    public function addAllowance(
        bool $charge = false,
        float $amount = 0,
        string $reason = ''
    ): self {
        $this->allowances[] = [
            'charge' => $charge,
            'amount' => $amount,
            'reason' => $reason,
        ];
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }
}
