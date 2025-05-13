<table>
    <thead>
    <tr>
        <th>App Name</th>
        <th>App Version</th>
        <th>App Copyright Year</th>
        <th>App URL</th>
        <th>Main App URL</th>
        <th>App Info</th>
        <th>App Powered By</th>
        <th>App Powered By URL</th>
        <th>Meta Title</th>
        <th>Meta Description</th>
        <th>Meta Keywords</th>
        <th>Meta Author</th>
        <th>Open Graph Type</th>
        <th>Open Graph Site Name</th>
        <th>Open Graph Title</th>
        <th>Open Graph Description</th>
        <th>Open Graph Twitter Card</th>
        <th>Open Graph Twitter Site</th>
        <th>Open Graph Twitter Site ID</th>
        <th>Open Graph FB App ID</th>
        <th>Site Key (admin)</th>
        <th>Secret Key (admin)</th>
        <th>Site Key (public)</th>
        <th>Secret Key (public)</th>
        <th>Secure Login</th>
        <th>Login Trial Limit</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $data->app_name }}</td>
            <td>{{ $data->app_version }}</td>
            <td>{{ $data->app_copyright_year }}</td>
            <td>{{ $data->app_url_site }}</td>
            <td>{{ $data->app_url_main }}</td>
            <td>{{ $data->app_info }}</td>
            <td>{{ $data->powered_by }}</td>
            <td>{{ $data->powered_by_url }}</td>
            <td>{{ $data->meta_title }}</td>
            <td>{{ $data->meta_description }}</td>
            <td>{{ $data->meta_keywords }}</td>
            <td>{{ $data->meta_author }}</td>
            <td>{{ $data->og_type }}</td>
            <td>{{ $data->og_site_name }}</td>
            <td>{{ $data->og_title }}</td>
            <td>{{ $data->og_description }}</td>
            <td>{{ $data->twitter_card }}</td>
            <td>{{ $data->twitter_site }}</td>
            <td>{{ $data->twitter_site_id }}</td>
            <td>{{ $data->fb_app_id }}</td>
            <td>{{ $data->recaptcha_site_key_admin }}</td>
            <td>{{ $data->recaptcha_secret_key_admin }}</td>
            <td>{{ $data->recaptcha_site_key_public }}</td>
            <td>{{ $data->recaptcha_secret_key_public }}</td>
            <td>{{ $data->secure_login }}</td>
            <td>{{ $data->login_trial }}</td>
        </tr>
    </tbody>
</table>