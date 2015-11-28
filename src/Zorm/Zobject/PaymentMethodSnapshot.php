<?php
namespace OlivierBarbier\Zorm\Zobject;

class PaymentMethodSnapshot extends \OlivierBarbier\Zorm\Base
{
	protected $blackList = ['AchAccountNumber', 'BankTransferAccountNumber', 'CreditCardNumber', 'CreditCardSecurityCode', 'GatewayOptionData', 'SkipValidation'];
}
