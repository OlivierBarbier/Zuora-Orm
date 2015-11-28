<?php

class zuora_ProductRatePlanCharge extends Zuora_Object
{
    protected $zType = 'ProductRatePlanCharge';

    public function __construct()
    {
        $this->_data = array(
            'ProductRatePlanId' => null,
        );
    }
}
