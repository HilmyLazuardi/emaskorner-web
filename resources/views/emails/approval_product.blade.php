<!DOCTYPE html>
<html>
<head>
	<title>[EmasKorner]</title>
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
					<p style="padding:20px 0 0">Halo <strong>{!! $data['name_store']  !!}</strong>,<br></p>
					<p style="color:#3A3A3A;margin:0;">Selamat! Produk kamu sudah disetujui! Produk akan live pada tanggal <strong>{!! $data['campaign_start']  !!}</strong> di website EmasKorner.</p>
					<p>Kami akan selalu mendukung <i>creative entrepreneur</i> seperti kamu yang <strong>#KayaIdeKreatif</strong> untuk terus berinovasi. Ditunggu project-project selanjutnya ya!</p>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding:20px;">
					<table style="width: 400px;background:#F0F0F0;border-radius: 10px;padding:20px;margin:0 auto 20px;">
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong>Detail Produk</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;">Nama Produk</td>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong>{!! $data['produk'] !!}</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;">Harga</td>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong>Rp{!! number_format($data['price'],0,',','.') !!}</strong></td>
						</tr>
						<tr>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;">Campaign produk berakhir pada</td>
							<td style="padding-bottom:10px;color:#303030;font-size:14px;text-align: left;"><strong>{!! $data['campaign_end']  !!}</strong></td>
						</tr>
					</table>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2" style="text-align: center;color:#FFF;padding:20px 20px 20px;border-top: 1px solid #959595;font-size: 14px;"><i>Jangan menginformasikan bukti dan data pembayaran kepada pihak manapun kecuali EmasKorner</i></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center;color:#FFF;padding:0 20px 20px;font-size: 14px;">
					Butuh bantuan? Hubungi tim EmasKorner di <a href="mailto:help@emaskorner.com" style="color:#FFF;">help@emaskorner.com</a>
					atau kunjungi Pusat Bantuan kami.
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