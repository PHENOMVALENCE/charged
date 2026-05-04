/**
 * Client-side previews for article featured + gallery file inputs (before save).
 */
(function () {
  function revoke(url) {
    if (url) {
      URL.revokeObjectURL(url);
    }
  }

  function isImageFile(file) {
    return file && file.type && file.type.indexOf("image/") === 0;
  }

  function init() {
    var featuredIn = document.getElementById("featured_image");
    var featuredBox = document.getElementById("featured_image_preview");
    var galleryIn = document.getElementById("gallery_images");
    var galleryBox = document.getElementById("gallery_images_preview");

    var featuredRevoke = null;
    var galleryRevokes = [];

    function clearFeaturedPreview() {
      revoke(featuredRevoke);
      featuredRevoke = null;
      if (featuredBox) {
        featuredBox.innerHTML = "";
        featuredBox.hidden = true;
      }
    }

    function clearGalleryPreview() {
      galleryRevokes.forEach(revoke);
      galleryRevokes = [];
      if (galleryBox) {
        galleryBox.innerHTML = "";
        galleryBox.hidden = true;
      }
    }

    if (featuredIn && featuredBox) {
      featuredIn.addEventListener("change", function () {
        clearFeaturedPreview();
        var f = featuredIn.files && featuredIn.files[0];
        if (!isImageFile(f)) {
          return;
        }
        featuredRevoke = URL.createObjectURL(f);
        var title = document.createElement("p");
        title.className = "ch-admin-file-preview__title";
        title.textContent = "New selection preview (not saved yet)";
        var frame = document.createElement("div");
        frame.className = "ch-admin-file-preview__frame ch-admin-file-preview__frame--featured";
        var img = document.createElement("img");
        img.src = featuredRevoke;
        img.alt = "Preview of selected featured image";
        img.className = "ch-admin-file-preview__img ch-admin-file-preview__img--featured";
        frame.appendChild(img);
        var cap = document.createElement("p");
        cap.className = "ch-admin-file-preview__caption";
        cap.textContent = f.name;
        featuredBox.appendChild(title);
        featuredBox.appendChild(frame);
        featuredBox.appendChild(cap);
        featuredBox.hidden = false;
      });
    }

    if (galleryIn && galleryBox) {
      galleryIn.addEventListener("change", function () {
        clearGalleryPreview();
        var files = galleryIn.files;
        if (!files || !files.length) {
          return;
        }
        var grid = document.createElement("div");
        grid.className = "ch-admin-file-preview__grid";
        for (var i = 0; i < files.length; i++) {
          var file = files[i];
          if (!isImageFile(file)) {
            continue;
          }
          var u = URL.createObjectURL(file);
          galleryRevokes.push(u);
          var cell = document.createElement("div");
          cell.className = "ch-admin-file-preview__cell";
          var thumbWrap = document.createElement("div");
          thumbWrap.className = "ch-admin-file-preview__thumb-wrap";
          var im = document.createElement("img");
          im.src = u;
          im.alt = "";
          im.className = "ch-admin-file-preview__thumb";
          thumbWrap.appendChild(im);
          var nm = document.createElement("span");
          nm.className = "ch-admin-file-preview__name";
          nm.textContent = file.name;
          cell.appendChild(thumbWrap);
          cell.appendChild(nm);
          grid.appendChild(cell);
        }
        if (!grid.childElementCount) {
          return;
        }
        var gtitle = document.createElement("p");
        gtitle.className = "ch-admin-file-preview__title";
        gtitle.textContent = "New selection preview (not saved yet)";
        galleryBox.appendChild(gtitle);
        galleryBox.appendChild(grid);
        galleryBox.hidden = false;
      });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
