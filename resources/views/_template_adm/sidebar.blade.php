@php
    // Libraries
    use App\Libraries\Helper;

    $badge_new = '<span class="label label-success pull-right">NEW</span>';
@endphp

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
    <div class="menu_section">
        <h3>{{ ucwords(lang('main menu', $translations)) }}</h3>
        <ul class="nav side-menu">
            <li>
                <a href="{{ route('admin.home') }}">
                    <i class="fa fa-home"></i> {{ ucwords(lang('home', $translations)) }}
                </a>
            </li>

            @if (Helper::authorizing('Product Category', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/product-category/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.product_category') }}">
                        <i class="fa fa-cube"></i> {{ ucwords(lang('product category', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Product Item', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/product-item/') || Helper::is_menu_active('/product-variant/') || Helper::is_menu_active('/product-content/') || Helper::is_menu_active('/product-faq/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.product_item') }}">
                        <i class="fa fa-cubes"></i> {{ ucwords(lang('product item', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Product Featured', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/product-featured/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.product_featured') }}">
                        <i class="fa fa-star"></i> {{ ucwords(lang('product featured', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Invoice', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/invoice/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.invoice') }}">
                        <i class="fa fa-file-text"></i> {{ ucwords(lang('invoice', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Order', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/order/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.order') }}">
                        <i class="fa fa-shopping-cart"></i> {{ ucwords(lang('order', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Buyer', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/buyer/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.buyer') }}">
                        <i class="fa fa-dollar"></i> {{ ucwords(lang('buyer', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Seller', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/seller/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.seller') }}">
                        <i class="fa fa-briefcase"></i> {{ ucwords(lang('seller', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Electronic Contract', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/e-contract/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.econtract') }}">
                        <i class="fa fa-pencil-square-o"></i> {{ ucwords(lang('Electronic Contract', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Voucher', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/voucher/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.voucher') }}">
                        <i class="fa fa-ticket"></i> {{ ucwords(lang('voucher', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Shipper', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/shipper/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.shipper') }}">
                        <i class="fa fa-truck"></i> {{ ucwords(lang('shipper', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Navigation Menu', 'View List')['status'] == 'true')
                @php
                    $positions = ['top', 'bottom'];
                @endphp
                @foreach ($positions as $position)
                    @php
                        $menu_active = '';
                        if(Helper::is_menu_active('/nav-menu/'.$position.'/')){
                            $menu_active = 'current-page';
                        }
                    @endphp
                    <li class="{{ $menu_active }}">
                        <a href="{{ route('admin.nav_menu', $position) }}">
                            <i class="fa fa-list"></i> {{ ucwords(lang($position.' navigation menu', $translations)) }}
                        </a>
                    </li>
                @endforeach
            @endif

            @if (Helper::authorizing('Page', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/page/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.page') }}">
                        <i class="fa fa-copy"></i> {{ ucwords(lang('page', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Social Media', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/social-media/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.social_media') }}">
                        <i class="fa fa-facebook-square"></i> {{ ucwords(lang('social media', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('FAQ', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/faq/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.faq') }}">
                        <i class="fa fa-question-circle"></i> {{ lang('FAQ', $translations) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Banner', 'View List')['status'] == 'true')
                @php
                    $positions = ['home'];
                @endphp
                @foreach ($positions as $position)
                    @php
                        $menu_active = '';
                        if(Helper::is_menu_active('/banner/'.$position.'/')){
                            $menu_active = 'current-page';
                        }
                    @endphp
                    <li class="{{ $menu_active }}">
                        <a href="{{ route('admin.banner', $position) }}">
                            <i class="fa fa-image"></i> {{ ucwords(lang($position.' banner', $translations)) }}
                        </a>
                    </li>
                @endforeach
            @endif

            @if (Helper::authorizing('Banner Popup', 'View List')['status'] == 'true')
                @php
                    $positions = ['home'];
                @endphp
                @foreach ($positions as $position)
                    @php
                        $menu_active = '';
                        if(Helper::is_menu_active('/banner-popup/'.$position.'/')){
                            $menu_active = 'current-page';
                        }
                    @endphp
                    <li class="{{ $menu_active }}">
                        <a href="{{ route('admin.banner_popup', $position) }}">
                            <i class="fa fa-bullhorn"></i> {{ ucwords(lang($position.' banner popup', $translations)) }}
                        </a>
                    </li>
                @endforeach
            @endif

            @if (Helper::authorizing('News Category', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if (Helper::is_menu_active('/news-category/')) {
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.news_category') }}">
                        <i class="fa fa-tags"></i> {{ ucwords(lang('news category', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('News', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if (Helper::is_menu_active('/news/')) {
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.news') }}">
                        <i class="fa fa-newspaper-o"></i> {{ ucwords(lang('news', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Blog Subscription', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if (Helper::is_menu_active('/blog-subscription/')) {
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.blog_subscription') }}">
                        <i class="fa fa-cube"></i> {{ ucwords(lang('blog subscription', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Blog Category', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if (Helper::is_menu_active('/blog-category/')) {
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.blog_category') }}">
                        <i class="fa fa-cube"></i> {{ ucwords(lang('blog category', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Banner', 'View List')['status'] == 'true')
                @php
                    $positions = ['blog'];
                @endphp
                @foreach ($positions as $position)
                    @php
                        $menu_active = '';
                        if(Helper::is_menu_active('/banner/'.$position.'/')){
                            $menu_active = 'current-page';
                        }
                    @endphp
                    <li class="{{ $menu_active }}">
                        <a href="{{ route('admin.banner', $position) }}">
                            <i class="fa fa-image"></i> {{ ucwords(lang($position.' banner', $translations)) }}
                        </a>
                    </li>
                @endforeach
            @endif

            @if (Helper::authorizing('Blog', 'View List')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/blog/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.blog') }}">
                        <i class="fa fa-file-text-o"></i> {{ ucwords(lang('blog', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Global Config', 'View')['status'] == 'true')
                @php
                    $menu_active = '';
                    if(Helper::is_menu_active('/global-config/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.global.config') }}">
                        <i class="fa fa-gears"></i> {{ ucwords(lang('global configuration', $translations)) }}
                    </a>
                </li>
            @endif
        </ul>
    </div>

    @php
        $priv_admin = 0;
    @endphp
    <div class="menu_section" id="navmenu_admin" style="display:none">
        <hr>
        <h3>{{ ucwords(lang('administration', $translations)) }}</h3>
        <ul class="nav side-menu">
            @if (Helper::authorizing('System Logs', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/system-logs/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.system_logs') }}">
                        <i class="fa fa-exchange"></i> {{ ucwords(lang('system logs', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Config', 'View')['status'] == 'true')
                @php
                    $priv_admin++;
                @endphp
                <li>
                    <a href="{{ route('admin.config') }}">
                        <i class="fa fa-gears"></i> {{ ucwords(lang('configuration', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Module', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/module/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.module') }}">
                        <i class="fa fa-folder"></i> {{ ucwords(lang('module', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Rules', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/rules/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.module_rule') }}">
                        <i class="fa fa-gavel"></i> {{ ucwords(lang('rules', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Office', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/office/') || Helper::is_menu_active('/branch/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.office') }}">
                        <i class="fa fa-building"></i> {{ ucwords(lang('office', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Admin Group', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/group/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.group') }}">
                        <i class="fa fa-users"></i> {{ ucwords(lang('admin group', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Administrator', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/administrator/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.user_admin') }}">
                        <i class="fa fa-user-secret"></i> {{ ucwords(lang('administrator', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Country', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/country/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.country') }}">
                        <i class="fa fa-flag"></i> {{ ucwords(lang('country', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Phrase', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/phrase/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.phrase') }}">
                        <i class="fa fa-list-alt"></i> {{ ucwords(lang('phrase', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Blocked IP', 'View List')['status'] == 'true' && $global_config->secure_login)
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/blocked-ip/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.blocked_ip') }}">
                        <i class="fa fa-times-circle-o"></i> {{ ucwords(lang('blocked IP', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Error Logs', 'View List')['status'] == 'true')
                @php
                    $priv_admin++;
                    $menu_active = '';
                    if(Helper::is_menu_active('/error-logs/')){
                        $menu_active = 'current-page';
                    }
                @endphp
                <li class="{{ $menu_active }}">
                    <a href="{{ route('admin.error_logs') }}">
                        <i class="fa fa-warning"></i> {{ ucwords(lang('error logs', $translations)) }}
                    </a>
                </li>
            @endif

            @if (Helper::authorizing('Phrase', 'View')['status'] == 'true')
                <li>
                    <a href="{{ route('dev.phpinfo') }}">
                        <i class="fa fa-desktop"></i> PHPINFO
                    </a>
                </li>
                <li>
                    <a href="{{ route('dev.cheatsheet_form') }}">
                        <i class="fa fa-file-text-o"></i> {{ ucwords(lang('cheatsheet form', $translations)) }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dev.encrypt') }}">
                        <i class="fa fa-lock"></i> {{ ucwords(lang('encrypt tool', $translations)) }} {!! $badge_new !!}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dev.decrypt') }}">
                        <i class="fa fa-unlock"></i> {{ ucwords(lang('decrypt tool', $translations)) }} {!! $badge_new !!}
                    </a>
                </li>
                <li>
                    <a href="{{ route('dev.nav_menu') }}" target="_blank">
                        <i class="fa fa-sitemap"></i> {{ ucwords(lang('nav menu structure', $translations)) }} &nbsp;<i class="fa fa-external-link pull-right"></i>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
<!-- /sidebar menu -->

@section('script-sidebar')
    <script>
        @if ($priv_admin > 0)
            $('#navmenu_admin').show();
        @endif
    </script>
@endsection