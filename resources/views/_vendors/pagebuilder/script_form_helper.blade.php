@if (isset($data_items[0]))
    @php
        $show_loading = false;
    @endphp

    @foreach($data_items as $item) 
        // SET JSON OBJECT DATA >> data = question_text, response_wording, question_media, question_src, option_other, point_per_item, option_answer_index, is_required, checkpoint_status
        var the_data = '{ "question_text":"{{ $item->question_text }}", "response_wording":"{{ $item->response_wording }}", "question_media":"{{ $item->question_media }}", "question_src":"{{ $item->question_src }}", "option_other":"{{ $item->option_other }}", "point_per_item":"{{ $item->point_per_item }}", "option_answer_index":"{{ $item->option_answer_index }}", "is_required":"{{ $item->is_required }}", "checkpoint_status":"{{ $item->checkpoint_status }}" }';

        var options_text = '';
        @if (!empty($item->options_text))
            @php
                $options = [];
                $json_options = json_decode($item->options_text);
                foreach ($json_options as $opt) {
                    $options[] = json_encode($opt);
                }
                $options_text = implode(', ', $options);
            @endphp
            // SET JSON ARRAY OPTIONS
            options_text = [<?php echo $options_text; ?>];
        @endif

        var options_media = '';
        @if (!empty($item->options_media))
            @php
                $options = [];
                $json_options = json_decode($item->options_media);
                foreach ($json_options as $opt) {
                    $options[] = json_encode($opt);
                }
                $options_media = implode(', ', $options);
            @endphp
            // SET JSON ARRAY OPTIONS
            options_media = [<?php echo $options_media; ?>];
        @endif

        @php
            // generate identifier
            $identifier = time() + $item->ordinal;

            $form_type = $data->type;
            $question_type = $item->question_type;
            $option_type = $item->option_type;

            switch ($option_type) {
                case 'image':
                    echo "add_question('".$form_type."', '".$question_type."', '".$option_type."', the_data, options_text, options_media, ".$enable_other_option.", ".$collapsed.", '".$identifier."');";
                    break;
            
                default:
                    // text
                    echo "add_question('".$form_type."', '".$question_type."', '".$option_type."', the_data, options_text, options_media, ".$enable_other_option.", ".$collapsed.", '".$identifier."');";
                    break;
            }
        @endphp
    @endforeach
@endif