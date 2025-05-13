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
					<p style="padding:20px 0 0;color:#3A3A3A;">Halo <strong>{!! $data['fullname'] !!}</strong>, <br><br>
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
					<table style="width: 400px;margin:0 auto 20px;border-collapse: collapse;border-bottom:1px solid #303030;">
						<tr>
							<td colspan="2" style="padding-bottom: 5px;color:#0C6663;"><strong>Detail Pesanan</strong></td>
						</tr>
						<tr>
							<td colspan="2" style="font-size: 14px;color:#303030;">Order ID: <span style="color:#0C6663;">{!! $data['transaction_id'] !!}</span></td>
						</tr>
						<tr>
							<td style="padding-right: 10px;width: 300px;border-bottom:1px solid #303030;padding-bottom: 15px;">
								<span style="color:#303030;font-size: 14px;display: block;padding-top: 5px;"><strong>{!! $data['product_name'] !!}</strong></span>
								<span style="color:#303030;font-size: 14px;display: block;padding-top: 5px;">Jumlah: {!! $data['quantity'] !!} Pcs</span>
								<span style="color:#303030;font-size: 14px;display: block;padding-top: 5px;">Berat: {!! $data['weight'] !!} kg</span>
								<span style="color:#303030;font-size: 14px;display: block;padding-top: 5px;">Varian: {!! $data['variant_name'] !!}</span>
							</td>
							<td style="border-bottom:1px solid #303030;"><img style="width: 100px;" src="{!! asset($data['image']) !!}"></td>
						</tr>
						<tr>
							<td colspan="2" style="padding-bottom: 5px;padding-top: 15px;color:#0C6663;"><strong>Alamat Pengiriman</strong></td>
						</tr>
						<tr>
							<td colspan="2" style="font-size: 14px;color:#303030;padding-bottom: 15px;">
								<span style="margin-bottom: 5px;display: block;">{!! $data['buyer_name'] !!}</span>
								{!! $data['buyer_address'] !!}
							</td>
						</tr>
					</table>
					<table style="width: 400px;margin:0 auto 20px;">
						<tr>
							<td colspan="2" style="padding-bottom: 10px;color:#0C6663;"><strong>Detail Pembayaran</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Subtotal</td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">{!! $data['subtotal'] !!}</td>
						</tr>
						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Ongkos kirim</td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">{!! $data['shipping_fee'] !!}</td>
						</tr>
						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;">Asuransi</td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;">{!! $data['insurance_shipping_fee'] !!}</td>
						</tr>
						<tr>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: left;"><strong>Total</strong></td>
							<td style="padding-bottom:5px;font-size:14px;vertical-align:top;color:#303030;text-align: right;"><strong>{!! $data['total_price'] !!}</strong></td>
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
					<strong>Butuh bantuan?</strong> Kami bisa dihubungi lewat <strong>WhatsApp di {!! $data['wa_number'] !!}</strong> atau email ke <a href="mailto:help@emaskorner.com" style="color: #fff;">help@emaskorner.com</a>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="color:#FFF;text-align: center;font-size: 14px;padding:0 0 20px;">
					EmasKorner<br>
					Jati Sampurna, Kota Bekasi, Jawa Barat<br>
					Indonesia <br>
					Follow us on<br> <a href="https://www.instagram.com/emaskorner/" style="display: inline-block;margin-top:5px;"><img style="width: 30px;" src="{{ asset('web/images/instagram.png') }}"></a><br>
					<p style="font-size: 12px;">Copyright by EmasKorner</p>
				</td>
			</tr>
		</tfoot>
	</table>
</body>
</html>