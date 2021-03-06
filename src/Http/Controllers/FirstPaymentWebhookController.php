<?php

namespace Fitblocks\Cashier\Http\Controllers;

use App\Repository\BoxRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Fitblocks\Cashier\Events\FirstPaymentFailed;
use Fitblocks\Cashier\Events\FirstPaymentPaid;
use Fitblocks\Cashier\FirstPayment\FirstPaymentHandler;
use Symfony\Component\HttpFoundation\Response;

class FirstPaymentWebhookController extends BaseWebhookController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Mollie\Api\Exceptions\ApiException Only in debug mode
     */
    public function handleWebhook(Request $request)
    {
        $payment = $this->getPaymentById($request->get('id'));
        $box = app(BoxRepositoryInterface::class)->getActiveBox();

        if ($payment) {
            if ($payment->isPaid()) {
                $order = (new FirstPaymentHandler($payment, $box))->execute();

                Event::dispatch(new FirstPaymentPaid($payment, $order));
            } elseif ($payment->isFailed()) {
                Event::dispatch(new FirstPaymentFailed($payment));
            }
        }

        return new Response(null, 200);
    }
}
