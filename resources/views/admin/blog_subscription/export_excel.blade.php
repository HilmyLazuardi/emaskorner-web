@php
    // LIBRARIES
    use App\Libraries\Helper;
@endphp

<table>
    <thead>
    <tr>
        <th>No</th>
        <th>Email</th>
        <th>Created at</th>
        <th>Updated at</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            @php $no = $key + 1; @endphp
            <tr>
                <td>{!! $no !!}</td>
                <td>{!! $item->email !!}</td>
                <td>{!! Helper::locale_timestamp($item->created_at) !!}</td>
                <td>{!! Helper::locale_timestamp($item->updated_at) !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>