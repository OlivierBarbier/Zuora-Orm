<?php

class zuora_ProductRatePlanChargeTier extends Zuora_Object
{
    protected $zType = 'ProductRatePlanChargeTier';

    public function __construct()
    {
        $this->_data = [
            'ProductRatePlanTierId' => null,
        ];
    }
}
