@extends('_template_web.master')

@php
    $pagetitle = 'Register Seller';
@endphp

@section('title', $pagetitle)

@section('script-plugins')
	<script>
    // Selecting the iframe element
    // const x = document.getElementById("main");
	// const y = x.getElementsByTagName("p");
    var iframe = document.getElementsByTagName("iframe");
    
    // Adjusting the iframe height onload event
    iframe.onload = function(){
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
    }
    </script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')

    <section>
    	<div style="max-width: 700px;margin:0 auto;display: block;">
	        <script>(function(t,e,s,n){var o,a,c;t.SMCX=t.SMCX||[],e.getElementById(n)||(o=e.getElementsByTagName(s),a=o[o.length-1],c=e.createElement(s),c.type="text/javascript",c.async=!0,c.id=n,c.src="https://widget.surveymonkey.com/collect/website/js/tRaiETqnLgj758hTBazgdyOg6A4Esw7KS0ddeN_2F9lOYl7r8803x1zpIVKcBXPC_2FR.js",a.parentNode.insertBefore(c,a))})(window,document,"script","smcx-sdk");</script>
	    </div>
    </section>

    @include('_template_web.footer')
@endsection