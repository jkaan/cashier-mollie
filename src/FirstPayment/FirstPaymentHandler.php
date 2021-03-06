<?php

namespace Fitblocks\Cashier\FirstPayment;

use App\Box;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Fitblocks\Cashier\Events\MandateUpdated;
use Fitblocks\Cashier\FirstPayment\Actions\BaseAction;
use Fitblocks\Cashier\Order\Order;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Order\OrderItemCollection;
use Mollie\Api\Resources\Payment;

class FirstPaymentHandler
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $owner;

    /** @var \Mollie\Api\Resources\Payment */
    protected $payment;

    /** @var \Illuminate\Support\Collection */
    protected $actions;
    /** @var Box */
    private $box;

    /**
     * FirstPaymentHandler constructor.
     *
     * @param \Mollie\Api\Resources\Payment $payment
     * @param Box $box
     */
    public function __construct(Payment $payment, Box $box)
    {
        $this->payment = $payment;
        $this->owner = $this->extractOwner();
        $this->actions = $this->extractActions();
        $this->box = $box;
    }

    /**
     * Execute all actions for the mandate payment and return the created Order.
     *
     * @return \Fitblocks\Cashier\Order\Order
     */
    public function execute()
    {
        $order = DB::transaction(function () {
            $this->owner->mollie_mandate_id = $this->payment->mandateId;
            $this->owner->save();

            $orderItems = $this->executeActions();

            return Order::createProcessedFromItems($orderItems, [
                'mollie_payment_id' => $this->payment->id,
                'mollie_payment_status' => $this->payment->status,
                'box_id' => $this->box->id,
            ]);
        });

        event(new MandateUpdated($this->owner));

        return $order;
    }

    /**
     * Fetch the owner model using the mandate payment metadata.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function extractOwner()
    {
        $ownerType = $this->payment->metadata->owner->type;
        $ownerID = $this->payment->metadata->owner->id;

        return $ownerType::findOrFail($ownerID);
    }

    /**
     * Build the action objects from the payment metadata.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function extractActions()
    {
        $actions = new Collection((array)$this->payment->metadata->actions);

        return $actions->map(function ($actionMeta) {
            return $actionMeta->handler::createFromPayload(
                object_to_array_recursive($actionMeta),
                $this->owner
            );
        });
    }

    /**
     * Execute the Actions and return a collection of the resulting OrderItems.
     * These OrderItems are already paid for using the mandate payment.
     *
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    protected function executeActions()
    {
        $orderItems = new OrderItemCollection();

        $this->actions->each(function (BaseAction $action) use (&$orderItems) {
            $orderItems = $orderItems->concat($action->execute());
        });

        return $orderItems;
    }

    /**
     * Retrieve the owner object.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Retrieve all Action objects.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }
}
