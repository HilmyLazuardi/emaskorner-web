<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Store Name</th>
            <th>Fullname</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Identity Number</th>
            <th>Birth Date</th>
            <th>Approval Status</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data->data as $item)
            @php
                $phone = '-';
                if (!empty($item->phone_number)) {
                    $first_letter = substr($item->phone_number, 0, 1);
                    $two_letter = substr($item->phone_number, 0, 2);
                    
                    if ($first_letter == "0") {
                        $phone = "0".substr($item->phone_number, 1);
                    } else {
                        $phone = '0'.$item->phone_number;
                    }
                }
            @endphp
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->store_name }}</td>
                <td>{{ $item->fullname }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $phone }}</td>
                <td>{{ $item->identity_number }}</td>
                <td>{{ $item->birth_date }}</td>
                <td>{{ $item->seller_approval_status }}</td>
                <td>{{ $item->seller_status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>