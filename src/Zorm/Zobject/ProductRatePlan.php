<?php
namespace OlivierBarbier\Zorm\Zobject;

class ProductRatePlan extends \OlivierBarbier\Zorm\Base
{
	protected $blackList = ['ActiveCurrencies'];

	public function addCharge(array $fields, $productRatePlanChargeTier)
	{
		$productRatePlanCharge = app('OlivierBarbier\Zorm\Zobject\ProductRatePlanCharge');

		$fields["ProductRatePlanId"] = $this->Id;

		$productRatePlanCharge->fill((object)$fields);
		
		$productRatePlanCharge->ProductRatePlanChargeTierData = $this->makeProductRatePlanChargeTierData($productRatePlanChargeTier);

		$create = $productRatePlanCharge->create();

		return $productRatePlanCharge->find($create->result->Id);
	}

	protected function makeProductRatePlanChargeTierData($productRatePlanChargeTier)
	{
		$productRatePlanChargeTierData = new \stdClass;
		$productRatePlanChargeTierData->ProductRatePlanChargeTier = $productRatePlanChargeTier;
		return $productRatePlanChargeTierData;
	}
}
