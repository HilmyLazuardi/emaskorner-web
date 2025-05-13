<!-- modal for add content element -->
<div class="modal fade modal-add-content-element" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel2">Add Content Element</h4>
            </div>
            <div class="modal-body">
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('masthead', true)"><i class="fa fa-list-alt"></i>&nbsp; Masthead</span><br>
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('text', true)"><i class="fa fa-font"></i>&nbsp; Text</span><br>
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('image', true)"><i class="fa fa-image"></i>&nbsp; Image</span><br>
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('image + text + button', true)"><i class="fa fa-newspaper-o"></i>&nbsp; Image + Text + Button</span><br>
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('video', true)"><i class="fa fa-video-camera"></i>&nbsp; Video</span><br>
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('button', true)"><i class="fa fa-dot-circle-o"></i>&nbsp; Button</span><br>
                <span class="btn btn-primary btn-block" onclick="add_content_element_page('plain', true)"><i class="fa fa-file-text-o"></i>&nbsp; Script</span><br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- modal for loading content element -->
<div class="modal fade modal-content-element-loading" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Loading Contents</h4>
            </div>
            <div class="modal-body">
                <h2 class="text-center">
                    <i class="fa fa-spinner fa-spin"></i>&nbsp; PLEASE WAIT...
                </h2>
            </div>
        </div>
    </div>
</div>