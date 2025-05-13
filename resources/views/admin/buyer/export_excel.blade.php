@php
    // Libraries
    use App\Libraries\Helper;
@endphp

<table>
    <thead>
    <tr>
        <th>No</th>
        <th>Fullname</th>
        <th>Phone Number</th>
        <th>Email</th>
        <th>Tanggal Lahir</th>
        <th>Status</th>
        <th>Submitted</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            @php
                $no = $key + 1;
                $birth_date = date('d/m/y', strtotime($item->birth_date));
                $status = 'Inactived';
                if($item->status == 1){
                    $status = 'Actived';
                }
            @endphp
            <tr>
                <td>{!! $no !!}</td>
                <td>{!! $item->fullname !!}</td>
                <td>{!! $item->phone_number !!}</td>
                <td>{!! $item->email !!}</td>
                <td>{!! $birth_date !!}</td>
                <td>{!! $status !!}</td>
                <td>{!! Helper::locale_timestamp($item->created_at) !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>