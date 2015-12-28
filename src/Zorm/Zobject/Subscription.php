<?php

namespace OlivierBarbier\Zorm\Zobject;

class Subscription extends \OlivierBarbier\Zorm\Base
{
    protected $blackList = ['AncestorAccountId'];

    public function cancel($cancelDate)
    {
        $instance = $this->zuora();

        $amendment = new \Zuora_Amendment();
        $amendment->EffectiveDate = $cancelDate;
        $amendment->Name = 'cancel'.time();
        $amendment->Status = 'Draft';
        $amendment->SubscriptionId = $this->Id;
        $amendment->Type = 'Cancellation';
// var_dump($cancelDate, $amendment);
        $result = $instance->create(array($amendment));

// print_r($result);

        $this->throwExceptionOnError($result);

        if (isset($result->result->Id)) {
            $amendmentId = $result->result->Id;
            $amendment = new \Zuora_Amendment();
            $amendment->Id = $amendmentId;
            $amendment->ContractEffectiveDate = date('Y-m-d');
            $amendment->Status = 'Completed';
            $result = $instance->update(array($amendment));
        }

        $this->throwExceptionOnError($result);

        $zsub = $this->where('PreviousSubscriptionId', '=', $this->Id)
            ->get()
            ->first()
            ->castToZuora();

        $this->fill($zsub);

        return $result;
    }

    public function domainNameRatePlans($columns = ['*'])
    {
        $columns = ($columns[0] != '*' ? array_unique(array_merge($columns, ['Name'])) : $columns);

        return $this->ratePlans($columns)->filter(function ($ratePlan) {
            return false !== stripos($ratePlan->Name, 'Nom de domaine');
        });
    }

    public function zenchefRatePlans($columns = ['*'])
    {
        $columns = ($columns[0] != '*' ? array_unique(array_merge($columns, ['Name'])) : $columns);

        return $this->ratePlans($columns)->filter(function ($ratePlan) {
            $product = $ratePlan->productRatePlan(['ProductId'])->product(['Name']);

            return 0 == strcasecmp($product->Name, 'BOOST') or
                0 == strcasecmp($product->Name, 'E-reputation') or
                0 == strcasecmp($product->Name, 'EXPRESS') or
                0 == strcasecmp($product->Name, 'Mobihotel') or
                false != stripos($product->Name, 'Formule') or
                false != stripos($product->Name, 'Abonnement')
            ;
        });
    }
}
