@extends('_template_web.master')

@php
    use App\Libraries\Helper;
    $pagetitle = 'Berita Terkini';
    // SET BULAN
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
@endphp

@section('title', $pagetitle)

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/fonts/stylesheet.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/content_element.css') }}">
@endsection

@section('script-plugins')
    <script>
        $(document).ready(function() {
            $('.show-more').on('click', function() {
                var id = $(this).data('id');
                var totalCurrentResult = $('.news-box').length;

                $.ajax({
                    url:"{{ route('web.news.get_data') }}",
                    type: "GET",
                    data: {
                        skip:totalCurrentResult
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $('.show-more').html('Loading....');
                    },
                })
                .done(function(response) {
                    if (response != '') {
                        $('.news-list').append(response[0].html);
                    } else {
                        $('.show-more').html('No Data');
                    }

                    var totalCurrentResult = $(".news-box").length;
                    var totalResult = parseInt($(".show-more").attr('data-totalResult'));
                    if (totalCurrentResult == totalResult) {
                        $(".show-more").remove();
                    } else {
                        $(".show-more").html("Lihat Semua Artikel");
                    }
                })
                .fail(function() {

                })
                .always(function() {

                });
            });
        });
    </script>

    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/jquery.fancybox.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')

    <section>
        <div class="seciton_news">
            <div class="container">
                <h3 class="title">Berita Terkini</h3>
                <div class="row_flex">
                    @if(!empty($news[0]))
                        @foreach($news as $item)
                            @php
                                $posted_at = explode('-', $item->posted_at);
                                $posted_tgl = explode(' ', $posted_at[2]);
                                $news_posted_at = $posted_tgl[0] . ' ' . $bulan[ (int)$posted_at[1] ] . ' ' . $posted_at[0];
                            @endphp
                            <div class="nl_box news-box">
                                <div class="nl_img"><a href="{{ route('web.news.detail', $item->slug) }}"><img src="{{ asset($item->dir_path.$item->thumbnail) }}"></a></div>
                                <div class="nl_desc">
                                    <h3><a href="{{ route('web.news.detail', $item->slug) }}">{{ $item->title }}</a></h3>
                                    {{-- <div class="writer_info">
                                        {{ $item->author }} - {{ $item->location }}<br>
                                        {!! $news_posted_at !!}
                                    </div> --}}
                                    <a href="{{ route('web.news.detail', $item->slug) }}" class="read_more">Baca Lebih Banyak</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    {{-- <div class="nl_box">
                        <div class="nl_img"><a href="#"><img src="images/news2.png"></a></div>
                        <div class="nl_desc">
                            <h3><a href="#">Pentingnya Branding</a></h3>
                            <div class="writer_info">
                                Nur Amaliyah - Lamongan<br>
                                12 November 2021
                            </div>
                            <a href="#" class="read_more">Baca Lebih Banyak</a>
                        </div>
                    </div>
                    <div class="nl_box">
                        <div class="nl_img"><a href="#"><img src="images/news3.png"></a></div>
                        <div class="nl_desc">
                            <h3><a href="#">Inkubasi Wirausaha</a></h3>
                            <div class="writer_info">
                                Nur Amaliyah - Lamongan<br>
                                12 November 2021
                            </div>
                            <a href="#" class="read_more">Baca Lebih Banyak</a>
                        </div>
                    </div>
                    <div class="nl_box">
                        <div class="nl_img"><a href="#"><img src="images/news.png"></a></div>
                        <div class="nl_desc">
                            <h3><a href="#">Pameran DEKRANASDA Jatim</a></h3>
                            <div class="writer_info">
                                Nur Amaliyah - Lamongan<br>
                                12 November 2021
                            </div>
                            <a href="#" class="read_more">Baca Lebih Banyak</a>
                        </div>
                    </div>
                    <div class="nl_box">
                        <div class="nl_img"><a href="#"><img src="images/news2.png"></a></div>
                        <div class="nl_desc">
                            <h3><a href="#">Agenda Bekraf Tahunan</a></h3>
                            <div class="writer_info">
                                Nur Amaliyah - Lamongan<br>
                                12 November 2021
                            </div>
                            <a href="#" class="read_more">Baca Lebih Banyak</a>
                        </div>
                    </div>
                    <div class="nl_box">
                        <div class="nl_img"><a href="#"><img src="images/news3.png"></a></div>
                        <div class="nl_desc">
                            <h3><a href="#">Inkubasi Wirausaha</a></h3>
                            <div class="writer_info">
                                Nur Amaliyah - Lamongan<br>
                                12 November 2021
                            </div>
                            <a href="#" class="read_more">Baca Lebih Banyak</a>
                        </div>
                    </div> --}}
                    <div class="news-list"></div>
                </div>
                @if(count($news) > 0)
                    <div>
                        <a href="javascript:void(0)" class="see_btn show-more" data-id="{{ $item->id }}" data-totalResult="{{ App\Models\news::count() }}">Lihat Semua Artikel</a>
                    </div>
                @endif
                {{-- <a href="#" class="see_btn">Lihat Semua Artikel</a> --}}
            </div>
        </div>
    </section>

    @include('_template_web.footer')
@endsection