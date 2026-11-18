<?php

namespace Webappick\zatca\Tags;

use Webappick\zatca\Tag;

class TaxNumber extends Tag
{
    public function __construct( $value ) {
        parent::__construct(2, $value);
    }
}
