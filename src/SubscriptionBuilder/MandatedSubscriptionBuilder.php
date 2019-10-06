<?php

namespace Fitblocks\Cashier\SubscriptionBuilder;

use App\Box;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Fitblocks\Cashier\Coupon\Contracts\CouponRepository;
use Fitblocks\Cashier\Plan\Contracts\PlanRepository;
use Fitblocks\Cashier\Subscription;
use Fitblocks\Cashier\SubscriptionBuilder\Contracts\SubscriptionBuilder as Contract;

/**
 * Creates and configures a subscription for an existing Mollie Mandate.
 */
class MandatedSubscriptionBuilder implements Contract
{
    /**
     * The model that is subscribing.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The name of the subscription.
     *
     * @var string
     */
    protected $name;

    /**
     * The quantity of the subscription.
     *
     * @var integer
     */
    protected $quantity = 1;

    /**
     * The date and time the trial will expire.
     *
     * @var Carbon
     */
    protected $trialExpires;

    /**
     * When the first (next) payment should be processed once the subscription has been created.
     *
     * @var Carbon
     */
    protected $nextPaymentAt;

    /**
     * The Plan being subscribed to.
     *
     * @var \Fitblocks\Cashier\Plan\Plan
     */
    protected $plan;

    /** @var \Fitblocks\Cashier\Coupon\Coupon */
    protected $coupon;

    /** @var bool */
    protected $handleCoupon = true;

    /** @var bool */
    protected $validateCoupon = true;
    /** @var Box */
    private $box;
    /** @var Carbon */
    private $startDate;

    /**
     * Create a new subscription builder instance.
     *
     * @param mixed $owner
     * @param string $name
     * @param string $plan
     * @param Box $box
     */
    public function __construct(Model $owner, string $name, string $plan, Box $box, Carbon $startDate)
    {
        $this->name = $name;
        $this->owner = $owner;
        $this->nextPaymentAt = Carbon::now();
        $this->plan = app(PlanRepository::class)::findOrFail($plan);
        $this->box = $box;
        $this->startDate = $startDate;
    }

    /**
     * Create a new Cashier subscription.
     *
     * @return Subscription
     * @throws \Fitblocks\Cashier\Exceptions\CouponException
     */
    public function create()
    {
        $now = $this->startDate;

        return DB::transaction(function () use ($now) {
            $subscription = $this->makeSubscription($now);
            $subscription->save();

            if ($this->coupon) {
                if ($this->validateCoupon) {
                    $this->coupon->validatbeFor($subscription);

                    if ($this->handleCoupon) {
                        $this->coupon->redeemFor($subscription);
                    }
                }
            }

            $subscription->scheduleNewOrderItemAt($this->nextPaymentAt);
            $subscription->save();

            $this->owner->cancelGenericTrial();

            return $subscription;
        });
    }

    /**
     * Prepare a not yet persisted Subscription model
     *
     * @param null|Carbon $now
     * @return Subscription $subscription
     */
    public function makeSubscription($now = null)
    {
        return $this->owner->subscriptionsFitblocks()->make([
            'name' => $this->name,
            'plan' => $this->plan->name(),
            'quantity' => $this->quantity,
            'tax_percentage' => $this->owner->taxPercentage() ?: 0,
            'trial_ends_at' => $this->trialExpires,
            'cycle_started_at' => $now ?: now(),
            'cycle_ends_at' => $this->nextPaymentAt,
            'box_id' => $this->box->id,
        ]);
    }

    /**
     * Specify the number of days of the trial.
     *
     * @param int $trialDays
     * @return $this
     */
    public function trialDays(int $trialDays)
    {
        return $this->trialUntil(now()->addDays($trialDays));
    }

    /**
     * Specify the ending date of the trial.
     *
     * @param Carbon $trialUntil
     * @return $this
     */
    public function trialUntil(Carbon $trialUntil)
    {
        $this->trialExpires = $trialUntil;
        $this->nextPaymentAt = $trialUntil;

        return $this;
    }

    /**
     * Specify the quantity of the subscription.
     *
     * @param int $quantity
     * @return $this
     */
    public function quantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Specify a coupon.
     *
     * @param string $coupon
     * @return $this|\Fitblocks\Cashier\SubscriptionBuilder\Contracts\SubscriptionBuilder
     * @throws \Fitblocks\Cashier\Exceptions\CouponNotFoundException
     */
    public function withCoupon(string $coupon)
    {
        /** @var CouponRepository $repository */
        $repository = app()->make(CouponRepository::class);
        $this->coupon = $repository->findOrFail($coupon);

        return $this;
    }

    /**
     * Override the default next payment date. This is superseded by the trial end date.
     *
     * @param \Carbon\Carbon $nextPaymentAt
     * @return MandatedSubscriptionBuilder
     */
    public function nextPaymentAt(Carbon $nextPaymentAt)
    {
        $this->nextPaymentAt = $nextPaymentAt;

        return $this;
    }

    /**
     * Skip validating the coupon when creating the subscription.
     *
     * @return $this
     */
    public function skipCouponValidation()
    {
        $this->validateCoupon = false;

        return $this;
    }

    /**
     * Skip handling the coupon completely when creating the subscription.
     *
     * @return $this
     */
    public function skipCouponHandling()
    {
        $this->handleCoupon = false;

        return $this;
    }
}
