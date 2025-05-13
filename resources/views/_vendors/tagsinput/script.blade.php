<script src="{{ asset('vendors/jquery.tagsinput/src/jquery.tagsinput.js') }}"></script>
<script>
    $(document).ready(function () {
        // Initialize tagsinput
        if (typeof $.fn.tagsInput !== "undefined") {
            $(".tagsinput").tagsInput({
                width: "auto",
            });
        }
    });
</script>