<?php

namespace OlivierBarbier\Zorm\Zobject;

class Account extends \OlivierBarbier\Zorm\Base
{
    protected $blackList = ['TaxCompanyCode', 'VATId'];

    public function activeSubscriptions($columns = ['*'])
    {
        $columns = ($columns[0] != '*' ? array_merge($columns, ['Status']) : $columns);

        return $this->subscriptions($columns, true, true)->where('Status', '=', 'Active')->get($columns);
    }

    public function cancel()
    {
        $instance = $this->zuora();

        $account = new \Zuora_Account();
        $account->Id = $this->Id;
        $account->Status = 'Canceled';

        $update = $instance->update(array($account));

        $this->throwExceptionOnError($update);

        $this->refresh();

        return $update;
    }

    public function subscribe($subscription, $productRatePlan)
    {
        $sdata = new \Zuora_SubscriptionData($subscription->castToZuora());
// print_r($sdata);

        $subcriptionRatePlan = app('OlivierBarbier\Zorm\Zobject\RatePlan');
        $subcriptionRatePlan->ProductRatePlanId = $productRatePlan->Id;

        $sdata->addRatePlanData(new \Zuora_RatePlanData(
            $subcriptionRatePlan->castToZuora(['ProductRatePlanId'])
        ));

        $zSubscribeOptions = new \Zuora_SubscribeOptions(false, false);

        $subscribe = $this->zuora()->subscribeWithExistingAccount(
            $this->castToZuora(['Id']),
            $sdata,
            $zSubscribeOptions
        );

// print_r($subscribe);

        $this->throwExceptionOnError($subscribe);

        return $subscribe;
    }
}
