/**
 * Sets window.CHARGED_SUBMIT_FORM (path starting with /) when not already set by PHP.
 * Ensures static .html pages POST to the same-directory submit-form.php for every URL shape.
 */
(function () {
  "use strict";
  if (typeof window.CHARGED_SUBMIT_FORM === "string" && window.CHARGED_SUBMIT_FORM.charAt(0) === "/") {
    return;
  }
  try {
    var path = window.location.pathname || "/";
    var dir;
    if (path.endsWith("/")) {
      dir = path;
    } else {
      var lastSeg = path.split("/").pop() || "";
      if (/\.[a-z0-9]+$/i.test(lastSeg)) {
        dir = path.replace(/[^/]*$/, "");
      } else {
        dir = path + "/";
      }
    }
    window.CHARGED_SUBMIT_FORM = dir + "submit-form.php";
  } catch (ignore) {}
})();
