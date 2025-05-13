@extends('_template_web.master')

@php
    use App\Libraries\Helper;

    $content = json_decode($data->content);
    $pagetitle = $data->title.' - LokalKorner';

    // SET BULAN
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
@endphp

@section('title', $pagetitle)

@section('open_graph')
@endsection

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/fonts/stylesheet.css') }}">
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

@section('header-script')
@endsection

@section('content')
    @include('_template_web.header_with_categories')

    <section>
        <div class="section_content_element green_bg">
            <div class="container">
                <div class="ce_text width100">
                    <div class="ce_top">
                        @php
                            $posted_at_detail = explode('-', $data->posted_at);
                            $posted_tgl_detail = explode(' ', $posted_at_detail[2]);
                            $news_posted_at_detail = $posted_tgl_detail[0] . ' ' . $bulan[ (int)$posted_at_detail[1] ] . ' ' . $posted_at_detail[0];
                        @endphp
                        <h3>{{ $data->title }}</h3>
                        <span>{{ $data->author }} - {{ $data->location }}</span>
                        {!! $news_posted_at_detail  !!}
                    </div>
                    <div class="ce_mid">
                        {{-- <img src="{{ asset('web/images/news2.png') }}">
                        <p>Badan Ekonomi Kreatif (Bekraf) menandatangani nota kesepahaman (MoU) dengan Kamar Dagang dan Industri (Kadin) Indonesia guna mendukung perkembangan ekonomi kreatif berbasis digital.</p>
                        <p>Penandatangan dilakukan oleh Kepala Bekraf, Triawan Munaf dengan Ketua Umum Kadin Rosan Roeslani, dikantor Bekraf disaksikan beberapa perwakilan dari perusahaan nasional berbasis digital diantaranya Go-Jek, Tokopedia, Traveloka, Bukalapak, Ruang Guru, Blibli dan Mataharimall.com.</p>
                        <p>"Kami di Bekraf menyadari bahwa industri ekonomi kreatif yang bergerak di infrastruktur digital berbasis elektronik atau startup nasional terus berkembang dengan pesat, menawarkan berbagai solusi yang dapat membantu masyarakat Indonesia," kata Triawan dalam sambutanya di Jakarta, Senin (24/7/2017).</p>
                        <p>Ia berharap, melalui Karya Merah Putih, startup nasional dapat lebih maju dan tumbuh dengan signifikan, sehingga bisa mewujudkan visi Indonesia menjadi The Digital Of Asia.</p> --}}

                        @if (!empty($content))
                            {{-- SECTIONS --}}
                            @foreach ($content as $item)
                                @if ($item->type == 'text')
                                    @php
                                        $text_value = $item->text;
                                    @endphp
                                    <p>@php echo $text_value; @endphp</p>
                                @elseif ($item->type == 'image')
                                    @php
                                        $image_value = asset('uploads/news/'.$item->image);
                                    @endphp
                                    <img src="{{ $image_value }}">
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="section_latest_news">
            <div class="container">
                <h2>Info Terkini</h2>
                <div class="slider_news mr20">
                    @if(!empty($news[0]))
                        @foreach($news as $item)
                            @php
                                $posted_at = explode('-', $item->posted_at);
                                $posted_tgl = explode(' ', $posted_at[2]);
                                $news_posted_at = $posted_tgl[0] . ' ' . $bulan[ (int)$posted_at[1] ] . ' ' . $posted_at[0];
                            @endphp
                            <div class="nl_box">
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
                        <div class="nl_img"><a href="#"><img src="{{ asset('web/images/news2.png') }}"></a></div>
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
                        <div class="nl_img"><a href="#"><img src="{{ asset('web/images/news3.png') }}"></a></div>
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
                        <div class="nl_img"><a href="#"><img src="{{ asset('web/images/news.png') }}"></a></div>
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
                        <div class="nl_img"><a href="#"><img src="{{ asset('web/images/news2.png') }}"></a></div>
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
                        <div class="nl_img"><a href="#"><img src="{{ asset('web/images/news3.png') }}"></a></div>
                        <div class="nl_desc">
                            <h3><a href="#">Inkubasi Wirausaha</a></h3>
                            <div class="writer_info">
                                Nur Amaliyah - Lamongan<br>
                                12 November 2021
                            </div>
                            <a href="#" class="read_more">Baca Lebih Banyak</a>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </section>

    @include('_template_web.footer')
@endsection

@section('footer-script')
@endsection