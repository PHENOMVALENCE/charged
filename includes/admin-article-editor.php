<?php
/**
 * TinyMCE rich editor for article body (create + edit pages).
 * Include only when $charged_include_article_editor is true (before footer.php).
 * Form must use id="article-editor-form" and textarea id="content".
 */

declare(strict_types=1);

?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
  "use strict";
  function initEditor() {
    if (typeof tinymce === "undefined") return;
    tinymce.init({
      selector: "#content",
      height: 480,
      min_height: 360,
      menubar: false,
      branding: false,
      promotion: false,
      license_key: "gpl",
      plugins: "lists link autoresize code table",
      toolbar:
        "undo redo | blocks | bold italic underline strikethrough | " +
        "bullist numlist | outdent indent | link blockquote hr | table | code removeformat",
      block_formats: "Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4",
      link_default_protocol: "https",
      link_assume_external_targets: true,
      link_target_list: [
        { title: "Same window", value: "" },
        { title: "New window", value: "_blank" }
      ],
      rel_list: [
        { title: "Default", value: "" },
        { title: "nofollow", value: "nofollow" },
        { title: "nofollow noopener", value: "nofollow noopener noreferrer" }
      ],
      table_toolbar: "tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol",
      table_resize_bars: true,
      table_default_attributes: { border: "0" },
      table_default_styles: { "border-collapse": "collapse", width: "100%" },
      content_style:
        "body{font-family:Sora,system-ui,sans-serif;font-size:16px;line-height:1.7;color:#141410;padding:0.5rem 0.25rem}" +
        "h2,h3,h4{font-family:'Playfair Display',Georgia,serif;margin:1.25em 0 0.5em;line-height:1.2}" +
        "blockquote{border-left:3px solid #b87a0f;margin:1rem 0;padding-left:1rem;color:#5a5850}" +
        "table{border-collapse:collapse;width:100%;margin:1rem 0}" +
        "th,td{border:1px solid rgba(20,20,16,0.15);padding:0.5rem 0.65rem;text-align:left}" +
        "th{background:#f0ebe2;font-weight:600}",
      setup: function (editor) {
        editor.on("change input undo redo", function () {
          editor.save();
        });
      }
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initEditor();
    var form = document.getElementById("article-editor-form");
    if (form) {
      form.addEventListener("submit", function () {
        if (window.tinymce && tinymce.triggerSave) {
          tinymce.triggerSave();
        }
      });
    }
  });
})();
</script>
