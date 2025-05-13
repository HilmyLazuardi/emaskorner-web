<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Seller</th>
            <th>Kategori</th>
            <th>SKU var.</th>
            <th>Product Name</th>
            <th>Product Image</th>
            <th>QTY</th>
            <th>Harga</th>
            {{-- <th>Campaign Mulai</th>
            <th>Campaign Berakhir</th> --}}
            <th>Status</th>
            <th>Ditampilkan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data->data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->seller_store_name }}</td>
                <td>{{ $item->product_category_name }}</td>
                <td>{{ $item->sku_variant }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->source_image }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->price }}</td>
                {{-- <td>{{ $item->campaign_start }}</td>
                <td>{{ $item->campaign_end }}</td> --}}
                <td>{{ $item->approval }}</td>
                <td>{{ $item->published }}</td>
            </tr>
        @endforeach
    </tbody>
</table>