<?php

namespace Webappick\zatca\Tags;

use Webappick\zatca\Tag;

class Seller extends Tag
{
    public function __construct( $value ) {
        parent::__construct(1, $value);
    }
}
