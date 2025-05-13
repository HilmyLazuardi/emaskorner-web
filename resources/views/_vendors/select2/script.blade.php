<script src="{{ asset('vendors/select2/dist/js/select2.min.js') }}"></script>
<script>
    // Initialize Select2
    $('.select2').select2();
    // keep search input, but avoid autofocus on dropdown open
    $('.select2').on('select2:open', function (e) {
        $('.select2-search input').prop('focus',false);
    });
</script>