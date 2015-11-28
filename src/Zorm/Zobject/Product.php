<?php

namespace OlivierBarbier\Zorm\Zobject;

class Product extends \OlivierBarbier\Zorm\Base
{
    protected $blackList = [];

    public function addRatePlan(array $fields)
    {
        $productRatePlan = app('OlivierBarbier\Zorm\Zobject\ProductRatePlan');

        $fields['ProductId'] = $this->Id;

        $create = $productRatePlan->fill((object) $fields)->create();

        return $productRatePlan->find($create->result->Id);
    }
}
