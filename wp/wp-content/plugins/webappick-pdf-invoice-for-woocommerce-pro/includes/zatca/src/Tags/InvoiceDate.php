<?php

namespace Webappick\zatca\Tags;

use Webappick\zatca\Tag;

class InvoiceDate extends Tag
{
    public function __construct( $value ) {
        parent::__construct(3, $value);
    }
}
