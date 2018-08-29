<?php
namespace Ciebit\ContactUs\Messages\Builders;

use Ciebit\ContactUs\Messages\Message;

interface Builder
{
    public function setData(array $data): self;
    public function build(): Message;
}
