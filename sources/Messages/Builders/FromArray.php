<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Builders;

use Ciebit\ContactUs\Messages\Addresses\Builders\FromArray as AddressBuilder;
use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Status;
use DateTime;

class FromArray implements Builder
{
    private $data; #:array

    public function setData(array $data): Builder
    {
        $this->data = $data;
        return $this;
    }

    public function build(): Message
    {
        if (
            ! is_array($this->data) OR
            ! is_string($this->data['name'] ?? false) OR
            ! is_string($this->data['body'] ?? false) OR
            ! is_string($this->data['email'] ?? false)
        ) {
            throw new Exception('ciebit.contactus.messages.builders.invalid', 3);
        }
        
        $status = is_numeric($this->data['status'] ?? null) ? new Status((int) $this->data['status']) : Status::DRAFT();

        $message = new Message(
            $this->data['name'],
            $this->data['body'],
            $this->data['email'],
            $status
        );

        $addressBuilder = (new AddressBuilder)->setData($this->standardizeAddress($this->data));
        $address = $addressBuilder->build();
        
        $this->data['id'] && $message->setId((int) $this->data['id']);
        $address && $message->setAddress($address);
        $this->data['phone'] && $message->setPhone($this->data['phone']);
        $this->data['subject'] && $message->setSubject($this->data['subject']);
        $this->data['date_hour'] && $message->setDateHour(new DateTime($this->data['date_hour']));
        
        return $message;
    }

    private function standardizeAddress(array $data): array
    {
        return [
            'place' => $data['address_place'] ?? null,
            'number' => $data['address_number'] ?? null,
            'neighborhood' => $data['address_neighborhood'] ?? null,
            'complement' => $data['address_complement'] ?? null,
            'cep' => $data['address_cep'] ?? null,
            'city_id' => $data['address_city_id'] ?? null,
            'city_name' => $data['address_city_name'] ?? null,
            'state_name' => $data['address_state_name'] ?? null
        ];
    }
}
