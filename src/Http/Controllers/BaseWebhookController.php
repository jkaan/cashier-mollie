<?php

namespace Fitblocks\Cashier\Http\Controllers;

use App\MollieWrapper;
use App\Repository\BoxRepositoryInterface;
use Mollie\Api\Exceptions\ApiException;

abstract class BaseWebhookController
{
    /**
     * Fetch a payment from Mollie using its ID.
     * Returns null if the payment cannot be retrieved.
     *
     * @param $id
     * @return \Mollie\Api\Resources\Payment|null
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getPaymentById($id)
    {
        try {
            return (new MollieWrapper(app(BoxRepositoryInterface::class)))->getPayment($id);
        } catch (ApiException $e) {
            if(! config('app.debug')) {
                // Prevent leaking information
                return null;
            }
            throw $e;
        }
    }
}
