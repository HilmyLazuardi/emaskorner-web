@php
    use App\Libraries\Helper;
    use App\Libraries\HelperWeb;

    $navigation_menu = HelperWeb::get_nav_menu();

    $opened_tab = 'product';

    $opened_page= 1;
    if(isset($_GET['page'])){
        $opened_page = (int) $_GET['page'];
    }
@endphp
<div class="wrapper" id="section_form">
    <header>
        <div class="container">
            <div class="menu_mobile"></div>
            <h1><a href="{{ route('web.home') }}" id="logo">EmasKorner</a></h1>
            <nav>
                <ul>
                    @php
                        if (isset($navigation_menu['top'])) {
                            foreach ($navigation_menu['top'] as $menu) {
                                // set link level 1
                                switch ($menu->link_type) {
                                    case 'internal':
                                        $link_url = url('/') . $menu->link_internal;
                                        break;

                                    case 'external':
                                        $link_url = $menu->link_external;
                                        break;
                                    
                                    default:
                                        // none
                                        $link_url = '#';
                                        break;
                                }

                                // set link target level 1
                                $link_target = '_self';
                                if ($menu->link_type != 'none') {
                                    if ($menu->link_target == 'new window') {
                                        $link_target = '_blank';
                                    }
                                }

                                // hanya level 1
                                echo '<li>';
                                    echo '<a href="'.$link_url.'" target="'.$link_target.'">'.$menu->name.'</a>';
                                echo '</li>';
                            }
                        }
                    @endphp
                </ul>
            </nav>
            <div class="header_right">
                @if (Session::has('buyer'))
                    <div class="h_cart">
                        {{-- <a href="#" class="cart_btn"><span>5</span></a>
                        <div class="cart_box">
                            <div class="cart_title">
                                <span>Keranjang (5)</span>
                                <a href="{{ route('web.buyer.cart') }}">Lihat Sekarang</a>
                            </div>
                            <div class="cart_wrapper">
                                <div class="cart_list">
                                    <div class="cart_img">
                                        <img src="{{ asset('web/images/bando.png') }}">
                                    </div>
                                    <div class="cart_desc">
                                        <h4><a href="#">Bando Kain Anti Iritasi Kulit Kepala</a></h4>
                                        Jumlah : 1
                                    </div>
                                    <div class="cart_price">
                                        Rp. 100.000,00
                                    </div>
                                </div>
                                <div class="cart_list">
                                    <div class="cart_img">
                                        <img src="{{ asset('web/images/bando.png') }}">
                                    </div>
                                    <div class="cart_desc">
                                        <h4><a href="#">Bando Kain Anti Iritasi Kulit Kepala</a></h4>
                                        Jumlah : 1
                                    </div>
                                    <div class="cart_price">
                                        Rp. 100.000,00
                                    </div>
                                </div>
                                <div class="cart_list">
                                    <div class="cart_img">
                                        <img src="{{ asset('web/images/bando.png') }}">
                                    </div>
                                    <div class="cart_desc">
                                        <h4><a href="#">Bando Kain Anti Iritasi Kulit Kepala</a></h4>
                                        Jumlah : 1
                                    </div>
                                    <div class="cart_price">
                                        Rp. 100.000,00
                                    </div>
                                </div>
                                <div class="cart_list">
                                    <div class="cart_img">
                                        <img src="{{ asset('web/images/bando.png') }}">
                                    </div>
                                    <div class="cart_desc">
                                        <h4><a href="#">Bando Kain Anti Iritasi Kulit Kepala</a></h4>
                                        Jumlah : 1
                                    </div>
                                    <div class="cart_price">
                                        Rp. 100.000,00
                                    </div>
                                </div>
                                <div class="cart_list">
                                    <div class="cart_img">
                                        <img src="{{ asset('web/images/bando.png') }}">
                                    </div>
                                    <div class="cart_desc">
                                        <h4><a href="#">Bando Kain Anti Iritasi Kulit Kepala</a></h4>
                                        Jumlah : 1
                                    </div>
                                    <div class="cart_price">
                                        Rp. 100.000,00
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                @endif
                
                <div class="h_search">Search</div>
                
                @if (Session::has('buyer'))
                    <div class="h_profil">
                        <a href="{{ route('web.buyer.profile') }}">Profil</a>
                        <div class="popup_profil">
                            <div class="popup_profil_box">
                                <ul>
                                    <li><a href="{{ route('web.buyer.profile') }}">Profil</a></li>
                                    <li><a href="{{ route('web.order.history') }}">Riwayat Pesanan</a></li>
                                    <li><a href="{{ route('web.buyer.list_address') }}">Daftar Alamat</a></li>
                                    <li><a href="{{ route('web.buyer.wishlist') }}">Wishlist</a></li>
                                </ul>
                                <a href="{{ route('web.auth.logout') }}" class="green_btn">Logout</a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="h_login">
                        <a href="{{ route('web.auth.login') }}">Masuk</a> | <a href="{{ route('web.auth.register') }}">Daftar</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="category_box">
            <div class="container">
                <ul>
                    @if (isset($product_category[0]))
                        @foreach ($product_category as $item)
                            <li><a href="{{ route('web.product.list', $item->slug) }}">{!! $item->name !!}</a></li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
        <div class="popup_search">
            <div class="container">
                <form action="{{ route('web.product.search') }}">
                    <input type="hidden" name="page" value="{{ $opened_page }}">
                    <input type="text" name="keyword" placeholder="Cari di sini">
                    <div class="header_right">
                        <button class="search_btn" type="submit"></button>
                        <div class="close_btn"></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="popup_menu">
            <div class="overlay" onclick="hide_popup();"></div>
            <div class="popup_box">
                <div class="close_btn" onclick="hide_popup();"></div>
                <img src="{{ asset('web/images/logo_beta_gold.png') }}">
                <ul>
                    @php
                        if (isset($navigation_menu['top'])) {
                            foreach ($navigation_menu['top'] as $menu) {
                                // set link level 1
                                switch ($menu->link_type) {
                                    case 'internal':
                                        $link_url = url('/') . $menu->link_internal;
                                        break;

                                    case 'external':
                                        $link_url = $menu->link_external;
                                        break;
                                    
                                    default:
                                        // none
                                        $link_url = '#';
                                        break;
                                }

                                // set link target level 1
                                $link_target = '_self';
                                if ($menu->link_type != 'none') {
                                    if ($menu->link_target == 'new window') {
                                        $link_target = '_blank';
                                    }
                                }

                                // hanya level 1
                                echo '<li>';
                                    echo '<a href="'.$link_url.'" target="'.$link_target.'">'.$menu->name.'</a>';
                                echo '</li>';
                            }
                        }
                    @endphp
                </ul>
                <div class="popup_cat">
                    <h3>Kategori Produk</h3>
                    <ul>
                        {{-- <li><a href="#">Art &amp; Craft <span>(97)</span></a></li>
                        <li><a href="#">Fashion &amp; Accesories <span>(230)</span></a></li>
                        <li><a href="#">Gadget &amp; Tech <span>(32)</span></a></li>
                        <li><a href="#">Commodities <span>(86)</span></a></li> --}}
                        @if (isset($product_category[0]))
                            @foreach ($product_category as $item)
                                <li><a href="{{ route('web.product.list', $item->slug) }}">{!! $item->name !!} <span>({{ $item->products }})</span></a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </header>