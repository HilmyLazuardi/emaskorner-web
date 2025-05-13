<!DOCTYPE html>
<html>
<head>
	<title>[EmasKorner] {!! $data['title'] !!}</title>
</head>
<body style="padding-top:40px;margin:0 auto;background: #0C6663;">
	<table style="width: 100%;max-width: 600px;margin:40px auto 0;font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;">
		<thead>
			<tr>
				<th style="padding: 0px 0;text-align: left;vertical-align: bottom;padding-bottom: 15px;"><img style="display: block;margin:0 auto;" src="{{ asset('web/images/logo.png') }}"></th>
			</tr>
		</thead>
		<tbody style="background: #D3A381;border-radius: 10px;">
			<tr>
				<td style="color:#3A3A3A;background:#D3A381;font-family: Arial, Helvetica, sans-serif;padding:20px 40px 20px 40px;text-align: center;border-top: 1px solid #959595;">
					<h2 style="color:#3A3A3A;font-size: 16px;">{!! $data['title'] !!}</h2>
				</td>
			</tr>
			<tr>
				<td style="padding:0 40px 20px 40px;color:#3A3A3A !important;text-align: center;">
					<p style="margin:0 0 20px;">{!! $data['content'] !!}</p>
					<a href="{!! route('web.home') !!}" style="background: #0C6663;color:#FFF;padding:10px 20px;text-decoration: none;min-width: 200px;display: inline-block;">Belanja Sekarang</a>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td style="text-align: center;color:#FFF;padding:20px 20px 20px;border-top: 1px solid #959595;font-size: 14px;">
					<strong>Butuh bantuan?</strong> Kami bisa dihubungi lewat WhatsApp di <a href="{!! $data['wa_link'] !!}" style="color: #FFF;">{!! $data['wa_number'] !!}</a> <br>atau email ke <a href="mailto:help@emaskorner.com" style="color:#FFF;">help@emaskorner.com</a>
				</td>
			</tr>
			<tr>
				<td style="color:#FFF;text-align: center;font-size: 14px;padding:0 0 20px;">
					EmasKorner<br>
					Jati Sampurna, Kota Bekasi, Jawa Barat<br>
					Indonesia
				</td>
			</tr>
		</tfoot>
	</table>
</body>
</html>