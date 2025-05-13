@extends('_template_web.master')

@php
    use App\Libraries\Helper;

    $pagetitle = $data->title;

    $content = json_decode($data->content);
@endphp

@section('title', $pagetitle)

@section('open_graph')
    <meta name="description" content="{!! $data->meta_description !!}">
    <meta name="keywords" content="{!! str_replace(',', ', ', $data->meta_keywords) !!}">
    <meta name="author" content="{!! $data->meta_author !!}">

    <meta property="og:type" content="{!! $data->og_type !!}" />
    <meta property="og:site_name" content="{!! $data->og_site_name !!}" />
    <meta property="og:title" content="{!! $data->og_title !!}" />
    <meta property="og:image" content="{{ asset($data->og_image) }}" />
    <meta property="og:description" content="{!! $data->og_description !!}" />
    <meta property="og:url" content="{{ Helper::get_url() }}" />

    @if ($data->fb_app_id)
        <meta property="fb:app_id" content="{!! $data->fb_app_id !!}" />
    @endif

    <meta property="twitter:card" content="{!! $data->twitter_card !!}" />
    @if ($data->twitter_site)
        <meta property="twitter:site" content="{!! $data->twitter_site !!}" />
    @endif
    @if ($data->twitter_site_id)
        <meta property="twitter:site:id" content="{!! $data->twitter_site_id !!}" />
    @endif
    @if ($data->twitter_creator)
        <meta property="twitter:creator" content="{!! $data->twitter_creator !!}" />
    @endif
    @if ($data->twitter_creator_id)
        <meta property="twitter:creator:id" content="{!! $data->twitter_creator_id !!}" />
    @endif
@endsection

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

@section('header-script')
    {!! $data->header_script !!}
@endsection

@section('body-script')
    {!! $data->body_script !!}
@endsection

@section('content')
    @include('_template_web.header_with_categories')

    <section>
        {{-- SECTIONS --}}
        @foreach ($content as $key => $value)
            {{-- GENERATE SECTION HTML --}}
            @php
                // CHECK SECTION STYLE
                $section_style = strtolower(str_replace(' ', '_', $value->v_page_section_style));
            @endphp
            <div class="section_content_element {{ $section_style }}">
                {{-- CONTENT ELEMENTS --}}
                @foreach ($value->contents as $key2 => $value2)
                    {{-- ONLY DISPLAY ELEMENT WITH ACTIVE STATUS --}}
                    @if ($value2->v_page_element_status == 1)
                        {{-- PROCESSING PER ELEMENT TYPE --}}
                        @if ($value2->v_page_element_type == 'masthead')
                            {{-- MASTHEAD (SUPPORT MULTIPLE ITEM) --}}
                            {{-- ONLY SHOW IF IT HAS ITEM(S) --}}
                            @if (!empty($value2->items))
                                <div class="section_masthead">
                                    <div class="masthead_slider">
                                        @foreach ($value2->items as $key3 => $value3)
                                            {{-- ONLY DISPLAY ITEM WITH ACTIVE STATUS --}}
                                            @if ($value3->v_page_element_status_item == 1)
                                                <div class="mh_box">
                                                    <div class="mh_img">
                                                        @if (isset($value3->v_page_element_image_link_type) && $value3->v_page_element_image_link_type != 'no link')
                                                            @php
                                                                // External Link
                                                                $link = $value3->v_page_element_image_link_external;
                                                                if ($value3->v_page_element_image_link_type == 'internal') {
                                                                    $link = url('/').'/'.$value3->v_page_element_image_link_internal;
                                                                }

                                                                // Target "opened on the same page"
                                                                $target = '_self';
                                                                if ($value3->v_page_element_image_link_target == 'new page') {
                                                                    $target = '_blank';
                                                                }
                                                            @endphp
                                                            <a href="{{ $link }}" target="{{ $target }}">
                                                        @endif
                                                                <img class="desktop" alt="{{ $value3->v_page_element_image_title }}" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                                <img class="mobile" alt="{{ $value3->v_page_element_image_title }}" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image_mobile) }}">
                                                        @if (isset($value3->v_page_element_image_link_type) && $value3->v_page_element_image_link_type != 'no link')
                                                            </a>
                                                        @endif
                                                    </div>
                                                    {{-- ONLY SHOW IF THERE IS A VALUE IN "TITLE" --}}
                                                    @if (!empty($value3->v_page_element_title))
                                                        <div class="mh_text">
                                                            @php
                                                                // if Content Alignment is "left" then content area width becomes 50%
                                                                $width = 'width50';
                                                                $btn_align = '';
                                                                if ($value3->v_page_element_alignment == 'center') {
                                                                    $width = 'width100 content-center';
                                                                    $btn_align = 'align_center';
                                                                }

                                                                $title_style = 'white_color';
                                                                if(isset($value3->v_page_element_title_style)){
                                                                    switch ($value3->v_page_element_title_style) {
                                                                        case 'Black':
                                                                            $title_style = 'black_color';
                                                                            break;
                                                                        case 'White':
                                                                            $title_style = 'white_color';
                                                                            break;
                                                                        case 'Red':
                                                                            $title_style = 'red_color';
                                                                            break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="{{ $width }}">
                                                                <h3 class="{{ $title_style }}">{{ $value3->v_page_element_title }}</h3>
                                                                @if (!empty($value3->v_page_element_subtitle))
                                                                    @php
                                                                        $subtitle_style = 'white_color';
                                                                        if(isset($value3->v_page_element_subtitle_style)) {
                                                                            switch ($value3->v_page_element_subtitle_style) {
                                                                                case 'Black':
                                                                                    $subtitle_style = 'black_color';
                                                                                    break;
                                                                                case 'White':
                                                                                    $subtitle_style = 'white_color';
                                                                                    break;
                                                                                case 'Red':
                                                                                    $subtitle_style = 'red_color';
                                                                                    break;
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <p class="{{ $subtitle_style }}">{{ $value3->v_page_element_subtitle }}</p>
                                                                @endif
                                                                @if (isset($value3->sub_items) && !empty($value3->sub_items))
                                                                    @php
                                                                        $button = [];
                                                                    @endphp
                                                                    @foreach ($value3->sub_items as $key4 => $value4)
                                                                        @php
                                                                            // IF BUTTON - NO LINK THEN NOT DISPLAY ANYTHING
                                                                            if ($value4->v_page_element_button_link_type != 'no link') {
                                                                                // CHECK BUTTON STYLE
                                                                                $style = ''; // red
                                                                                if (strtolower($value4->v_page_element_button_style) == 'gray') {
                                                                                    $style = 'gray_btn';
                                                                                }

                                                                                // CHECK BUTTON LINK
                                                                                // External Link
                                                                                $link = $value4->v_page_element_button_link_external;
                                                                                if ($value4->v_page_element_button_link_type == 'internal') {
                                                                                    $link = url('/').'/'.$value4->v_page_element_button_link_internal;
                                                                                }

                                                                                // Target "opened on the same page"
                                                                                $target = '_self';
                                                                                if ($value4->v_page_element_button_link_target == 'new page') {
                                                                                    $target = '_blank';
                                                                                }

                                                                                $button[] = '<a href="'.$link.'" class="def_btn '.$style.'" target="'.$target.'">'.$value4->v_page_element_button_label.'</a>';
                                                                            }
                                                                        @endphp
                                                                    @endforeach

                                                                    @if (!empty($button))
                                                                        <div class="ce_button {{ $btn_align }}">
                                                                            <?php echo implode(' ', $button) ?>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <!-- /.masthead_slider -->
                                </div>
                                <!-- /.section_masthead -->
                            @endif
                        @elseif ($value2->v_page_element_type == 'text')
                            {{-- TEXT (SUPPORT 3 ITEMS) --}}
                            {{-- ONLY SHOW IF IT HAS ITEM(S) --}}
                            @if (!empty($value2->items))
                                @php
                                    // CHECK WIDTH
                                    $width = 'width100';
                                    if ($value2->v_page_element_width == '67') {
                                        $width = 'width67';
                                    } elseif ($value2->v_page_element_width == '50') {
                                        $width = 'width50';
                                    }

                                    // ALIGNMENT
                                    $align = '';
                                    if ($value2->v_page_element_alignment == 'center') {
                                        $align = 'container_center';
                                    }
                                @endphp
                                <div class="container">
                                    @if ($value2->v_page_element_total_item == 1)
                                        {{-- SINGLE COLUMN --}}
                                        <div class="ce_text {{ $width }} {{ $align }}"><?php echo $value2->items[0]; ?></div>
                                    @else
                                        {{-- 2 OR 3 COLUMN --}}
                                        <div class="ce_column">
                                            <div class="row_flex">
                                                @for ($i = 0; $i < $value2->v_page_element_total_item; $i++)
                                                <div class="flex_box">
                                                    <div class="content_box">
                                                        <div class="content">
                                                            <?php echo $value2->items[$i]; ?>
                                                        </div>
                                                        <!-- /.content -->
                                                    </div>
                                                    <!-- /.content_box -->
                                                </div>
                                                <!-- /.flex_box -->
                                                @endfor
                                            </div>
                                            <!-- /.row_flex -->
                                        </div>
                                        <!-- /.ce_column -->
                                    @endif
                                </div>
                                <!-- /.container -->
                            @endif
                        @elseif ($value2->v_page_element_type == 'image')
                            {{-- IMAGE (SUPPORT MULTIPLE ITEM) --}}
                            {{-- ONLY SHOW IF IT HAS ITEM(S) --}}
                            @if (!empty($value2->items))
                                <div class="container">
                                    {{-- CHECK IMAGE TYPE --}}
                                    @if ($value2->v_page_element_image_type == 'Carousel')
                                        <div class="carousel_image_slider">
                                            @foreach ($value2->items as $key3 => $value3)
                                                {{-- ONLY DISPLAY ITEM WITH ACTIVE STATUS --}}
                                                @if ($value3->v_page_element_status_item == 1)
                                                    <div class="mh_box">
                                                        <div class="mh_img">
                                                            @if ($value3->v_page_element_link_type == 'no link')
                                                                <a href="{{ asset($value3->v_page_element_image) }}" data-fancybox="gallery">
                                                            @else
                                                                @php
                                                                    // External Link
                                                                    $link = $value3->v_page_element_link_external;
                                                                    if ($value3->v_page_element_link_type == 'internal') {
                                                                        $link = url('/').'/'.$value3->v_page_element_link_internal;
                                                                    }

                                                                    // Target "opened on the same page"
                                                                    $target = '_self';
                                                                    if ($value3->v_page_element_link_target == 'new page') {
                                                                        $target = '_blank';
                                                                    }
                                                                @endphp
                                                                <a href="{{ $link }}" target="{{ $target }}">
                                                            @endif
                                                                <img class="desktop" alt="banner name" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                                <img class="mobile" alt="banner name" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <!-- /.carousel_image_slider -->
                                    @else
                                        <div class="image_slider">
                                            @foreach ($value2->items as $key3 => $value3)
                                                {{-- ONLY DISPLAY ITEM WITH ACTIVE STATUS --}}
                                                @if ($value3->v_page_element_status_item == 1)
                                                    @php
                                                        // CHECK LINK
                                                        $link = '#';
                                                        $target = '_self';
                                                        if ($value3->v_page_element_link_type != 'no link') {
                                                            // External Link
                                                            $link = $value3->v_page_element_link_external;
                                                            if ($value3->v_page_element_link_type == 'internal') {
                                                                $link = url('/').'/'.$value3->v_page_element_link_internal;
                                                            }

                                                            // Target "opened on the same page"
                                                            $target = '_self';
                                                            if ($value3->v_page_element_link_target == 'new page') {
                                                                $target = '_blank';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="mh_box">
                                                        <div class="mh_img">
                                                            <a href="{{ $link }}" target="{{ $target }}">
                                                                <img class="desktop" alt="{{ $value3->v_page_element_image_title }}" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                                <img class="mobile" alt="{{ $value3->v_page_element_image_title }}" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <!-- /.image_slider -->
                                    @endif
                                </div>
                                <!-- /.container -->
                            @endif
                        @elseif ($value2->v_page_element_type == 'image + text + button')
                            {{-- Image + Text + Button (Support 3 Items) --}}
                            <div class="container">
                                @if ($value2->v_page_element_total_item == 3)
                                    {{-- 3 COLUMNS - AUTOMATIC ALIGN CENTER --}}
                                    <div class="ce_column radius_on align_center">
                                        <div class="row_flex">
                                            @foreach ($value2->items as $key3 => $value3)
                                                @php
                                                    $link = '#';
                                                    $target = '_self';
                                                    if ($value3->v_page_element_button_link_type != 'no link') {
                                                        $link = $value3->v_page_element_button_link_external;
                                                        if ($value3->v_page_element_button_link_type == 'internal') {
                                                            $link = url('/').'/'.$value3->v_page_element_button_link_internal;
                                                        }

                                                        // CHECK BUTTON LINK TARGET
                                                        $target = '_self';
                                                        if ($value3->v_page_element_button_link_target == 'new page') {
                                                            $target = '_blank';
                                                        }
                                                    }
                                                @endphp
                                                <div class="flex_box">
                                                    <div class="content_box">
                                                        <div class="content">
                                                            {{-- <h5>{{ $value3->v_page_element_image_title }}</h5> --}}
                                                            <a href="{{ $link }}" target="{{ $target }}">
                                                                <img alt="{{ $value3->v_page_element_image_title }}" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                            </a>
                                                            @if (!empty($value3->v_page_element_text))
                                                                <?php echo $value3->v_page_element_text; ?>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    {{-- ONLY SHOW BUTTON IF BUTTON LINK TYPE IS NOT "NO LINK" & BUTTON LABEL IS NOT EMPTY --}}
                                                    @if ($value3->v_page_element_button_link_type != 'no link' && !empty($value3->v_page_element_button_label))
                                                        @php
                                                            // CHECK BUTTON STYLE
                                                            $style = ''; // red
                                                            if (strtolower($value3->v_page_element_button_style) == 'gray') {
                                                                $style = 'gray_btn';
                                                            }

                                                            // CHECK BUTTON LINK
                                                            $link = $value3->v_page_element_button_link_external;
                                                            if ($value3->v_page_element_button_link_type == 'internal') {
                                                                $link = url('/').'/'.$value3->v_page_element_button_link_internal;
                                                            }

                                                            // CHECK BUTTON LINK TARGET
                                                            $target = '_self';
                                                            if ($value3->v_page_element_button_link_target == 'new page') {
                                                                $target = '_blank';
                                                            }
                                                        @endphp
                                                        <div class="ce_button align_center">
                                                            <a href="{{ $link }}" target="{{ $target }}" class="def_btn {{ $style }}">{{ $value3->v_page_element_button_label }}</a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif ($value2->v_page_element_total_item == 2)
                                    {{-- 2 COLUMNS - AUTOMATIC ALIGN LEFT --}}
                                    <div class="ce_column radius_on align_left">
                                        <div class="row_flex">
                                            @php
                                                $limit = (int) $value2->v_page_element_total_item;
                                                $no_item = 1;
                                            @endphp
                                            @foreach ($value2->items as $key3 => $value3)
                                                @php
                                                    $link = '#';
                                                    $target = '_self';
                                                    if ($value3->v_page_element_button_link_type != 'no link') {
                                                        $link = $value3->v_page_element_button_link_external;
                                                        if ($value3->v_page_element_button_link_type == 'internal') {
                                                            $link = url('/').'/'.$value3->v_page_element_button_link_internal;
                                                        }

                                                        // CHECK BUTTON LINK TARGET
                                                        $target = '_self';
                                                        if ($value3->v_page_element_button_link_target == 'new page') {
                                                            $target = '_blank';
                                                        }
                                                    }
                                                @endphp
                                                <div class="flex_box">
                                                    <div class="content_box">
                                                        <div class="content">
                                                            {{-- <h5>{{ $value3->v_page_element_image_title }}</h5> --}}
                                                            <a href="{{ $link }}" target="{{ $target }}">
                                                                <img alt="{{ $value3->v_page_element_image_title }}" title="{{ $value3->v_page_element_image_title }}" src="{{ asset($value3->v_page_element_image) }}">
                                                            </a>
                                                            @if (!empty($value3->v_page_element_text))
                                                                <?php echo $value3->v_page_element_text; ?>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    {{-- ONLY SHOW BUTTON IF BUTTON LINK TYPE IS NOT "NO LINK" & BUTTON LABEL IS NOT EMPTY --}}
                                                    @if ($value3->v_page_element_button_link_type != 'no link' && !empty($value3->v_page_element_button_label))
                                                        @php
                                                            // CHECK BUTTON STYLE
                                                            $style = ''; // red
                                                            if (strtolower($value3->v_page_element_button_style) == 'gray') {
                                                                $style = 'gray_btn';
                                                            }

                                                            // CHECK BUTTON LINK
                                                            $link = $value3->v_page_element_button_link_external;
                                                            if ($value3->v_page_element_button_link_type == 'internal') {
                                                                $link = url('/').'/'.$value3->v_page_element_button_link_internal;
                                                            }

                                                            // CHECK BUTTON LINK TARGET
                                                            $target = '_self';
                                                            if ($value3->v_page_element_button_link_target == 'new page') {
                                                                $target = '_blank';
                                                            }
                                                        @endphp
                                                        <div class="ce_button">
                                                            <a href="{{ $link }}" target="{{ $target }}" class="def_btn {{ $style }}">{{ $value3->v_page_element_button_label }}</a>
                                                        </div>
                                                    @endif
                                                </div>
                                                @php
                                                    if ($no_item == $limit) {
                                                        break;
                                                    }
                                                    $no_item++;
                                                @endphp
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    {{-- 1 COLUMN - AUTOMATIC ALIGN LEFT & SET IMAGE + TEXT POSITION --}}
                                    @php
                                        $alignment = '';
                                        if ($value2->v_page_element_alignment == 'Left Text, Right Image') {
                                            $alignment = 'row_reverse';
                                        }

                                        $link = '#';
                                        $target = '_self';
                                        if ($value2->items[0]->v_page_element_button_link_type != 'no link') {
                                            $link = $value2->items[0]->v_page_element_button_link_external;
                                            if ($value2->items[0]->v_page_element_button_link_type == 'internal') {
                                                $link = url('/').'/'.$value2->items[0]->v_page_element_button_link_internal;
                                            }

                                            if ($value2->items[0]->v_page_element_button_link_target == 'new page') {
                                                $target = '_blank';
                                            }
                                        }
                                    @endphp
                                    <div class="ce_flex {{ $alignment }}">
                                        <div class="ce_img_left">
                                            <a href="{{ $link }}" target="{{ $target }}">
                                                <img alt="{{ $value2->items[0]->v_page_element_image_title }}" title="{{ $value2->items[0]->v_page_element_image_title }}" src="{{ asset($value2->items[0]->v_page_element_image) }}">
                                            </a>
                                        </div>
                                        <div class="ce_text_right">
                                            {{-- <h3>{{ $value2->items[0]->v_page_element_image_title }}</h3> --}}
                                            @if (!empty($value2->items[0]->v_page_element_text))
                                                <div class="ce_text">
                                                    <?php echo $value2->items[0]->v_page_element_text; ?>
                                                </div>
                                            @endif

                                            @if ($value2->items[0]->v_page_element_button_link_type != 'no link')
                                                @php
                                                    // CHECK BUTTON STYLE
                                                    $style = ''; // red
                                                    if (strtolower($value2->items[0]->v_page_element_button_style) == 'gray') {
                                                        $style = 'gray_btn';
                                                    }

                                                    // CHECK BUTTON LINK
                                                    $link = $value2->items[0]->v_page_element_button_link_external;
                                                    if ($value2->items[0]->v_page_element_button_link_type == 'internal') {
                                                        $link = url('/').'/'.$value2->items[0]->v_page_element_button_link_internal;
                                                    }

                                                    // CHECK BUTTON LINK TARGET
                                                    $target = '_self';
                                                    if ($value2->items[0]->v_page_element_button_link_target == 'new page') {
                                                        $target = '_blank';
                                                    }
                                                @endphp
                                                <div class="ce_button">
                                                    <a href="{{ $link }}" target="{{ $target }}" class="def_btn {{ $style }}">{{ $value2->items[0]->v_page_element_button_label }}</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <!-- /.container -->
                        @elseif ($value2->v_page_element_type == 'video')
                            {{-- Video (Video Only or Video + Text + Button) --}}
                            @php
                                $tmp = explode('v=', $value2->v_page_element_video);
                                $video = $tmp[1];
                            @endphp
                            <div class="container">
                                @if ($value2->v_page_element_video_type == 'video')
                                    {{-- VIDEO ONLY --}}
                                    <div class="ce_video">
                                        <div class="ce_video_box">
                                            <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $video }}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                        <!-- /.ce_video_box -->
                                    </div>
                                    <!-- /.ce_video -->
                                @else
                                    {{-- VIDEO + TEXT --}}
                                    @php
                                        $alignment = '';
                                        if ($value2->v_page_element_alignment == 'Left Text, Right Video') {
                                            $alignment = 'row_reverse';
                                        }
                                    @endphp
                                    <div class="ce_flex {{ $alignment }}">
                                        <div class="ce_video_left">
                                            <div class="ce_video">
                                                <div class="ce_video_box">
                                                    <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $video }}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ce_text_right">
                                            <h3>{{ $value2->v_page_element_video_title }}</h3>
                                            @if (!empty($value2->v_page_element_text))
                                                <div class="ce_text"><?php echo $value2->v_page_element_text; ?></div>
                                            @endif
                                            @if ($value2->v_page_element_button_link_type != 'no link')
                                                @php
                                                    // CHECK BUTTON STYLE
                                                    $style = ''; // red
                                                    if (strtolower($value2->v_page_element_button_style) == 'gray') {
                                                        $style = 'gray_btn';
                                                    }

                                                    // CHECK BUTTON LINK
                                                    $link = $value2->v_page_element_button_link_external;
                                                    if ($value2->v_page_element_button_link_type == 'internal') {
                                                        $link = url('/').'/'.$value2->v_page_element_button_link_internal;
                                                    }

                                                    // CHECK BUTTON LINK TARGET
                                                    $target = '_self';
                                                    if ($value2->v_page_element_button_link_target == 'new page') {
                                                        $target = '_blank';
                                                    }
                                                @endphp
                                                <div class="ce_button">
                                                    <a href="{{ $link }}" target="{{ $target }}" class="def_btn {{ $style }}">{{ $value2->v_page_element_button_label }}</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <!-- /.container -->
                        @elseif ($value2->v_page_element_type == 'button')
                            {{-- BUTTON (SUPPORT MULTIPLE ITEM) --}}
                            {{-- ONLY SHOW IF IT HAS ITEM(S) --}}
                            @if (!empty($value2->items))
                            <div class="container">
                                @php
                                    // CHECK ALIGNMENT ITEMS
                                    $alignment = ''; // left
                                    if ($value2->v_page_element_alignment == 'center') {
                                        $alignment = 'align_center';
                                    } elseif ($value2->v_page_element_alignment == 'right') {
                                        $alignment = 'align_right';
                                    }
                                @endphp
                                <div class="ce_button {{ $alignment }}">
                                    @foreach ($value2->items as $key3 => $value3)
                                        {{-- ONLY DISPLAY ITEM WITH ACTIVE STATUS --}}
                                        @if ($value3->v_page_element_status_item == 1)
                                            @php
                                                // CHECK BUTTON STYLE
                                                $style = ''; // red
                                                if (strtolower($value3->v_page_element_button_style) == 'gray') {
                                                    $style = 'gray_btn';
                                                }

                                                // CHECK BUTTON LINK
                                                $link = $value3->v_page_element_button_link_external;
                                                if ($value3->v_page_element_button_link_type == 'internal') {
                                                    $link = url('/').'/'.$value3->v_page_element_button_link_internal;
                                                }

                                                // CHECK BUTTON LINK TARGET
                                                $target = '_self';
                                                if ($value3->v_page_element_button_link_target == 'new page') {
                                                    $target = '_blank';
                                                }
                                            @endphp
                                            <a href="{{ $link }}" target="{{ $target }}" class="def_btn {{ $style }}">{{ $value3->v_page_element_button_label }}</a>
                                        @endif
                                    @endforeach
                                </div>
                                <!-- /.ce_button -->
                            </div>
                            <!-- /.container -->
                            @endif
                        @elseif($value2->v_page_element_type == 'plain')
                            <div class="container">
                                <?php echo $value2->v_page_element_text; ?>
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>
            <!-- /.section_content_element -->
        @endforeach
    </section>

    @include('_template_web.footer')
@endsection

@section('footer-script')
    {!! $data->footer_script !!}
@endsection
