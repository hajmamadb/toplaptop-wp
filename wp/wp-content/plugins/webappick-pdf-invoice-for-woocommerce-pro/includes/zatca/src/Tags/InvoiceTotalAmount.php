<?php

namespace Webappick\zatca\Tags;

use Webappick\zatca\Tag;

class InvoiceTotalAmount extends Tag
{
    public function __construct( $value ) {
        parent::__construct(4, $value);
    }
}
