@php
    use App\Libraries\HelperWeb;

    $navigation_menu    = HelperWeb::get_nav_menu();
    $company_info       = HelperWeb::get_company_info();
    $social_media       = HelperWeb::get_social_media();
@endphp

<footer>
    <div class="container">
        <div class="row_flex footer_top">
            <div class="column_box">
                <h4>Hubungi Kami</h4>
                <table>
                    @if ($company_info->phone)
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{ $company_info->phone }}</td>
                        </tr>
                    @endif
                    @if ($company_info->wa_phone)
                        <tr>
                            <td>WhatsApp</td>
                            <td>:</td>
                            <td>{{ env('COUNTRY_CODE').$company_info->wa_phone }}</td>
                        </tr>
                    @endif
                    @if ($company_info->email_contact)
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td><a href="mailto:{{ $company_info->email_contact }}" target="_blank">{{ $company_info->email_contact }}</a></td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="column_box">
                @php
                    if (isset($navigation_menu['bottom'])) {
                        foreach ($navigation_menu['bottom'] as $menu) {
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

                            echo '<div class="cb_footer">';
                                echo '<h4>'.$menu->name.'</h4>';
                                echo '<ul>';
                                    // generate level 2
                                    if (isset($menu->level_2)) {
                                        foreach ($menu->level_2 as $level_2) {
                                            // set link level 2
                                            switch ($level_2->link_type) {
                                                case 'internal':
                                                    $link_url = url('/') . $level_2->link_internal;
                                                    break;

                                                case 'external':
                                                    $link_url = $level_2->link_external;
                                                    break;
                                                
                                                default:
                                                    // none
                                                    $link_url = '#';
                                                    break;
                                            }

                                            // set link target level 1
                                            $link_target = '_self';
                                            if ($level_2->link_type != 'none') {
                                                if ($level_2->link_target == 'new window') {
                                                    $link_target = '_blank';
                                                }
                                            }

                                            echo '<li>';
                                                echo '<a href="'.$link_url.'" target="'.$link_target.'">'.$level_2->name.'</a>';
                                            echo '</li>';
                                        }
                                    }
                                echo '</ul>';
                            echo '</div>';
                        }
                    }
                @endphp
            </div>
            <div class="column_box">
                @if ($company_info->address)
                    <h4>Alamat</h4>
                    <p>{!! nl2br($company_info->address) !!}</p>
                @endif

                @if (isset($social_media[0]))
                    <h4>Follow Us</h4>
                    <div class="sosmed_box">
                        @foreach ($social_media as $item)
                            <a href="{{ $item->link }}" target="_blank" class="socmed_btn"><img src="{{ asset($item->logo) }}"></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="footer_bottom">
            <div class="cr_box" style="text-align: center;">
                Copyright &copy; {{ $global_config->app_copyright_year }} {!! $company_info->name !!}. All Right Reserved.
            </div>
            <a href="https://wa.me/{{ env('COUNTRY_CODE').$company_info->wa_phone }}" class="wa_btn" target="_blank"></a>
        </div>
    </div>
</footer>