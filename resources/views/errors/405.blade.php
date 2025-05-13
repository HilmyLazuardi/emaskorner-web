@php
    use App\Libraries\Helper;

    $global_config = \App\Models\config::first();
    $homepage = url('/');

    $bg_img = 'images/background.jpeg';
    if ($global_config->bg_images) {
        $bg_images_existing = json_decode($global_config->bg_images, true);
        if (isset($bg_images_existing['405'])) {
            $bg_img = $bg_images_existing['405'];
        }
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>405 ({{ ucwords(lang("method not allowed")) }}) | {!! $global_config->meta_title !!}</title>
    <meta name="keywords" content="{!! str_replace(',', ', ', $global_config->meta_keywords) !!}">
    <meta name="description" content="{{ $global_config->meta_description }}" />

    <link rel="icon" href="{{ asset($global_config->app_favicon) }}" />

    @if (isset($global_config->og_type))
        <meta property="og:type" content="{!! $global_config->og_type !!}" />
        <meta property="og:site_name" content="{!! $global_config->og_site_name !!}" />
        <meta property="og:title" content="419 ({{ ucwords(lang("method not allowed")) }}) | {!! $global_config->og_title !!}" />
        <meta property="og:image" content="{{ asset($global_config->og_image) }}" />
        <meta property="og:description" content="{!! $global_config->og_description !!}" />
        <meta property="og:url" content="{{ Helper::get_url() }}" />

        @if ($global_config->fb_app_id)
            <meta property="fb:app_id" content="{!! $global_config->fb_app_id !!}" />
        @endif

        <meta property="twitter:card" content="{!! $global_config->twitter_card !!}" />
        @if ($global_config->twitter_site)
            <meta property="twitter:site" content="{!! $global_config->twitter_site !!}" />
        @endif
        @if ($global_config->twitter_site_id)
            <meta property="twitter:site:id" content="{!! $global_config->twitter_site_id !!}" />
        @endif
        @if ($global_config->twitter_creator)
            <meta property="twitter:creator" content="{!! $global_config->twitter_creator !!}" />
        @endif
        @if ($global_config->twitter_creator_id)
            <meta property="twitter:creator:id" content="{!! $global_config->twitter_creator_id !!}" />
        @endif
    @endif

    <!-- Latest compiled and minified CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">

    <!-- Latest compiled and minified JavaScript -->
    {{-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script> --}}

    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #000;
        }

        .bg-img {
            position: absolute;
            width: 100%;
            height: 100%;
            background: url({{ asset($bg_img) }}) no-repeat center center fixed;
            background-size: cover;
            background-color: #000;
            opacity: .5;
            filter: alpha(opacity=50);
        }

        .content {
            font-family: 'Avenir-Next',Avenir,Helvetica,sans-serif;
            color: #fff;
            background-color: none;
            z-index: 2;
            position: absolute;
            top: 50%;
            -webkit-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            transform: translateY(-50%);
        }

        h1 {
            font-size: 160px;
            margin-bottom: 0;
            margin-top: 0;
        }

        h2 {
            margin-top: 0;
            max-width: 700px;
            font-size: 30px;
            width: 90%;
        }

        p {
            text-align: left;
            padding-bottom: 32px;
        }

        .btn {
            display: inline-block;
            border: 1px solid #aaa;
            border-radius: 40px;
            padding: 15px 30px;
            margin-right: 15px;
            margin-bottom: 10px;
            color: white;
        }
        .btn:hover {
            color: #e2e2e2;
            background: rgba(255, 255, 255, 0.1);
        }

        @media only screen and (max-width: 480px) {
            .btn {
                background-color: white;
                color: #444444;
                width: 100%;
            }

            h1 {
                font-size: 120px;
            }
        }
    </style>

    @php
        $preview_class = '';
    @endphp
    @if (isset($preview))
        @php
            $preview_class = 'container_preview';
        @endphp
        @include('_vendors.simple_preview.css')
    @endif
</head>
<body class="{{ $preview_class }}">
    @if (isset($preview))
        @include('_vendors.simple_preview.html')
    @endif
    <div class='container'>
      <div class='row content'>
        <div class='col-lg-12'></div>
        <div class='col-lg-12'>
          <h1>405</h1>
          <h2>{{ ucwords(lang("method not allowed")) }}</h2>
          <p>{{ lang("Something is broken. Please let us know what you were doing when this error occurred. We will fix it as soon as possible. Sorry for any inconvenience caused.") }}</p>
          <p>{{ lang("You may want to head back to the homepage.") }}</p>
          <a href="{{ $homepage }}" class='btn'>{{ strtoupper(lang("return home")) }}</a>
        </div>
      </div>
    </div>
    <div class='bg-img'></div>
  </body>  
</html>