@extends('_template_web.master')

@section('title', 'FAQ')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/content_element.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/jquery.fancybox.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    <section class="white_bg">
        <div class="section_faq">
            <div class="container">
                @if (isset($data[0]))
                    @foreach ($data as $item)
                        <div class="faq_wrapper">
                            {{-- LEVEL 1 --}}
                            <h2>{!! $item->text_1 !!}</h2>
                            
                            {{-- LEVEL 2 --}}
                            @if (isset($item->level_2))
                                @foreach ($item->level_2 as $level_2)
                                    <div class="row_clear">
                                        <h3>{!! $level_2->text_1 !!}</h3>

                                        {{-- LEVEL 3 --}}
                                        @if (isset($level_2->level_3))
                                            @foreach ($level_2->level_3 as $level_3)
                                                <div class="faq_box">
                                                    <div class="faq_top">{!! $level_3->text_1 !!}</div>
                                                    <div class="faq_bottom">{!! nl2br($level_3->text_2) !!}</div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                @else

                @endif
            </div>
        </div>
    </section>
    @include('_template_web.footer')
@endsection