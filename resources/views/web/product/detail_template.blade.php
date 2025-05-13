@extends('_template_web.master')

@section('title', 'Wishlist')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="css/jquery.fancybox.min.css">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>
    <script type="text/javascript" src="js/jquery.fancybox.min.js"></script>
    <script type="text/javascript" src="js/canvasjs.min.js"></script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')

    <section>
        <div class="section_product_banner vtwo">
            <div class="row_flex container">
                <div class="spd_box">
                    <div class="spd_img_box">
                        <div class="slider_banner">
                            <div class="bs_img"><img src="{{ asset('web/images/sepatu2.png') }}"></div>
                            <div class="bs_img"><img src="{{ asset('web/images/sepatu2.png') }}"></div>
                        </div>
                        <div class="wishlist_btn">
                            <input type="checkbox">
                            <span></span>
                        </div>
                    </div>
                </div>
                <div class="spn_box">
                    <div class="section_product_name">
                        <div class="container">
                            <h3>Sepatu Pria Bahan Kulit Sapi Muda</h3>
                            <span class="price">Rp400.000</span>
                            <div class="share_btn"></div>
                        </div>
                    </div>
                    <div class="section_product_info">
                        <div class="row_flex">
                            <div class="spi_left">
                                <div class="spi_box">
                                    <span>112</span>
                                    Pemesan
                                </div>
                                <div class="spi_box">
                                    <span>18 hari</span>
                                    Tersisa
                                </div>
                            </div>
                            <div class="spi_mid">
                                <div class="spi_box">
                                    <div class="pl_diagram_box">
                                        <div id="chartContainer" style="width: 160px;height: 160px;"></div>
                                        <div class="info_stock"><span>176 Dipesan</span> dari 200</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row_flex">
                            <div class="spi_right">
                                <div class="form_box">
                                    <span class="title">Warna</span>
                                    <div class="box_init">
                                        <select class="select3" data-placeholder="Ukuran">
                                            <option></option>
                                            <option>Hitam</option>
                                            <option>Putih</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form_box">
                                    <span class="title">Ukuran</span>
                                    <div class="box_init">
                                        <select class="select3" data-placeholder="Ukuran">
                                            <option></option>
                                            <option>36</option>
                                            <option>37</option>
                                            <option>38</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form_box">
                                    <span class="title">Jumlah</span>
                                    <div class="box_init">
                                        <button class="minus_btn">-</button>
                                        <input type="text" value="1">
                                        <button class="plus_btn">+</button>
                                    </div>
                                </div>
                                <button class="red_btn">Pre Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="section_product_menu">
            <div class="container">
                <ul class="tab_menu">
                    <li><a href="#tab-1" class="active">Deskripsi Produk</a></li>
                    <li><a href="#tab-2">FAQ</a></li>
                    <li><a href="#tab-3">Tentang Penjual</a></li>
                </ul>
                <div class="tab_wrapper" id="tab-1">
                    <div class="section_content_element_simple">
                        <div class="container">
                            <h3>Maecenas mattis hendrerit dolor ut suscipit dictum vitae ipsum. Nullam lorem ante, venenatis in venenatis sed, consectetur ut diam.</h3>
                            <div class="sce_image"><img src="{{ asset('web/images/sepatu2.png') }}"></div>
                            <div class="sce_text">
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ultricies eleifend aliquam. Nullam lorem ante, venenatis in venenatis sed, consectetur ut diam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin at rhoncus lectus. Nunc vulputate, tellus vitae tempor convallis, metus nisi elementum velit, at varius velit leo sit amet lacus. Maecenas sed massa maximus, elementum metus id, lobortis mi. Proin ac mattis lectus. Curabitur lacus dolor, tempus sit amet eros eu, auctor egestas nisi. Ut tempor condimentum porttitor. Proin fermentum aliquam sapien, at hendrerit purus venenatis sit amet. Aliquam eget lorem quis purus consequat euismod. Mauris eu scelerisque nunc, ut consequat ligula. Donec pellentesque leo quis sapien laoreet, eget lobortis eros bibendum. Donec cursus non odio tristique gravida. Praesent at erat congue, elementum urna in, ornare turpis. Sed quis leo nec leo tincidunt venenatis.</p>
                                <p>In hac habitasse platea dictumst. Vestibulum fermentum augue ante, non pretium nisl congue tincidunt. Praesent laoreet rutrum lacus nec interdum. Integer id cursus quam. Donec sem nisl, tempus in odio elementum, auctor blandit ex. Maecenas aliquam elit metus, at ultricies dui porta a. Integer gravida libero eget quam aliquet sodales sed ut ante. Pellentesque tortor nunc, sollicitudin eu accumsan cursus, semper a nunc. Ut eu sem eget ante fermentum tincidunt.</p>
                                <p>Vivamus volutpat ante vel congue scelerisque. Duis condimentum dignissim lectus, molestie elementum justo. Sed nulla nisi, viverra vel augue ac, posuere fermentum ligula. Duis eget viverra massa, at viverra elit. Integer blandit rhoncus condimentum. Duis justo orci, euismod et massa id, laoreet consequat nulla. Curabitur placerat neque vel velit mollis sollicitudin eget vitae ex. Nam fermentum viverra enim non dignissim. Donec convallis felis eget felis porta mattis malesuada vel ante.</p>
                            </div>
                            <div class="sce_clear">
                                <div class="sce_img_left"><img src="{{ asset('web/images/sepatu2.png') }}"></div>
                                <div class="sce_desc_right"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ultricies eleifend aliquam. Nullam lorem ante, venenatis in venenatis sed, consectetur ut diam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin at rhoncus lectus. Nunc vulputate, tellus vitae tempor convallis, metus nisi elementum velit, at varius velit leo sit amet lacus. Maecenas sed massa maximus, elementum metus id, lobortis mi. Proin ac mattis lectus. Curabitur lacus dolor, tempus sit amet eros eu, auctor egestas nisi. Ut tempor condimentum porttitor. Proin fermentum aliquam sapien, at hendrerit purus venenatis sit amet. Aliquam eget lorem quis purus consequat euismod. Mauris eu scelerisque nunc, ut consequat ligula. Donec pellentesque leo quis sapien laoreet, eget lobortis eros bibendum. Donec cursus non odio tristique gravida. Praesent at erat congue, elementum urna in, ornare turpis. Sed quis leo nec leo tincidunt venenatis.</p>
                                </div>
                            </div>
                            <div class="sce_clear">
                                <div class="sce_img_right"><img src="{{ asset('web/images/sepatu2.png') }}"></div>
                                <div class="sce_desc_left"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ultricies eleifend aliquam. Nullam lorem ante, venenatis in venenatis sed, consectetur ut diam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin at rhoncus lectus. Nunc vulputate, tellus vitae tempor convallis, metus nisi elementum velit, at varius velit leo sit amet lacus. Maecenas sed massa maximus, elementum metus id, lobortis mi. Proin ac mattis lectus. Curabitur lacus dolor, tempus sit amet eros eu, auctor egestas nisi. Ut tempor condimentum porttitor. Proin fermentum aliquam sapien, at hendrerit purus venenatis sit amet. Aliquam eget lorem quis purus consequat euismod. Mauris eu scelerisque nunc, ut consequat ligula. Donec pellentesque leo quis sapien laoreet, eget lobortis eros bibendum. Donec cursus non odio tristique gravida. Praesent at erat congue, elementum urna in, ornare turpis. Sed quis leo nec leo tincidunt venenatis.</p>
                                </div>
                            </div>
                            <div class="sce_clear">
                                <div class="sce_img_left">
                                    <div class="sce_video">
                                        <div class="sce_video_box">
                                          <!-- Copy & Pasted from YouTube -->
                                          <iframe width="560" height="315" src="https://www.youtube.com/embed/yHgLTGXY2So" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                </div>
                                <div class="sce_desc_right"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ultricies eleifend aliquam. Nullam lorem ante, venenatis in venenatis sed, consectetur ut diam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin at rhoncus lectus. Nunc vulputate, tellus vitae tempor convallis, metus nisi elementum velit, at varius velit leo sit amet lacus. Maecenas sed massa maximus, elementum metus id, lobortis mi. Proin ac mattis lectus. Curabitur lacus dolor, tempus sit amet eros eu, auctor egestas nisi. Ut tempor condimentum porttitor. Proin fermentum aliquam sapien, at hendrerit purus venenatis sit amet. Aliquam eget lorem quis purus consequat euismod. Mauris eu scelerisque nunc, ut consequat ligula. Donec pellentesque leo quis sapien laoreet, eget lobortis eros bibendum. Donec cursus non odio tristique gravida. Praesent at erat congue, elementum urna in, ornare turpis. Sed quis leo nec leo tincidunt venenatis.</p>
                                </div>
                            </div>
                            <div class="sce_clear">
                                <div class="sce_img_right">
                                    <div class="sce_video">
                                        <div class="sce_video_box">
                                          <!-- Copy & Pasted from YouTube -->
                                          <iframe width="560" height="315" src="https://www.youtube.com/embed/yHgLTGXY2So" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                </div>
                                <div class="sce_desc_left"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ultricies eleifend aliquam. Nullam lorem ante, venenatis in venenatis sed, consectetur ut diam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin at rhoncus lectus. Nunc vulputate, tellus vitae tempor convallis, metus nisi elementum velit, at varius velit leo sit amet lacus. Maecenas sed massa maximus, elementum metus id, lobortis mi. Proin ac mattis lectus. Curabitur lacus dolor, tempus sit amet eros eu, auctor egestas nisi. Ut tempor condimentum porttitor. Proin fermentum aliquam sapien, at hendrerit purus venenatis sit amet. Aliquam eget lorem quis purus consequat euismod. Mauris eu scelerisque nunc, ut consequat ligula. Donec pellentesque leo quis sapien laoreet, eget lobortis eros bibendum. Donec cursus non odio tristique gravida. Praesent at erat congue, elementum urna in, ornare turpis. Sed quis leo nec leo tincidunt venenatis.</p>
                                </div>
                            </div>
                            <div class="sce_video">
                                <div class="sce_video_box">
                                  <!-- Copy & Pasted from YouTube -->
                                  <iframe width="560" height="315" src="https://www.youtube.com/embed/yHgLTGXY2So" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab_wrapper" id="tab-2">
                    <div class="faq_wrapper">
                        <div class="faq_box">
                            <div class="faq_top">Bagaimana cara mencuci sepatu kulit?</div>
                            <div class="faq_bottom">Ya tinggal di cuci pakai sabun dan air bersih ya.</div>
                        </div>
                        <div class="faq_box">
                            <div class="faq_top">Jika tidak sengaja sepatu saya terkena cat, apa yang harus saya lakukan?</div>
                            <div class="faq_bottom">Ya di cuci atau beli baru lah.</div>
                        </div>
                        <div class="faq_box">
                            <div class="faq_top">Dimana saya bisa mendapatkan akesories sepatu yang sesuai?</div>
                            <div class="faq_bottom">Di toko resmi kami ya.</div>
                        </div>
                    </div>
                </div>
                <div class="tab_wrapper" id="tab-3">
                    <div class="produsen_box">
                        <div class="row_clear">
                            <div class="produsen_img">
                                <img src="{{ asset('web/images/nara.png') }}">	
                            </div>
                            <div class="produsen_name">
                                <h4>Nara Shoes</h4>
                                <span>Kota Bandung</span>
                            </div>
                        </div>
                        <div class="produsen_desc">
                            <p>Sepatu Berbahan kulit domba yang melewati proses pengawetan kimiawi untuk menambah daya tahan kulit dan kekuatan sepatu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection