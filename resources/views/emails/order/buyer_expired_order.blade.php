@php
    use App\Libraries\Helper;
    use App\Libraries\HelperWeb;

    $company_info = HelperWeb::get_company_info();
@endphp

<!DOCTYPE html>
<html>
<head>
	<title>[EmasKorner] {!! $data['title'] !!}</title>
</head>
<body style="padding-top:40px;margin:0 auto;background: #0C6663;">
	<table style="width: 100%;max-width: 600px;margin:40px auto 0;font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;">
		<thead>
			<tr>
				<th style="padding: 0px 0;text-align: left;vertical-align: bottom;padding-bottom: 15px;"><img style="margin:0 auto;display: block;" src="{{ asset('web/images/logo.png') }}"></th>
			</tr>
		</thead>
		<tbody style="background: #D3A381;">
			<tr>
				<td colspan="2" style="color:#3A3A3A;font-family: Arial, Helvetica, sans-serif;padding:0 40px 20px 40px;text-align: center;border-top: 1px solid #959595;">
					<p style="padding:20px 0 0;color:#3A3A3A;">Halo <strong>{!! $data['user_name'] !!}</strong>, <br><br>
						Yah, waktu pembayaran kamu habis. Untuk cek detail dan status pemesanan, klik tombol Riwayat Pesanan. Masih mau pre order produk ini? Klik tombol "Belanja Lagi" dan segera selesaikan pembayaran kamu.</p>
					<p style="padding:0;color:#3A3A3A;margin-top:0;">Merasa sudah bayar? Email ke <a href="mailto:help@emaskorner.com" style="color:#3A3A3A;">help@emaskorner.com</a> dan tim kami akan siap membantu.<br><br>Terima kasih!</p>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding:20px;">
					<table style="width: 400px;margin:0 auto 20px;">
						<tr>
							<td><a href="{{ route('web.order.history') }}" style="width:180px;color:#303030;font-size:14px;font-weight:bold;border:1px solid #303030;text-align: center;line-height: 36px;display: block;text-decoration: none;">Riwayat Pesanan</a></td>
							<td><a href="{{ route('web.home') }}" style="float:right;width:160px;color:#fff;font-size:14px;font-weight:bold;border:1px solid #0C6663;background:#0C6663;text-align: center;line-height: 36px;display: block;text-decoration: none;">Belanja Lagi</a>
							</td>
						</tr>
					</table>

					<table style="width: 400px;margin:0 auto;border-collapse: collapse;">
						<tr>
							<td colspan="2" style="padding-bottom: 5px;color:#0C6663;"><strong>Detail Pesanan</strong></td>
						</tr>

                        @foreach ($data['orders'] as $seller_id => $order_details)
                            <tr>
                                <td colspan="2" style="font-size: 14px;color:#303030;padding-top: 10px;">Order ID: <span style="color:#0C6663;">{{ $order_details[0]->transaction_id }}</span></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size: 14px;color:#303030;">Toko: <span style="color:#0C6663;font-weight: bold;">{{ $order_details[0]->store_name }}</span></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size: 14px;color:#303030;">Estimasi Tiba: <span style="color:#3A3A3A;font-weight: bold;">{{ Helper::convert_date_to_indonesian(Helper::convert_timestamp($order_details[0]->estimate_arrived_at, 'Y-m-d', env('APP_TIMEZONE', 'UTC'))) }}</span></td>
                            </tr>
                            
                            @foreach ($order_details as $order_item)
                                {{-- product list --}}
                                @php
                                    $product_image = asset($order_item->product_image);
                                    if (!empty($order_item->product_variant_image)) {
                                        $product_image = asset($order_item->product_variant_image);
                                    }
                                @endphp
                                <tr>
                                    <td style="border-bottom:1px dashed #303030;vertical-align: top;padding-top: 5px;"><img style="width: 80px;" src="{{ $product_image }}"></td>
                                    <td style="padding-right: 10px;width: 300px;border-bottom:1px dashed #303030;padding-bottom: 10px;vertical-align: top;">
                                        <span style="color:#303030;font-size: 14px;display: block;padding-top: 5px;"><strong>{{ $order_item->product_name }}</strong></span>
                                        <span style="color:#303030;font-size: 12px;display: block;padding-top: 5px;">Jumlah: {{ $order_item->qty }} Pcs</span>
                                        <span style="color:#303030;font-size: 12px;display: block;padding-top: 5px;">Berat: {{ $order_item->weight / 1000 }} kg</span>
                                        <span style="color:#303030;font-size: 12px;display: block;padding-top: 5px;">Varian: {{ $order_item->product_variant_name }}</span>
                                    </td>
                                </tr>
                            @endforeach

                            <tr>
                                <td colspan="2" style="padding-bottom: 5px;padding-top: 5px;color:#0C6663;"><strong>Alamat Pengiriman</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size: 14px;color:#303030;padding-bottom: 15px;border-bottom:1px solid #303030;">
                                    <span style="margin-bottom: 5px;display: block;">{{ $order_details[0]->receiver_name }}</span>
                                    {{ $order_details[0]->receiver_address }}
                                    <br>
                                    {{ $order_details[0]->receiver_village_name }}, {{ $order_details[0]->receiver_sub_district_name }}, {{ $order_details[0]->receiver_city_name }}, {{ $order_details[0]->receiver_province_name }} {{ $order_details[0]->receiver_postal_code }}
                                    <br>
                                    {{ $order_details[0]->receiver_phone }}
                                </td>
                            </tr>
                        @endforeach
					</table>

					<table style="width: 400px;margin:0 auto 20px;">
						<tr>
							<td colspan="2" style="padding-bottom: 10px;color:#0C6663;padding-top: 10px;"><strong>Detail Pembayaran</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Subtotal</td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">{!! Helper::currency_format($data['invoice']->subtotal, 0, ',', '.', 'Rp', null) !!}</td>
						</tr>
						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Ongkos kirim</td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">{!! Helper::currency_format($data['invoice']->shipping_fee, 0, ',', '.', 'Rp', null) !!}</td>
						</tr>
						
                        @if ($data['invoice']->shipping_insurance_fee > 0)
                            <tr>
                                <td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Asuransi</td>
                                <td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">{!! Helper::currency_format($data['invoice']->shipping_insurance_fee, 0, ',', '.', 'Rp', null) !!}</td>
                            </tr>
                        @endif

                        @if ($data['invoice']->discount_amount > 0)
                            <tr>
                                <td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Promo (<strong>{{ $data['invoice']->voucher_code }}</strong>)</td>
                                <td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">-{!! Helper::currency_format($data['invoice']->discount_amount, 0, ',', '.', 'Rp', null) !!}</td>
                            </tr>
                        @endif

						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;"><strong>Total</strong></td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;"><strong>{!! Helper::currency_format($data['invoice']->total_amount, 0, ',', '.', 'Rp', null) !!}</strong></td>
						</tr>
					</table>
				</td>
			</tr>
		</tbody>

		<tfoot>
			<tr>
				<td colspan="2" style="text-align: center;color:#FFF;padding:20px 20px 20px;border-top: 1px solid #959595;font-size: 14px;"><i>Produk masih dalam bentuk prototype. Dana kamu akan dikembalikan jika produk tidak selesai dalam batas waktu yang ditentukan.</i></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center;color:#FFF;padding:0 20px 20px;font-size: 14px;">
					<strong>Butuh bantuan?</strong> Kami bisa dihubungi lewat <strong>WhatsApp di {!! env('COUNTRY_CODE').$company_info->wa_phone !!}</strong> atau email ke <a href="mailto:help@emaskorner.com" style="color: #fff;">help@emaskorner.com</a>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="color:#FFF;text-align: center;font-size: 14px;padding:0 0 20px;">
					EmasKorner<br>
					Jati Sampurna, Kota Bekasi, Jawa Barat<br>
					Indonesia<br>
					Follow us on<br> <a href="https://www.instagram.com/emaskorner/" style="display: inline-block;margin-top:5px;"><img style="width: 30px;" src="{{ asset('web/images/instagram.png') }}"></a><br>
					<p style="font-size: 12px;">Copyright by EmasKorner</p>
				</td>
			</tr>
		</tfoot>

	</table>
</body>
</html>