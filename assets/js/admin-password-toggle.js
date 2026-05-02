/**
 * Show / hide password fields inside .ch-password-wrap (admin login + profile).
 */
(function () {
  "use strict";

  function wire(wrap) {
    var input = wrap.querySelector("input");
    if (!input || wrap.querySelector(".ch-password-wrap__toggle")) {
      return;
    }
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "ch-password-wrap__toggle";
    btn.setAttribute("aria-label", "Show password");
    btn.setAttribute("aria-pressed", "false");
    btn.textContent = "Show";
    btn.addEventListener("click", function () {
      var hidden = input.type === "password";
      input.type = hidden ? "text" : "password";
      btn.textContent = hidden ? "Hide" : "Show";
      btn.setAttribute("aria-label", hidden ? "Hide password" : "Show password");
      btn.setAttribute("aria-pressed", hidden ? "true" : "false");
    });
    wrap.appendChild(btn);
  }

  function init() {
    document.querySelectorAll(".ch-password-wrap").forEach(wire);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
