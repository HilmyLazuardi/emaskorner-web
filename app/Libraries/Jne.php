<?php

namespace App\Libraries;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

/**
 * JNE API - Laravel Library
 * Based on JNE Official Documentation (https://apidash.jne.co.id)
 */

class Jne
{
    // Sandbox
    // public $endpoint_tariff = 'http://apiv2.jne.co.id:10102/tracing/api/pricedev';
    // public $endpoint_generate_awb = 'http://apiv2.jne.co.id:10102/tracing/api/generatecnote';

    // PRODUCTION
    public $endpoint_tariff = 'http://apiv2.jne.co.id:10101/tracing/api/pricedev';
    public $endpoint_generate_awb = 'http://apiv2.jne.co.id:10101/tracing/api/generatecnote';

    public static function check_tariff($origin, $destination, $weight = 1)
    {
        $this_obj = new Jne();
        $endpoint = $this_obj->endpoint_tariff;

        $username = env('JNE_USERNAME');
        $api_key = env('JNE_API_KEY');

        if (!$username || !$api_key) {
            if (env('APP_DEBUG')) {
                return 'Auth credentials for JNE is not set';
            } else {
                return false;
            }
        }

        /**
         * origin & destination used area code that generated by JNE
         * ORIGIN based on kabupaten/kotamadya
         * DESTINATION based on kecamatan
         * 
         * weight in kg
         * weight support angka 1 decimal
         * 1,3 kg = 2 kg
         * 1,2 kg = 1 kg
         */

        // set minimum
        if ($weight < 1) {
            $weight = 1;
        }

        $client = new Client();
        $result = $client->request('POST', $endpoint, [
            'form_params' => [
                'username' => $username,
                'api_key' => $api_key,
                'from' => $origin,
                'thru' => $destination,
                'weight' => $weight
            ]
        ]);

        $response = json_decode($result->getBody()->getContents());

        if (isset($response->price)) {
            # SUCCESS
            $data = $response->price; // array
            return [
                'status' => true,
                'data' => $data
            ];

            // *Sample Success Response
            // array:2 [▼
            //     "status" => true
            //     "data" => array:8 [▼
            //         0 => {#745 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "JTR"
            //             +"service_code": "JTR18"
            //             +"goods_type": "Paket"
            //             +"currency": "IDR"
            //             +"price": "30000"
            //             +"etd_from": "3"
            //             +"etd_thru": "4"
            //             +"times": "D"
            //         }
            //         1 => {#747 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "JTR250"
            //             +"service_code": "JTR250"
            //             +"goods_type": "Paket"
            //             +"currency": "IDR"
            //             +"price": "850000"
            //             +"etd_from": "3"
            //             +"etd_thru": "4"
            //             +"times": "D"
            //         }
            //         2 => {#760 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "JTR<150"
            //             +"service_code": "JTR<150"
            //             +"goods_type": "Paket"
            //             +"currency": "IDR"
            //             +"price": "500000"
            //             +"etd_from": "3"
            //             +"etd_thru": "4"
            //             +"times": "D"
            //         }
            //         3 => {#750 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "JTR>250"
            //             +"service_code": "JTR>250"
            //             +"goods_type": "Paket"
            //             +"currency": "IDR"
            //             +"price": "1200000"
            //             +"etd_from": "3"
            //             +"etd_thru": "4"
            //             +"times": "D"
            //         }
            //         4 => {#757 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "OKE"
            //             +"service_code": "OKE19"
            //             +"goods_type": "Document/Paket"
            //             +"currency": "IDR"
            //             +"price": "10000"
            //             +"etd_from": "2"
            //             +"etd_thru": "3"
            //             +"times": "D"
            //         }
            //         5 => {#759 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "REG"
            //             +"service_code": "REG19"
            //             +"goods_type": "Document/Paket"
            //             +"currency": "IDR"
            //             +"price": "11000"
            //             +"etd_from": "1"
            //             +"etd_thru": "2"
            //             +"times": "D"
            //         }
            //         6 => {#761 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "SPS"
            //             +"service_code": "SPS19"
            //             +"goods_type": "Document/Paket"
            //             +"currency": "IDR"
            //             +"price": "403000"
            //             +"etd_from": null
            //             +"etd_thru": null
            //             +"times": null
            //         }
            //         7 => {#746 ▼
            //             +"origin_name": "JAKARTA"
            //             +"destination_name": "BANDUNG"
            //             +"service_display": "YES"
            //             +"service_code": "YES19"
            //             +"goods_type": "Document/Paket"
            //             +"currency": "IDR"
            //             +"price": "24000"
            //             +"etd_from": "1"
            //             +"etd_thru": "1"
            //             +"times": "D"
            //         }
            //     ]
            // ]
        }

        if (isset($response->error)) {
            # ERROR
            $data = $response->error; // array
            return [
                'status' => false,
                'info' => $data
            ];

            // * Error Response Sample
            // array:2 [▼
            //     "status" => false
            //     "info" => "Price Not Found."
            // ]
        }

        # INTERNAL ERROR
        return [
            'status' => false,
            'info' => 'Internal Server Error in JNE Library'
        ];
    }

    public static function create_order($order_id, $shipper, $customer, $product, $origin, $destination, $options)
    {
        $this_obj = new Jne();
        $endpoint = $this_obj->endpoint_generate_awb;

        $username = env('JNE_USERNAME_AWB');
        $api_key = env('JNE_API_KEY_AWB');

        if (!$username || !$api_key) {
            return [
                'status' => false,
                'info' => 'Auth credentials for JNE is not set'
            ];
        }

        /**
         * OLSHOP_SHIPPER_ADDR (support 3 params with limitation 30 chars for each param)
         */
        $OLSHOP_SHIPPER_ADDR1 = '';
        $OLSHOP_SHIPPER_ADDR2 = '';
        $OLSHOP_SHIPPER_ADDR3 = '';
        $province = $shipper->province;
        $address = $shipper->address;
        $address_chars = strlen($address);
        if ($address_chars < 30) {
            // masukkan alamat apa adanya
            $OLSHOP_SHIPPER_ADDR1 = $address;
            // ditambah nama provinsi agar param OLSHOP_SHIPPER_ADDR2 bisa diisi karena required
            $OLSHOP_SHIPPER_ADDR2 = $province;
        } elseif ($address_chars > 30) {
            // ambil 30 char pertama dari alamat
            $OLSHOP_SHIPPER_ADDR1 = substr($address, 0, 30);
            // jika char alamat lebih dari 60, maka hrs pakai param OLSHOP_SHIPPER_ADDR3
            if ($address_chars > 60) {
                // ambil sisa char dari alamat yg sudah diambil 30 char utk OLSHOP_SHIPPER_ADDR1
                $OLSHOP_SHIPPER_ADDR2 = substr($address, 30, 30);
                // ambil sisa char dari alamat yg sudah diambil 30 char utk OLSHOP_SHIPPER_ADDR2
                // jika char alamat lbh dari 90, maka akan dipotong dan hanya diambil 90 char saja
                $OLSHOP_SHIPPER_ADDR3 = substr($address, 60, 30);
            } else {
                // ambil sisa char yg ada dari alamat yg sudah diambil 30 char
                $OLSHOP_SHIPPER_ADDR2 = substr($address, 30);
            }
        }

        /**
         * OLSHOP_RECEIVER_ADDR (support 3 params with limitation 30 chars for each param)
         */
        $OLSHOP_RECEIVER_ADDR1 = '';
        $OLSHOP_RECEIVER_ADDR2 = '';
        $OLSHOP_RECEIVER_ADDR3 = '';
        $province = $customer->province;
        $address = $customer->address;
        $address_chars = strlen($address);
        if ($address_chars < 30) {
            // masukkan alamat apa adanya
            $OLSHOP_RECEIVER_ADDR1 = $address;
            // ditambah nama provinsi agar param OLSHOP_RECEIVER_ADDR2 bisa diisi karena required
            $OLSHOP_RECEIVER_ADDR2 = $province;
        } elseif ($address_chars > 30) {
            // ambil 30 char pertama dari alamat
            $OLSHOP_RECEIVER_ADDR1 = substr($address, 0, 30);
            // jika char alamat lebih dari 60, maka hrs pakai param OLSHOP_RECEIVER_ADDR3
            if ($address_chars > 60) {
                // ambil sisa char dari alamat yg sudah diambil 30 char utk OLSHOP_RECEIVER_ADDR1
                $OLSHOP_RECEIVER_ADDR2 = substr($address, 30, 30);
                // ambil sisa char dari alamat yg sudah diambil 30 char utk OLSHOP_RECEIVER_ADDR2
                // jika char alamat lbh dari 90, maka akan dipotong dan hanya diambil 90 char saja
                $OLSHOP_RECEIVER_ADDR3 = substr($address, 60, 30);
            } else {
                // ambil sisa char yg ada dari alamat yg sudah diambil 30 char
                $OLSHOP_RECEIVER_ADDR2 = substr($address, 30);
            }
        }

        $params = [
            'username' => $username,
            'api_key' => $api_key,
            'OLSHOP_BRANCH' => $shipper->jne_branch,
            'OLSHOP_CUST' => env('JNE_ACCOUNT_NO'),
            'OLSHOP_ORDERID' => $order_id,

            'OLSHOP_SHIPPER_NAME' => $shipper->name,
            'OLSHOP_SHIPPER_ADDR1' => $OLSHOP_SHIPPER_ADDR1,
            'OLSHOP_SHIPPER_ADDR2' => $OLSHOP_SHIPPER_ADDR2,
            'OLSHOP_SHIPPER_ADDR3' => $OLSHOP_SHIPPER_ADDR3,
            'OLSHOP_SHIPPER_CITY' => $shipper->city,
            'OLSHOP_SHIPPER_ZIP' => $shipper->postcode,
            'OLSHOP_SHIPPER_PHONE' => $shipper->phone,

            'OLSHOP_RECEIVER_NAME' => $customer->name,
            'OLSHOP_RECEIVER_ADDR1' => $OLSHOP_RECEIVER_ADDR1,
            'OLSHOP_RECEIVER_ADDR2' => $OLSHOP_RECEIVER_ADDR2,
            'OLSHOP_RECEIVER_ADDR3' => $OLSHOP_RECEIVER_ADDR3,
            'OLSHOP_RECEIVER_CITY' => $customer->city,
            'OLSHOP_RECEIVER_ZIP' => $customer->postcode,
            'OLSHOP_RECEIVER_PHONE' => $customer->phone,

            'OLSHOP_QTY' => $product->qty,
            'OLSHOP_WEIGHT' => $product->weight,
            'OLSHOP_GOODSDESC' => $product->description,
            'OLSHOP_GOODSVALUE' => $product->price,

            'OLSHOP_GOODSTYPE' => 2,
            'OLSHOP_INS_FLAG' => $options->use_insurance,

            'OLSHOP_ORIG' => $origin,
            'OLSHOP_DEST' => $destination,
            'OLSHOP_SERVICE' => $options->service_type,
            'OLSHOP_COD_FLAG' => 'N',
            'OLSHOP_COD_AMOUNT' => 0,
        ];

        try {
            $client = new Client();
            $result = $client->request('POST', $endpoint, [
                'form_params' => $params
            ]);

            $response = json_decode($result->getBody()->getContents());

            // logging
            DB::table('courier_jne_logs')->insert(
                [
                    'method' => 'POST',
                    'endpoint' => $endpoint,
                    'headers' => null,
                    'params_type' => 'form_params',
                    'params' => json_encode($params),
                    'response' => json_encode($response),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );

            return [
                'status' => true,
                'data' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = json_decode($response->getBody()->getContents());
            # ERROR
            return [
                'status' => false,
                'info' => $responseBodyAsString
            ];
        }

        # INTERNAL ERROR
        return [
            'status' => false,
            'info' => 'Internal Server Error in JNE Library'
        ];
    }
}
