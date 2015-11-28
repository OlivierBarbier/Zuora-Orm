<?php

namespace OlivierBarbier\Zorm\Zobject;

class PaymentMethod extends \OlivierBarbier\Zorm\Base
{
    protected $blackList = ['AchAccountNumber', 'BankTransferAccountNumber', 'CreditCardNumber', 'CreditCardSecurityCode', 'GatewayOptionData', 'SkipValidation'];
}
