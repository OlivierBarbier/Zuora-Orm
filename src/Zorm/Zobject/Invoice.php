<?php

namespace OlivierBarbier\Zorm\Zobject;

class Invoice extends \OlivierBarbier\Zorm\Base
{
    protected $blackList = ['RegenerateInvoicePDF', 'BillRunId'];
}
