<?php
namespace Ciebit\ContactUs\Messages\Addresses\Builders;

use Ciebit\ContactUs\Messages\Addresses\Address;

interface Builder
{
    public function build(): Address;
}
