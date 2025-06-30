<?php

declare(strict_types=1);

namespace App\Services\CollectionPoint\Data;

class Row
{
    private $symbol;
    private $collectionPointClassification;
    private $coordinator;
    private $street;
    private $city;
    private $postalCode;
    private $phone;
    private $laboratory;
    private $isInternet;
    private $isParking;
    private $isForDisabled;
    private $model;
    private $collectionPointLocation;
    private $collectionPointPartner;
    private $isChildren;
    private $isGynecology;
    private $isDermatofit;
    private $isSwab;
    private $openSunday;
    private $openMonday;
    private $openTuesday;
    private $openWednesday;
    private $openThursday;
    private $openFriday;
    private $openSaturday;
    private $additionalInfo;
    private $collectionPointType;
    private $email;
    private $priceList;
    private $mpk;

    public function __construct()
    {
        $this->isForDisabled = false;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getCollectionPointClassification(): ?string
    {
        return $this->collectionPointClassification;
    }

    public function setCollectionPointClassification(?string $collectionPointClassification): self
    {
        $this->collectionPointClassification = $collectionPointClassification;

        return $this;
    }

    public function getCoordinator(): ?string
    {
        return $this->coordinator;
    }

    public function setCoordinator($coordinator): self
    {
        $this->coordinator = $coordinator;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLaboratory(): ?string
    {
        return $this->laboratory;
    }

    public function setLaboratory(?string $laboratory): self
    {
        $this->laboratory = $laboratory;

        return $this;
    }

    public function getIsInternet(): ?bool
    {
        return $this->isInternet;
    }

    public function setIsInternet(?bool $isInternet): self
    {
        $this->isInternet = $isInternet;

        return $this;
    }

    public function getIsParking(): ?bool
    {
        return $this->isParking;
    }

    public function setIsParking(?bool $isParking): self
    {
        $this->isParking = $isParking;

        return $this;
    }

    public function getIsForDisabled(): ?bool
    {
        return $this->isForDisabled;
    }

    public function setIsForDisabled(?bool $isForDisabled): self
    {
        $this->isForDisabled = $isForDisabled;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getCollectionPointLocation(): ?string
    {
        return $this->collectionPointLocation;
    }

    public function setCollectionPointLocation(?string $collectionPointLocation): self
    {
        $this->collectionPointLocation = $collectionPointLocation;

        return $this;
    }

    public function getCollectionPointPartner(): ?string
    {
        return $this->collectionPointPartner;
    }

    public function setCollectionPointPartner(?string $collectionPointPartner): self
    {
        $this->collectionPointPartner = $collectionPointPartner;

        return $this;
    }

    public function getIsChildren(): ?bool
    {
        return $this->isChildren;
    }

    public function setIsChildren(?bool $isChildren): self
    {
        $this->isChildren = $isChildren;

        return $this;
    }

    public function getIsGynecology(): ?bool
    {
        return $this->isGynecology;
    }

    public function setIsGynecology(?bool $isGynecology): self
    {
        $this->isGynecology = $isGynecology;

        return $this;
    }

    public function getIsDermatofit(): ?bool
    {
        return $this->isDermatofit;
    }

    public function setIsDermatofit(?bool $isDermatofit): self
    {
        $this->isDermatofit = $isDermatofit;

        return $this;
    }

    public function getIsSwab(): ?bool
    {
        return $this->isSwab;
    }

    public function setIsSwab(?bool $isSwab): self
    {
        $this->isSwab = $isSwab;

        return $this;
    }

    public function getOpenSunday(): ?string
    {
        return $this->openSunday;
    }

    public function setOpenSunday(?string $openSunday): self
    {
        $this->openSunday = $openSunday;

        return $this;
    }

    public function getOpenMonday(): ?string
    {
        return $this->openMonday;
    }

    public function setOpenMonday(?string $openMonday): self
    {
        $this->openMonday = $openMonday;

        return $this;
    }

    public function getOpenTuesday(): ?string
    {
        return $this->openTuesday;
    }

    public function setOpenTuesday(?string $openTuesday): self
    {
        $this->openTuesday = $openTuesday;

        return $this;
    }

    public function getOpenWednesday(): ?string
    {
        return $this->openWednesday;
    }

    public function setOpenWednesday(?string $openWednesday): self
    {
        $this->openWednesday = $openWednesday;

        return $this;
    }

    public function getOpenThursday(): ?string
    {
        return $this->openThursday;
    }

    public function setOpenThursday(?string $openThursday): self
    {
        $this->openThursday = $openThursday;

        return $this;
    }

    public function getOpenFriday(): ?string
    {
        return $this->openFriday;
    }

    public function setOpenFriday(?string $openFriday): self
    {
        $this->openFriday = $openFriday;

        return $this;
    }

    public function getOpenSaturday(): ?string
    {
        return $this->openSaturday;
    }

    public function setOpenSaturday(?string $openSaturday): self
    {
        $this->openSaturday = $openSaturday;

        return $this;
    }

    public function getCollectionPointType(): ?string
    {
        return $this->collectionPointType;
    }

    public function setCollectionPointType(?string $collectionPointType): self
    {
        $this->collectionPointType = $collectionPointType;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPriceList(): ?string
    {
        return $this->priceList;
    }

    public function setPriceList(?string $priceList): self
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function getMpk(): ?string
    {
        return $this->mpk;
    }

    public function setMpk(?string $mpk): self
    {
        $this->mpk = $mpk;

        return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?string $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }
}
