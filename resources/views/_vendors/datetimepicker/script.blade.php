<script src="{{ asset('vendors/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
<script>
    // Initialize datetimepicker
    $('.input-datepicker').datetimepicker({
        format: 'DD/MM/YYYY',
        ignoreReadonly: true,
        allowInputToggle: true
    });

    $('.input-datetimepicker').datetimepicker({
        format: 'DD/MM/YYYY HH:mm',
        ignoreReadonly: true,
        allowInputToggle: true
    });
</script>