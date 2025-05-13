@php
	// Libraries
	use App\Libraries\Helper;

	// SET BULAN
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
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
			@php
				$expired_date = explode('-', $data['expired_date']);
				$tgl_indo = $expired_date[2] . ' ' . $bulan[ (int)$expired_date[1] ] . ' ' . $expired_date[0];
			@endphp
			<tr>
				<td colspan="2" style="color:#3A3A3A;font-family: Arial, Helvetica, sans-serif;padding:0 40px 20px 40px;text-align: center;border-top: 1px solid #959595;">
					<p style="font-size: 14px;padding:20px 0 0;color:#3A3A3A;">Halo <strong>{!! $data['fullname'] !!},</strong> <br><br>
						Pesanan kamu sudah dikonfirmasi.<br>Lakukan pembayaran paling lambat <strong>{!! $tgl_indo !!}</strong>, pukul <strong>{!! $data['expired_time'] !!} WIB</strong> </p>
					<p style="font-size: 14px;margin:0;">Terima kasih, ya! <br><br><strong>Berikut detail pembayaranmu:</strong></p>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding:20px;">
					<table style="width: 400px;background:#F0F0F0;border-radius: 10px;padding:20px;margin:0 auto 20px;">
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;">Total bayar:</td>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong style="color:#D70002;">{!! $data['total_price'] !!}</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;">Tanggal batas bayar:</td>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong>{!! $tgl_indo !!}</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;">Waktu batas bayar:</td>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong>{!! $data['expired_time'] !!} WIB</strong></td>
						</tr>
					</table>
					<table style="width: 400px;margin:0 auto 20px;">
                        <tr>
							<td colspan="2"><a href="{!! $data['link'] !!}" style="width:160px;color:#fff;font-size:14px;font-weight:bold;border:1px solid #0C6663;background:#0C6663;text-align: center;line-height: 36px;margin:0 auto;display: block;text-decoration: none;border-radius: 5px;">Bayar Sekarang</a></td>
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
					Indonesia
				</td>
			</tr>
		</tfoot>
	</table>
</body>
</html>