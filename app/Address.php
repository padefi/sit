<?php

namespace App;

class Address {
    public string $street;
    public int $streetNumber;
    public ?string $floor;
    public ?string $apartment;
    public string $city;
    public string $state;
    public string $postalCode;
    public int $osm_id;
    /**
     * Create a new class instance.
     */
    public function __construct(array $data) {
        $this->street = $data['street'];
        $this->streetNumber = $data['streetNumber'];
        $this->floor = $data['floor'] ?? '';
        $this->apartment = $data['apartment'] ?? '';
        $this->city = $data['city'];
        $this->state = $data['state'];
        $this->postalCode = $data['postalCode'];
        $this->osm_id = $data['osm_id'];
    }
}
