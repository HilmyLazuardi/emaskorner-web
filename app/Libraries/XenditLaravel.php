<?php

namespace App\Libraries;

use Xendit\Xendit;

/**
 * Xendit API - Laravel Library
 * Based on Xendit Official Documentation (https://docs.xendit.co/)
 * 
 * require xendit/xendit-php (https://github.com/xendit/xendit-php)
 */

class XenditLaravel
{
    public static function create_invoice($external_id, $amount, $description, $payer_email, $order_id)
    {
        $api_key = env('XENDIT_API_KEY');
        Xendit::setApiKey($api_key);

        /**
         * SET INVOICE DURATION
         * 
         * Duration of time that the end customer is given to pay the invoice before expiration (in seconds, since creation).
         * Default is 24 hours (86,400 seconds).
         * Min number: 1 second
         * Max number: 31536000 seconds (1 year)
         */
        $invoice_duration = env('XENDIT_INVOICE_DURATION');

        /**
         * URL that the end customer will be redirected to upon successful invoice payment. (Max 255 chars)
         * Example : https://yourcompany.com/example_item/10/success_page
         */
        $success_redirect_url = env('XENDIT_SUCCESS_REDIRECT_URL') . $external_id;

        /**
         * URL that end user will be redirected to upon expiration of this invoice. (Max 255 chars)
         * Example : https://yourcompany.com/example_item/10/failed_checkout
         */
        $failure_redirect_url = env('XENDIT_FAILURE_REDIRECT_URL') . $external_id;

        /**
         * Currency of the amount that you created.
         * Possible values : IDR or PHP
         */
        $currency = env('XENDIT_CURRENCY');

        $params = [
            'external_id' => $external_id,
            'amount' => $amount,
            'description' => $description,
            'payer_email' => $payer_email,
            'invoice_duration' => $invoice_duration,
            'success_redirect_url' => $success_redirect_url,
            'failure_redirect_url' => $failure_redirect_url,
            'currency' => $currency,
        ];

        $createInvoice = \Xendit\Invoice::create($params);

        return [
            'request' => $params,
            'response' => $createInvoice
        ];

        // *Sample Success Response
        // array:21 [▼
        //     "id" => "612c970dc795ee1f91e0f06a"
        //     "external_id" => "TLK-20210830024448-9163"
        //     "user_id" => "60e68ceeefa7650e385bccc2"
        //     "status" => "PENDING"
        //     "merchant_name" => "PT Lumina Kaya Indonesia"
        //     "merchant_profile_picture_url" => "https://xnd-companies.s3.amazonaws.com/prod/1625728489957_391.png"
        //     "amount" => 10000
        //     "payer_email" => "vicky@isysedge.com"
        //     "description" => "LK-20210825-1162"
        //     "expiry_date" => "2021-08-30T08:35:04.338Z"
        //     "invoice_url" => "https://checkout-staging.xendit.co/web/612c970dc795ee1f91e0f06a"
        //     "available_banks" => array:5 [▼
        //     0 => array:7 [▼
        //         "bank_code" => "MANDIRI"
        //         "collection_type" => "POOL"
        //         "bank_account_number" => "8860880178211"
        //         "transfer_amount" => 10000
        //         "bank_branch" => "Virtual Account"
        //         "account_holder_name" => "PT LUMINA KAYA INDONESIA"
        //         "identity_amount" => 0
        //     ]
        //     1 => array:7 [▼
        //         "bank_code" => "BRI"
        //         "collection_type" => "POOL"
        //         "bank_account_number" => "9200114085648"
        //         "transfer_amount" => 10000
        //         "bank_branch" => "Virtual Account"
        //         "account_holder_name" => "PT LUMINA KAYA INDONESIA"
        //         "identity_amount" => 0
        //     ]
        //     2 => array:7 [▼
        //         "bank_code" => "BNI"
        //         "collection_type" => "POOL"
        //         "bank_account_number" => "880840413387"
        //         "transfer_amount" => 10000
        //         "bank_branch" => "Virtual Account"
        //         "account_holder_name" => "PT LUMINA KAYA INDONESIA"
        //         "identity_amount" => 0
        //     ]
        //     3 => array:7 [▼
        //         "bank_code" => "PERMATA"
        //         "collection_type" => "POOL"
        //         "bank_account_number" => "729374766312"
        //         "transfer_amount" => 10000
        //         "bank_branch" => "Virtual Account"
        //         "account_holder_name" => "PT LUMINA KAYA INDONESIA"
        //         "identity_amount" => 0
        //     ]
        //     4 => array:7 [▼
        //         "bank_code" => "BCA"
        //         "collection_type" => "POOL"
        //         "bank_account_number" => "1076651125226"
        //         "transfer_amount" => 10000
        //         "bank_branch" => "Virtual Account"
        //         "account_holder_name" => "PT LUMINA KAYA INDONESIA"
        //         "identity_amount" => 0
        //     ]
        //     ]
        //     "available_retail_outlets" => array:2 [▼
        //     0 => array:3 [▼
        //         "retail_outlet_name" => "ALFAMART"
        //         "payment_code" => "TEST437387"
        //         "transfer_amount" => 10000
        //     ]
        //     1 => array:3 [▼
        //         "retail_outlet_name" => "INDOMARET"
        //         "payment_code" => "TEST202293"
        //         "transfer_amount" => 10000
        //     ]
        //     ]
        //     "available_ewallets" => array:4 [▼
        //     0 => array:1 [▼
        //         "ewallet_type" => "OVO"
        //     ]
        //     1 => array:1 [▼
        //         "ewallet_type" => "DANA"
        //     ]
        //     2 => array:1 [▼
        //         "ewallet_type" => "SHOPEEPAY"
        //     ]
        //     3 => array:1 [▼
        //         "ewallet_type" => "LINKAJA"
        //     ]
        //     ]
        //     "should_exclude_credit_card" => false
        //     "should_send_email" => false
        //     "success_redirect_url" => "http://localhost:8888/me/siorensys-dev/beta/public/dev/xendit/success-page"
        //     "failure_redirect_url" => "http://localhost:8888/me/siorensys-dev/beta/public/dev/xendit/failed-page"
        //     "created" => "2021-08-30T08:30:06.626Z"
        //     "updated" => "2021-08-30T08:30:06.626Z"
        //     "currency" => "IDR"
        // ]
    }
}
