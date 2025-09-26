if ($(".email_body").length > 0) {
    tinymce.init({
        selector: "textarea.email_body",
        theme: "modern",
        height: 250,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        plugins: [
            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
            "save table contextmenu directionality emoticons template paste textcolor",
            "contextmenu",
        ],
        contextmenu: "removeimage",
        setup: function(editor) {
            editor.addMenuItem("removeimage", {
                text: "Remove Image",
                icon: "image",
                context: "img",
                onclick: function() {
                    var selectedNode = editor.selection.getNode();
                    if (selectedNode.nodeName === "IMG") {
                        var imageUrl = selectedNode.src; // Get the image URL

                        var imageKey = imageUrl.split("/").pop();

                        $.ajax({
                            url: "/admin/ckeditor/imageRemove",
                            method: "POST",
                            data: {
                                imageKey: imageKey
                            },
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                            },
                            success: function(response) {
                                editor.dom.remove(selectedNode);
                            },
                            error: function() {
                                // Handle error case
                                console.log("Error deleting image");
                            },
                        });
                    }
                },
            });
        },
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | fontsizeselect formatselect | fontselect | print preview media fullpage | forecolor backcolor emoticons",

        fontsize_formats: "8px 10px 12px 14px 18px 24px 36px",
        font_formats: `
Arial=arial,helvetica,sans-serif;
Arial Black=arial black,avant garde;
Book Antiqua=book antiqua,palatino;
Comic Sans MS=comic sans ms,sans-serif;
Courier New=courier new,courier,monospace;
Georgia=georgia,times new roman,times,serif;
Impact=impact,chicago;
Tahoma=tahoma,arial,helvetica,sans-serif;
Times New Roman=times new roman,times,serif;
Trebuchet MS=trebuchet ms,geneva;
Verdana=verdana,geneva,sans-serif;
Symbol=symbol;
Webdings=webdings;
Wingdings=wingdings,zapf dingbats;
MS Sans Serif=ms sans serif;
MS Serif=ms serif`,
        images_upload_url: "/admin/ckeditor/imageupload", // Endpoint for image upload
        images_upload_handler: function(blobInfo, success, failure) {
            var formData = new FormData();
            formData.append("file", blobInfo.blob(), blobInfo.filename());

            $.ajax({
                url: "/admin/ckeditor/imageupload",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function(data) {
                    success(data.location);
                },
                error: function() {
                    failure("Image upload failed");
                },
            });
        },
        style_formats: [{
                title: "Bold text",
                inline: "b"
            },
            {
                title: "Red text",
                inline: "span",
                styles: {
                    color: "#ff0000"
                }
            },
            {
                title: "Red header",
                block: "h1",
                styles: {
                    color: "#ff0000"
                }
            },
            {
                title: "Example 1",
                inline: "span",
                classes: "example1"
            },
            {
                title: "Example 2",
                inline: "span",
                classes: "example2"
            },
            {
                title: "Table styles"
            },
            {
                title: "Table row 1",
                selector: "tr",
                classes: "tablerow1"
            },
        ],
    });
}
