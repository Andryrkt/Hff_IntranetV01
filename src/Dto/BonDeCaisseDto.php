<?php

namespace App\Dto;

use DateTime;

class BonDeCaisseDto
{
	private ?string $numeroCde = null;
	private ?string $numeroDa = null;
	private ?string $numeroOr = null;
	private ?float $montant = null;
	private ?DateTime $date = null;
	private ?string $fournisseur = null;
	private ?int $numeroVersion = null;
	private ?string $fichier = null;
	private ?string $utilisateur = null;

	public function getNumeroCde(): ?string
	{
		return $this->numeroCde;
	}

	public function setNumeroCde(?string $numeroCde): self
	{
		$this->numeroCde = $numeroCde;
		return $this;
	}

	public function getNumeroDa(): ?string
	{
		return $this->numeroDa;
	}

	public function setNumeroDa(?string $numeroDa): self
	{
		$this->numeroDa = $numeroDa;
		return $this;
	}

	public function getNumeroOr(): ?string
	{
		return $this->numeroOr;
	}

	public function setNumeroOr(?string $numeroOr): self
	{
		$this->numeroOr = $numeroOr;
		return $this;
	}

	public function getMontant(): ?float
	{
		return $this->montant;
	}

	public function setMontant(?float $montant): self
	{
		$this->montant = $montant;
		return $this;
	}

	public function getDate(): ?DateTime
	{
		return $this->date;
	}

	public function setDate(?DateTime $date): self
	{
		$this->date = $date;
		return $this;
	}

	public function getFournisseur(): ?string
	{
		return $this->fournisseur;
	}

	public function setFournisseur(?string $fournisseur): self
	{
		$this->fournisseur = $fournisseur;
		return $this;
	}

	public function getNumeroVersion(): ?int
	{
		return $this->numeroVersion;
	}

	public function setNumeroVersion(?int $numeroVersion): self
	{
		$this->numeroVersion = $numeroVersion;
		return $this;
	}

	public function getFichier(): ?string
	{
		return $this->fichier;
	}

	public function setFichier(?string $fichier): self
	{
		$this->fichier = $fichier;
		return $this;
	}

	public function getUtilisateur(): ?string
	{
		return $this->utilisateur;
	}

	public function setUtilisateur(?string $utilisateur): self
	{
		$this->utilisateur = $utilisateur;
		return $this;
	}

	/**
	 * Optional: instancier depuis un tableau associatif
	 */
	public static function fromArray(array $data): self
	{
		$dto = new self();
		if (isset($data['numeroCde'])) $dto->setNumeroCde($data['numeroCde']);
		if (isset($data['numeroDa'])) $dto->setNumeroDa($data['numeroDa']);
		if (isset($data['numeroOr'])) $dto->setNumeroOr($data['numeroOr']);
		if (isset($data['montant'])) $dto->setMontant((float)$data['montant']);
		if (isset($data['date'])) $dto->setDate($data['date'] instanceof DateTime ? $data['date'] : new DateTime($data['date']));
		if (isset($data['fournisseur'])) $dto->setFournisseur($data['fournisseur']);
		if (isset($data['numeroVersion'])) $dto->setNumeroVersion((int)$data['numeroVersion']);
		if (isset($data['fichier'])) $dto->setFichier($data['fichier']);
		if (isset($data['utilisateur'])) $dto->setUtilisateur($data['utilisateur']);
		return $dto;
	}
}
