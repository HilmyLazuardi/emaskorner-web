{{-- GENERATE PAGEBUILDER CONTENT ELEMENT FROM DATA --}}
@if (!empty($data->content))
    @php
        $show_loading = false;
        $content = json_decode($data->content);
    @endphp
    @foreach ($content as $key => $value)
        // Button "Add Section" clicked
        var v_page_section_id = '{{ $key }}';
        var v_page_section_data = {
            v_page_section : encodeURI("{{ $value->v_page_section }}"), 
            v_page_section_style : "{{ $value->v_page_section_style }}"
        };
        add_section_element(true, v_page_section_id, v_page_section_data);

        // Button "Add Content Element" clicked
        set_content_container('#list-section-' + v_page_section_id);
        set_section_page_id(v_page_section_id);

        // Add Content Elements
        @foreach ($value->contents as $key2 => $value2)
            var uniqid_element = '{{ $key2 }}';

            @if ($value2->v_page_element_type == 'masthead')
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}"
                };
                // Add Element "Masthead"
                add_content_element_page('masthead', true, uniqid_element, v_page_element_data);

                @if (!empty($value2->items))
                    @foreach ($value2->items as $key3 => $value3)
                        @php
                            $pb_text_title = '';
                            if (!empty($value3->v_page_element_title)) {
                                $pb_text = json_encode($value3->v_page_element_title);
                                $pb_text_title = substr($pb_text, 1, strlen($pb_text)-2);
                            }

                            $pb_text_subtitle = '';
                            if (!empty($value3->v_page_element_subtitle)) {
                                $pb_text = json_encode($value3->v_page_element_subtitle);
                                $pb_text_subtitle = substr($pb_text, 1, strlen($pb_text)-2);
                            }

                            $title_style = 'White';
                            if (!empty($value3->v_page_element_title_style)) {
                                $title_style = $value3->v_page_element_title_style;
                            }

                            $subtitle_style = 'White';
                            if (!empty($value3->v_page_element_subtitle_style)) {
                                $subtitle_style = $value3->v_page_element_subtitle_style;
                            }

                            if (!isset($value3->v_page_element_image_link_type)) {
                                $value3->v_page_element_image_link_type = '';
                            }
                            if (!isset($value3->v_page_element_image_link_internal)) {
                                $value3->v_page_element_image_link_internal = '';
                            }
                            if (!isset($value3->v_page_element_image_link_external)) {
                                $value3->v_page_element_image_link_external = '';
                            }
                            if (!isset($value3->v_page_element_image_link_target)) {
                                $value3->v_page_element_image_link_target = '';
                            }
                        @endphp
                        var uniqid_item = '{{ $key3 }}';
                        var v_page_element_item_data = {
                            v_page_element_image_title : encodeURI("{{ $value3->v_page_element_image_title }}"), 
                            v_page_element_title : encodeURI("{{ $pb_text_title }}"), 
                            v_page_element_title_style : "{{ $title_style }}",
                            v_page_element_subtitle : encodeURI("{{ $pb_text_subtitle }}"), 
                            v_page_element_subtitle_style : "{{ $subtitle_style }}",
                            v_page_element_alignment : "{{ $value3->v_page_element_alignment }}",
                            v_page_element_image_link_type : "{{ $value3->v_page_element_image_link_type }}",
                            v_page_element_image_link_internal : "{{ $value3->v_page_element_image_link_internal }}",
                            v_page_element_image_link_external : "{{ $value3->v_page_element_image_link_external }}",
                            v_page_element_image_link_target : "{{ $value3->v_page_element_image_link_target }}",
                            v_page_element_image : "{{ $value3->v_page_element_image }}",
                            v_page_element_image_mobile : "{{ $value3->v_page_element_image_mobile }}",
                            v_page_element_status_item : "{{ $value3->v_page_element_status_item }}"
                        };
                        var v_page_element_sub_item_data = [];
                        @if (!empty($value3->sub_items))
                            @foreach ($value3->sub_items as $value4)
                                v_page_sub_item = {
                                    v_page_element_button_link_type : "{{ $value4->v_page_element_button_link_type }}",
                                    v_page_element_button_link_internal : "{{ $value4->v_page_element_button_link_internal }}",
                                    v_page_element_button_link_external : "{{ $value4->v_page_element_button_link_external }}",
                                    v_page_element_button_link_target : "{{ $value4->v_page_element_button_link_target }}",
                                    v_page_element_button_label : "{{ $value4->v_page_element_button_label }}",
                                    v_page_element_button_style : "{{ $value4->v_page_element_button_style }}"
                                };
                                v_page_element_sub_item_data.push(v_page_sub_item);
                            @endforeach
                        @endif
                        // Button "Add Item" clicked
                        add_masthead_item(v_page_section_id, uniqid_element, uniqid_item, v_page_element_item_data, v_page_element_sub_item_data);
                    @endforeach
                @endif
            @elseif ($value2->v_page_element_type == 'text')
                @php
                    $show_loading = true;
                @endphp
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}",
                    v_page_element_width : "{{ $value2->v_page_element_width }}",
                    v_page_element_alignment : "{{ $value2->v_page_element_alignment }}",
                    v_page_element_total_item : "{{ $value2->v_page_element_total_item }}"
                };
                @if (!empty($value2->items))
                    var v_page_element_item_data = [];
                    @foreach ($value2->items as $key3 => $value3)
                        @php
                            $pb_text = '';
                            if (!empty($value3)) {
                                $pb_text = json_encode($value3);
                                $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                            }
                        @endphp
                        v_page_element_item_data.push(encodeURI("{{ $pb_text }}"));
                    @endforeach
                    v_page_element_data.items = v_page_element_item_data;
                @endif
                // Add Element "Text"
                add_content_element_page('text', true, uniqid_element, v_page_element_data);
            @elseif ($value2->v_page_element_type == 'image')
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}",
                    v_page_element_image_type : "{{ $value2->v_page_element_image_type }}"
                };
                // Add Element "Image"
                add_content_element_page('image', true, uniqid_element, v_page_element_data);

                @if (!empty($value2->items))
                    @foreach ($value2->items as $key3 => $value3)
                        var uniqid_item = '{{ $key3 }}';
                        var v_page_element_item_data = {
                            v_page_element_image_title : encodeURI("{{ $value3->v_page_element_image_title }}"), 
                            v_page_element_link_type : "{{ $value3->v_page_element_link_type }}",
                            v_page_element_link_internal : encodeURI("{{ $value3->v_page_element_link_internal }}"), 
                            v_page_element_link_external : encodeURI("{{ $value3->v_page_element_link_external }}"), 
                            v_page_element_link_target : "{{ $value3->v_page_element_link_target }}",
                            v_page_element_status_item : "{{ $value3->v_page_element_status_item }}",
                            v_page_element_image : "{{ $value3->v_page_element_image }}"
                        };
                        // Button "Add Item" clicked
                        add_image_item(v_page_section_id, uniqid_element, uniqid_item, v_page_element_item_data);
                        // Show Link Properties
                        show_input_link('', uniqid_item);
                    @endforeach
                @endif
            @elseif ($value2->v_page_element_type == 'image + text + button')
                @php
                    $show_loading = true;
                @endphp
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}",
                    v_page_element_alignment : "{{ $value2->v_page_element_alignment }}",
                    v_page_element_total_item : "{{ $value2->v_page_element_total_item }}"
                };
                @if (!empty($value2->items))
                    var v_page_element_item_data = [];
                    @foreach ($value2->items as $key3 => $value3)
                        @php
                            $pb_text = '';
                            if (!empty($value3->v_page_element_text)) {
                                $pb_text = json_encode($value3->v_page_element_text);
                                $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                            }
                        @endphp
                        var v_page_item = {
                            v_page_element_image_title : encodeURI("{{ $value3->v_page_element_image_title }}"), 
                            v_page_element_image : "{{ $value3->v_page_element_image }}",
                            v_page_element_text : encodeURI("{{ $pb_text }}"),
                            v_page_element_button_link_type : "{{ $value3->v_page_element_button_link_type }}",
                            v_page_element_button_link_internal : encodeURI("{{ $value3->v_page_element_button_link_internal }}"),
                            v_page_element_button_link_external : encodeURI("{{ $value3->v_page_element_button_link_external }}"),
                            v_page_element_button_link_target : "{{ $value3->v_page_element_button_link_target }}",
                            v_page_element_button_label : encodeURI("{{ $value3->v_page_element_button_label }}"),
                            v_page_element_button_style : "{{ $value3->v_page_element_button_style }}"
                        };
                        v_page_element_item_data.push(v_page_item);
                    @endforeach
                    v_page_element_data.items = v_page_element_item_data;
                @endif
                // Add Element "Image + Text + Button"
                add_content_element_page('image + text + button', true, uniqid_element, v_page_element_data);
            @elseif ($value2->v_page_element_type == 'video')
                @php
                    $pb_text = '';
                    if (!empty($value2->v_page_element_text)) {
                        $pb_text = json_encode($value2->v_page_element_text);
                        $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                    }
                @endphp
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}",
                    v_page_element_alignment : "{{ $value2->v_page_element_alignment }}",
                    v_page_element_video_type : "{{ $value2->v_page_element_video_type }}",
                    v_page_element_video_title : "{{ $value2->v_page_element_video_title }}",
                    v_page_element_video : encodeURI("{{ $value2->v_page_element_video }}"),
                    v_page_element_text : encodeURI("{{ $pb_text }}"),
                    v_page_element_button_link_type : "{{ $value2->v_page_element_button_link_type }}",
                    v_page_element_button_link_internal : encodeURI("{{ $value2->v_page_element_button_link_internal }}"),
                    v_page_element_button_link_external : encodeURI("{{ $value2->v_page_element_button_link_external }}"),
                    v_page_element_button_link_target : "{{ $value2->v_page_element_button_link_target }}",
                    v_page_element_button_label : encodeURI("{{ $value2->v_page_element_button_label }}"),
                    v_page_element_button_style : "{{ $value2->v_page_element_button_style }}"
                };
                // Add Element "Video"
                add_content_element_page('video', true, uniqid_element, v_page_element_data);

                show_input_video_text('', uniqid_element);
                show_input_link('', uniqid_element);
            @elseif ($value2->v_page_element_type == 'button')
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}",
                    v_page_element_alignment : "{{ $value2->v_page_element_alignment }}"
                };
                // Add Element "Button"
                add_content_element_page('button', true, uniqid_element, v_page_element_data);

                @if (!empty($value2->items))
                    @foreach ($value2->items as $key3 => $value3)
                        var uniqid_item = '{{ $key3 }}';
                        var v_page_element_item_data = {
                            v_page_element_button_label : encodeURI("{{ $value3->v_page_element_button_label }}"), 
                            v_page_element_button_style : "{{ $value3->v_page_element_button_style }}",
                            v_page_element_button_link_type : "{{ $value3->v_page_element_button_link_type }}",
                            v_page_element_button_link_internal : encodeURI("{{ $value3->v_page_element_button_link_internal }}"), 
                            v_page_element_button_link_external : encodeURI("{{ $value3->v_page_element_button_link_external }}"), 
                            v_page_element_button_link_target : "{{ $value3->v_page_element_button_link_target }}",
                            v_page_element_status_item : "{{ $value3->v_page_element_status_item }}"
                        };
                        // Button "Add Item" clicked
                        add_button_item(v_page_section_id, uniqid_element, uniqid_item, v_page_element_item_data);
                        // Show Link Properties
                        show_input_link('', uniqid_item);
                    @endforeach
                @endif
            @elseif ($value2->v_page_element_type == 'plain')
                @php
                    $pb_text = '';
                    if (!empty($value2->v_page_element_text)) {
                        $pb_text = json_encode($value2->v_page_element_text);
                        $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                    }
                @endphp
                var v_page_element_data = {
                    v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                    v_page_element_status : "{{ $value2->v_page_element_status }}",
                    v_page_element_text : encodeURI("{{ $pb_text }}")
                };
                // Add Element "Plain"
                add_content_element_page('plain', true, uniqid_element, v_page_element_data);
            @endif
        @endforeach
    @endforeach

    @if ($show_loading)
        show_loading_pagebuilder();
    @endif
@endif