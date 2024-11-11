<?php

namespace App;

use Ramsey\Uuid\Type\Decimal;

class Address {
    public string $street;
    public int $streetNumber;
    public ?string $floor;
    public ?string $apartment;
    public string $city;
    public string $state;
    public string $country;
    public string $postalCode;
    public int $osm_id;
    public string $latitude;
    public string $longitude;
    /**
     * Create a new class instance.
     */
    public function __construct(array $data) {
        $this->street = $data['street'];
        $this->streetNumber = $data['streetNumber'] ?? 0;
        $this->floor = $data['floor'] ?? '';
        $this->apartment = $data['apartment'] ?? '';
        $this->city = $data['city'];
        $this->state = $data['state'];
        $this->country = $data['country'];
        $this->postalCode = $data['postalCode'];
        $this->osm_id = $data['osm_id'];
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
    }
}
