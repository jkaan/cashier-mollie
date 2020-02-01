<?php

namespace Fitblocks\Cashier;

use App\Repository\BoxRepositoryInterface;
use Fitblocks\Cashier\Order\Contracts\MinimumPayment as MinimumPaymentContract;
use Mollie\Api\Resources\Mandate;

class MinimumPayment implements MinimumPaymentContract
{
    /**
     * @param \Mollie\Api\Resources\Mandate $mandate
     * @param $currency
     * @return \Money\Money
     */
    public static function forMollieMandate(Mandate $mandate, $currency)
    {
        $boxRepo = app(BoxRepositoryInterface::class);
        $profileId = $boxRepo->getActiveBox()->mollie_profile_id;
        
        return mollie_object_to_money(
            mollie()
                ->methods()->get($mandate->method, ['currency' => $currency, 'profileId' => $profileId])
                ->minimumAmount
        );
    }

    /**
     * @param string $mandateId
     * @param string $currency
     * @return \Money\Money
     */
    public static function forMollieMandateId(string $mandateId, string $currency)
    {
        return static::forMollieMandate(
            mollie()->mandates()->get($mandateId),
            $currency
        );
    }
}
