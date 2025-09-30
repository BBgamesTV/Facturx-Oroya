<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "invoice")]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $issueDate = null;

    #[ORM\OneToMany(targetEntity: InvoiceLine::class, mappedBy: "invoice", cascade: ["persist", "remove"])]
    private Collection $lines;

    #[ORM\Column(type: "json", nullable: true)]
    private array $buyer = [];

    #[ORM\Column(type: "json", nullable: true)]
    private array $seller = [];

    #[ORM\Column(type: "json", nullable: true)]
    private array $taxRepresentative = [];

    #[ORM\Column(type: "json", nullable: true)]
    private array $paymentTerms = [];

    #[ORM\Column(type: "json", nullable: true)]
    private array $totals = [];

    #[ORM\Column(type: "string", length: 3)]
    private string $currency = 'NOK';

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $note = null;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
    }

    // ----- Getters / Setters -----

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTimeInterface $issueDate): self
    {
        $this->issueDate = $issueDate;
        return $this;
    }

    /** @return Collection|InvoiceLine[] */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(InvoiceLine $line): self
    {
        $this->lines->add($line);
        return $this;
    }

    public function removeLine(InvoiceLine $line): self
    {
        $this->lines->removeElement($line);
        return $this;
    }

    public function getBuyer(): array
    {
        return $this->buyer;
    }

    public function setBuyer(array $buyer): self
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function getSeller(): array
    {
        return $this->seller;
    }

    public function setSeller(array $seller): self
    {
        $this->seller = $seller;
        return $this;
    }

    public function getTaxRepresentative(): array
    {
        return $this->taxRepresentative;
    }

    public function setTaxRepresentative(array $taxRepresentative): self
    {
        $this->taxRepresentative = $taxRepresentative;
        return $this;
    }

    public function getPaymentTerms(): array
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(array $paymentTerms): self
    {
        $this->paymentTerms = $paymentTerms;
        return $this;
    }

    public function getTotals(): array
    {
        return $this->totals;
    }

    public function setTotals(array $totals): self
    {
        $this->totals = $totals;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(string $paymentReference): self
    {
        $this->paymentReference = $paymentReference;
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
