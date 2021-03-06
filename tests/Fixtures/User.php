<?php

namespace Fitblocks\Cashier\Tests\Fixtures;

use Fitblocks\Cashier\FitblocksBillable;
use Illuminate\Database\Eloquent\Model;
use Fitblocks\Cashier\Order\Contracts\ProvidesInvoiceInformation;

class User extends Model implements ProvidesInvoiceInformation
{
    use FitblocksBillable;

    protected $dates = ['trial_ends_at'];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the receiver information for the invoice.
     * Typically includes the name and some sort of (E-mail/physical) address.
     *
     * @return array An array of strings
     */
    public function getInvoiceInformation()
    {
        return [$this->name, $this->email];
    }

    /**
     * Get additional information to be displayed on the invoice.
     * Typically a note provided by the customer.
     *
     * @return string|null
     */
    public function getExtraBillingInformation()
    {
        return $this->extra_billing_information;
    }
}
