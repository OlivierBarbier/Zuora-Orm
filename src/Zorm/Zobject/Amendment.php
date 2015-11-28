<?php

namespace OlivierBarbier\Zorm\Zobject;

class Amendment extends \OlivierBarbier\Zorm\Base
{
    protected $blackList = ['DestinationAccountId', 'DestinationInvoiceOwnerId', 'RatePlanData'];
}
