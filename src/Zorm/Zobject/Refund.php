<?php
namespace OlivierBarbier\Zorm\Zobject;

class Refund extends \OlivierBarbier\Zorm\Base
{
	protected $blackList = ['GatewayOptionData', 'PaymentId', 'RefundInvoicePaymentData'];
}
