@php
	use App\Libraries\Helper;
@endphp

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="target-densitydpi=device-dpi; width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
    <meta name="HandheldFriendly" content="true" />
	<title>LokalKorner</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('web/fonts/stylesheet.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery.fancybox.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/content_element.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/main.css') }}">
	<script type="text/javascript" src="{{ asset('web/js/jquery.js') }}"></script>
	<script type="text/javascript" src="{{ asset('web/js/moment.min.js') }}"></script>

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-VB946CBTSZ"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-VB946CBTSZ');
	</script>
</head>
<body>
	<div class="wrapper">
		<section id="full_height">
			<div class="pos_abs_cd">
				<img src="{{ asset('web/images/logo_full.png') }}">
				<div class="cd_box">
					<div class="countdown_box">
						<div id="countdown"></div>
						<span>Hari</span>
						<span>Jam</span>
						<span>Menit</span>
						<span>Detik</span>
					</div>
				</div>
			</div>
			<div class="follow_box">
				Follow Us On<br>
				<a href="https://www.instagram.com/lokalkorner/" target="_blank"><img src="{{ asset('web/images/icon_ig_big.png') }}"> <span>@LokalKorner</span></a>
			</div>
		</section>
	</div>
	<script>
		var d = new Date('Jan 22, 2022 08:00:00');
		// var d = new Date("{{ Helper::convert_timestamp(env('LAUNCHING_DATETIME'), 'Y-m-d H:i:s', env('APP_TIMEZONE')) }}");
		var eventTime= moment(d.getTime()).unix();
		var currentTime = moment(new Date().getTime()).unix();
		var diffTime = eventTime - currentTime;
		var duration = moment.duration(diffTime*1000, 'milliseconds');
		var interval = 1000;
		
		var x = setInterval(function(){
			duration = moment.duration(duration - interval, 'milliseconds');
			$('#countdown').html("<span>" + duration.days() + "</span>" + " : " + "<span>"+ duration.hours() + "</span>" + " : " + "<span>"+ duration.minutes() + "</span>" + " : " + "<span>"+ duration.seconds() + "</span>")
			if(duration < 0){
			    clearInterval(x);
			    $('#countdown').html("<span>" + 0 + "</span>" + " : " + "<span>"+ 0 + "</span>" + " : " + "<span>"+ 0 + "</span>" + " : " + "<span>"+ 0 + "</span>")
			    location.reload();
			}
		}, interval);

	// Set the date we're counting down to
	// var countDownDate = new Date("Dec 17, 2021 16:31:00").getTime();
	// var countDownDate = new Date("{{ Helper::convert_timestamp(env('LAUNCHING_DATETIME'), 'Y-m-d H:i:s', env('APP_TIMEZONE')) }}").getTime();

	// // Update the count down every 1 second
	// var x = setInterval(function() {

	//   // Get today's date and time
	//   var now = new Date().getTime();
	    
	//   // Find the distance between now and the count down date
	//   var distance = countDownDate - now;
	    
	//   // Time calculations for days, hours, minutes and seconds
	//   var days = Math.floor(distance / (1000 * 60 * 60 * 24));
	//   var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
	//   var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
	//   var seconds = Math.floor((distance % (1000 * 60)) / 1000);
	    
	//   document.getElementById("countdown").innerHTML = "<span>" + days + "</span>" + " : " + "<span>" + hours + "</span>" + " : " + "<span>" + minutes + "</span>" + " : " + "<span>" + seconds + "</span>";
	//   if (distance < 0) {
	// 	    clearInterval(x);
	// 	    document.getElementById("countdown").innerHTML = "<span>" + 0 + "</span>" + " : " + "<span>" + 0 + "</span>" + " : " + "<span>" + 0 + "</span>" + " : " + "<span>" + 0 + "</span>";
	// 	    location.reload();
	// 	}
	// }, 1000);
	</script>
</body>
</html>