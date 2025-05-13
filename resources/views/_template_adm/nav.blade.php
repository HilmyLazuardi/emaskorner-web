@php
    // Libraries
    use App\Libraries\Helper;
@endphp

<!-- top navigation -->
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>

            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Helper::get_avatar() }}" alt="">{{ Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->fullname }}
                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu animated fadeInDown pull-right">
                        <li><a href="{{ route('admin.profile') }}"> {{ ucwords(lang('my profile', $translations)) }}</a></li>
                        
                        @if (env('MULTILANG_MODULE', false))
                            <li>
                                <a href="javascript:void(0);" onclick="setTimeout(function(){ $('#menu-country').click(); }, 500);">
                                    <span class="badge bg-blue pull-right">{{ Session::get('country_used') }}</span>
                                    <span>{{ ucwords(lang('change #item', $translations, ['#item' => lang('country', $translations)])) }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="setTimeout(function(){ $('#menu-lang').click(); }, 500);">
                                    <span class="badge bg-blue pull-right">{{ Session::get('language_used') }}</span>
                                    <span>{{ ucwords(lang('change #item', $translations, ['#item' => lang('language', $translations)])) }}</span>
                                </a>
                            </li>
                        @endif
                        
                        <li>
                            <a href="{{ route('admin.logout') }}" style="color:rgba(231,76,60,.88); !important;" onclick="return confirm('{{ lang('Are you sure to #action?', $translations, ['#action' => lang('log out', $translations)]) }}')">
                                <b><i class="fa fa-sign-out pull-right"></i> {{ ucwords(lang('log out', $translations)) }}</b>
                            </a>
                        </li>
                    </ul>
                </li>

                @if (env('MULTILANG_MODULE', false))
                    <li role="presentation" class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false" id="menu-lang" style="display: none">
                            <i class="fa fa-language"></i>
                        </a>
                        <ul id="menu-lang-list" class="dropdown-menu list-unstyled msg_list animated fadeInDown" role="menu">
                            <li>
                                <div class="text-center">
                                    <strong>{{ ucwords(lang('change #item', $translations, ['#item' => lang('language', $translations)])) }}</strong>
                                </div>
                            </li>
                            @if (isset($sio_languages) && count($sio_languages) > 0)
                                @foreach ($sio_languages as $key => $value)
                                    @php
                                        $lang_stat = '';
                                        if (Session::get('language_used') == $value->alias)
                                        {
                                            $lang_stat = '<b class="pull-right">'. strtoupper(lang('active', $translations)) .'</b>';
                                        }
                                    @endphp
                                    <li>
                                        <a href="{{ route('admin.change_language', $value->alias) }}">
                                            <span>{{ $value->alias . ' - ' . $value->name }} <?php echo $lang_stat; ?></span>
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <a>
                                        <span>EN - English <i class="pull-right">default</i></span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    <li role="presentation" class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false" id="menu-country" style="display: none">
                            <i class="fa fa-flag"></i>
                        </a>
                        <ul id="menu-country-list" class="dropdown-menu list-unstyled msg_list animated fadeInDown" role="menu">
                            <li>
                                <div class="text-center">
                                    <strong>{{ ucwords(lang('change #item', $translations, ['#item' => lang('country', $translations)])) }}</strong>
                                </div>
                            </li>
                            @if (isset($sio_countries) && count($sio_countries) > 0)
                                @foreach ($sio_countries as $key => $value)
                                    @php
                                        $lang_stat = '';
                                        if (Session::get('country_used') == $value->country_alias)
                                        {
                                            $lang_stat = '<b class="pull-right">'. strtoupper(lang('active', $translations)) .'</b>';
                                        }
                                    @endphp
                                    <li>
                                        <a href="{{ route('admin.change_country', $value->country_alias) }}">
                                            <span>{{ $value->country_alias . ' - ' . $value->country_name }} <?php echo $lang_stat; ?></span>
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <a>
                                        <span>US - United States <i class="pull-right">default</i></span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (env('NOTIF_MODULE'))
                    <li role="presentation" class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell"></i>
                            <span class="badge bg-orange" id="sys_notif_badge" style="display: none;">6</span>
                        </a>
                        <ul id="menu-notif-list" class="dropdown-menu list-unstyled msg_list animated fadeInDown" role="menu">
                            <li>
                                <a>
                                    <span class="message text-center">
                                        No Unread Notifications
                                    </span>
                                </a>
                            </li>
                            <li>
                                <div class="text-center">
                                    <a href="{{ route('admin.notif.list') }}">
                                        <strong>{{ ucwords(lang('see all #item', $translations, ['#item' => lang('notifications', $translations)])) }}</strong>
                                        <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</div>
<!-- /top navigation -->