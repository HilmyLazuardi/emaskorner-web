@if (count($errors) > 0)
    <!-- Alert Failed -->
    @foreach ($errors->all() as $error)
        <span class="error_msg">{{ $error }}</span>
    @endforeach
@endif

@if (session('error'))
    <!-- Alert Failed -->
    <span class="error_msg" style="margin-bottom: 10px;">{{ session('error') }}</span>
@endif

@if (session('success'))
    <!-- Alert Failed -->
    <span class="success_msg" style="margin-bottom: 10px;">{{ session('success') }}</span>
@endif