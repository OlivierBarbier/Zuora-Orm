<?php
namespace OlivierBarbier\Zorm\Zobject;

class Payment extends \OlivierBarbier\Zorm\Base
{
	protected $blackList = ['AppliedInvoiceAmount', 'GatewayOptionData', 'InvoiceId', 'InvoiceNumber', 'InvoicePaymentData'];
}
