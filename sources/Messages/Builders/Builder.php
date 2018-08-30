<?php
namespace Ciebit\ContactUs\Messages\Builders;

use Ciebit\ContactUs\Messages\Message;

interface Builder
{
    public function build(): Message;
}
