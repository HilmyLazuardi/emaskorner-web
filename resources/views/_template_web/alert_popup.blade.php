@if (count($errors) > 0)
    <div class="popup_failed">
        <div class="overlay"></div>
        <div class="popup_box">
            <div class="icon_box"><img src="{{ asset('web/images/success.png') }}"></div>
            <h3>Error!</h3>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
            <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="popup_failed">
        <div class="overlay"></div>
        <div class="popup_box">
            <div class="icon_box"><img src="{{ asset('web/images/icon_close_white.png') }}"></div>
            <h3>Error!</h3>
            <p>{{ session('error') }}</p>
            <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
        </div>
    </div>
@endif

@if (session('error_variant'))
    <div class="popup_failed">
        <div class="overlay"></div>
        <div class="popup_box">
            <div class="icon_box"><img src="{{ asset('web/images/icon_close_white.png') }}"></div>
            <h3>Whoops!</h3>
            <p>{{ session('error_variant') }}</p>
            <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
        </div>
    </div>
@endif

@if (session('success'))
    <div class="popup_success">
        <div class="overlay"></div>
        <div class="popup_box">
            <div class="icon_box"><img src="{{ asset('web/images/success.png') }}"></div>
            @php
                $header = 'Sukses!';
                if (Session::has('header')) {
                    $header = Session::get('header');
                }
            @endphp
            <h3>{!! $header !!}</h3>
            <p>{!! session('success') !!}</p>
            <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
        </div>
    </div>
@endif

@if (session('success_address'))
    <div class="popup_success">
        <div class="overlay"></div>
        <div class="popup_box">
            <div class="icon_box"><img src="{{ asset('web/images/success.png') }}"></div>
            @php
                $header = 'Hore!';
                if (Session::has('header')) {
                    $header = Session::get('header');
                }
            @endphp
            <h3>{!! $header !!}</h3>
            <p>{!! session('success_address') !!}</p>
            <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
        </div>
    </div>
@endif

@if (session('error_profile'))
    <div class="popup_failed">
        <div class="overlay"></div>
        <div class="popup_box">
            <div class="icon_box"><img src="{{ asset('web/images/icon_close_white.png') }}"></div>
            <h3>Oops!</h3>
            <p>{{ session('error_profile') }}</p>
            <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
        </div>
    </div>
@endif