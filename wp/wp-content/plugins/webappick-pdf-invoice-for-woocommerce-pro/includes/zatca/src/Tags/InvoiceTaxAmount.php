<?php

namespace Webappick\zatca\Tags;

use Webappick\zatca\Tag;

class InvoiceTaxAmount extends Tag
{
    public function __construct( $value ) {
        parent::__construct(5, $value);
    }
}
