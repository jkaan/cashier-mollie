<?php

namespace Fitblocks\Cashier\FirstPayment\Actions;

use App\Box;
use App\PaymentCalculator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Fitblocks\Cashier\Coupon\Contracts\CouponRepository;
use Fitblocks\Cashier\Coupon\RedeemedCoupon;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Order\OrderItemCollection;
use Fitblocks\Cashier\Plan\Contracts\PlanRepository;
use Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder;

class StartSubscription extends BaseAction
{
    /** @var string */
    protected $name;

    /** @var \Fitblocks\Cashier\Plan\Plan */
    protected $plan;

    /** @var \Fitblocks\Cashier\Coupon\Coupon */
    protected $coupon;

    /** @var \Carbon\Carbon */
    protected $nextPaymentAt;

    /** @var null|int */
    protected $trialDays;

    /** @var null|\Carbon\Carbon */
    protected $trialUntil;

    /** @var null|\Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder */
    protected $builder;

    /** @var CouponRepository */
    protected $couponRepository;

    /** @var Box */
    private $box;
    /** @var Carbon */
    private $startDate;

    /**
     * Create a new subscription builder instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $owner
     * @param string $name
     * @param string $plan
     * @param Box $box
     * @param Carbon $startDate
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Model $owner, string $name, string $plan, Box $box, Carbon $startDate)
    {
        $this->owner = $owner;
        $this->taxPercentage = $this->owner->taxPercentage();
        $this->name = $name;

        $this->plan = app(PlanRepository::class)::findOrFail($plan);

        $this->subtotal = $this->plan->amount();
        $this->description = $this->plan->description();
        $this->currency = $this->subtotal->getCurrency()->getCode();

        $this->couponRepository = app()->make(CouponRepository::class);
        $this->box = $box;

        $this->subtotal = (new PaymentCalculator())->calculateMoneyAmountToPayFromDay($this->plan->amount(), $startDate);

        $this->nextPaymentAt = Carbon::parse($this->plan->interval())->startOfMonth();
        $this->builder = new MandatedSubscriptionBuilder(
            $this->owner,
            $this->name,
            $this->plan->name(),
            $this->box,
            $startDate
        );

        $this->builder->nextPaymentAt($this->nextPaymentAt);
        $this->startDate = $startDate;
    }

    /**
     * @param array $payload
     * @param \Illuminate\Database\Eloquent\Model $owner
     * @return static
     * @throws \Exception
     */
    public static function createFromPayload(array $payload, Model $owner)
    {
        $action = new static($owner, $payload['name'], $payload['plan'], Box::find($payload['box_id']), Carbon::parse($payload['startDate']));

        // Already validated when preparing the first payment, so don't validate again
        $action->builder()->skipCouponValidation();

        // The coupon will be handled manually by this action
        $action->builder()->skipCouponHandling();

        if (isset($payload['taxPercentage'])) {
            $action->withTaxPercentage($payload['taxPercentage']);
        }

        if (isset($payload['trialUntil'])) {
            $action->trialUntil(Carbon::parse($payload['trialUntil']));
        }

        if (isset($payload['trialDays'])) {
            $action->trialDays($payload['trialDays']);
        }

        if (isset($payload['coupon'])) {
            $action->withCoupon($payload['coupon']);
        }

        return $action;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return array_filter([
            'handler' => self::class,
            'description' => $this->getDescription(),
            'subtotal' => money_to_mollie_array($this->getSubtotal()),
            'taxPercentage' => $this->getTaxPercentage(),
            'plan' => $this->plan->name(),
            'name' => $this->name,
            'box_id' => $this->box->id,
            'startDate' => $this->startDate->toIso8601String(),
            'trialExpires' => !empty($this->trialExpires) ? $this->trialExpires->toIso8601String() : null,
            'quantity' => !empty($this->quantity) ? $this->quantity : null,
            'nextPaymentAt' => !empty($this->nextPaymentAt) ? $this->nextPaymentAt->toIso8601String() : null,
            'trialDays' => $this->trialDays,
            'trialUntil' => !empty($this->trialUntil) ? $this->trialUntil->toIso8601String() : null,
            'coupon' => !empty($this->coupon) ? $this->coupon->name() : null,
        ]);
    }

    /**
     * Prepare a stub of OrderItems processed with the payment.
     *
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public function makeProcessedOrderItems()
    {
        return OrderItem::make($this->processedOrderItemData())->toCollection();
    }

    /**
     * @return array
     */
    protected function processedOrderItemData()
    {
        return [
            'owner_type' => get_class($this->owner),
            'owner_id' => $this->owner->id,
            'process_at' => now(),
            'description' => $this->getDescription(),
            'currency' => $this->getCurrency(),
            'unit_price' => $this->getSubtotal()->getAmount(),
            'tax_percentage' => $this->getTaxPercentage(),
            'quantity' => $this->quantity,
            'box_id' => $this->box->id,
        ];
    }

    /**
     * Returns an OrderItemCollection ready for processing right away.
     * Another OrderItem is scheduled for the next billing cycle.
     *
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     * @throws \Fitblocks\Cashier\Exceptions\PlanNotFoundException
     * @throws \Throwable
     */
    public function execute()
    {
        if (empty($this->nextPaymentAt) && !$this->isTrial()) {
            $this->builder()->nextPaymentAt(Carbon::parse($this->plan->interval()));
        }

        // Create the subscription, scheduling the next payment
        $subscription = $this->builder()->create();

        // Create an additional OrderItem for the already processed payment
        /** @var OrderItemCollection $processedItems */
        $processedItems = $subscription->orderItems()
            ->create($this->processedOrderItemData())
            ->toCollection();

        if ($this->coupon) {
            $redeemedCoupon = RedeemedCoupon::record($this->coupon, $subscription);

            if (!$this->isTrial()) {
                $processedItems = $this->coupon->applyTo($redeemedCoupon, $processedItems);
            }
        }

        $this->owner->cancelGenericTrial();

        return $processedItems;
    }

    /**
     * Specify the number of days of the trial.
     *
     * @param int $trialDays
     * @return $this
     * @throws \Fitblocks\Cashier\Exceptions\PlanNotFoundException
     * @throws \Throwable
     */
    public function trialDays(int $trialDays)
    {
        $this->trialDays = $trialDays;
        $this->builder()->trialDays($trialDays);
        $this->subtotal = money(0, $this->getCurrency());

        return $this;
    }

    /**
     * Specify the ending date of the trial.
     *
     * @param Carbon $trialUntil
     * @return $this
     * @throws \Throwable|\Fitblocks\Cashier\Exceptions\PlanNotFoundException
     */
    public function trialUntil(Carbon $trialUntil)
    {
        $this->trialUntil = $trialUntil;
        $this->builder()->trialUntil($trialUntil);
        $this->subtotal = money(0, $this->getCurrency());

        return $this;
    }

    /**
     * Specify the quantity of the subscription.
     *
     * @param int $quantity
     * @return $this
     * @throws \Throwable|\LogicException
     */
    public function quantity(int $quantity)
    {
        throw_if($quantity < 1, new \LogicException('Subscription quantity must be at least 1.'));
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return \Fitblocks\Cashier\Coupon\Coupon|null
     */
    public function coupon()
    {
        return $this->coupon;
    }

    /**
     * Specify and validate the coupon code.
     *
     * @param string $coupon
     * @return $this
     * @throws \Fitblocks\Cashier\Exceptions\CouponNotFoundException
     * @throws \Throwable
     */
    public function withCoupon(string $coupon)
    {
        $this->coupon = $this->couponRepository->findOrFail($coupon);
        $this->builder()->withCoupon($coupon);

        return $this;
    }

    /**
     * Override the default next payment date.
     *
     * @param \Carbon\Carbon $nextPaymentAt
     * @return $this
     */
    public function nextPaymentAt(Carbon $nextPaymentAt)
    {
        $this->nextPaymentAt = $nextPaymentAt;

        return $this;
    }

    /**
     * @return bool
     */
    protected function isTrial()
    {
        return !(empty($this->trialDays) && empty($this->trialUntil));
    }

    /**
     * Retrieve the subscription builder
     *
     * @return \Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder
     * @throws \Throwable|\Fitblocks\Cashier\Exceptions\PlanNotFoundException
     */
    public function builder()
    {
        if ($this->builder === null) {
            $this->builder = new MandatedSubscriptionBuilder(
                $this->owner,
                $this->name,
                $this->plan->name(),
                $this->box
            );
        }

        return $this->builder;
    }
}
