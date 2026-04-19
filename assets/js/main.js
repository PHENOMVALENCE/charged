/**
 * CHARGED — site behavior (vanilla JS)
 * Theme, sticky header, mobile nav, subscribe validation, smooth scroll, archive filter
 */
(function () {
  "use strict";

  var THEME_KEY = "charged-theme";
  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function getPreferredTheme() {
    var stored = localStorage.getItem(THEME_KEY);
    if (stored === "light" || stored === "dark") return stored;
    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      return "dark";
    }
    return "light";
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem(THEME_KEY, theme);
    var toggle = document.getElementById("theme-toggle");
    if (toggle) {
      toggle.setAttribute("aria-label", theme === "dark" ? "Switch to light mode" : "Switch to dark mode");
    }
  }

  function initTheme() {
    applyTheme(getPreferredTheme());
    var toggle = document.getElementById("theme-toggle");
    if (toggle) {
      toggle.addEventListener("click", function () {
        var next = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        applyTheme(next);
      });
    }
  }

  function initHeaderScroll() {
    var header = document.querySelector(".ch-header");
    if (!header) return;
    function onScroll() {
      if (window.scrollY > 8) header.classList.add("is-scrolled");
      else header.classList.remove("is-scrolled");
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  function initMobileNav() {
    var btn = document.getElementById("nav-toggle");
    var panel = document.getElementById("mobile-nav");
    if (!btn || !panel) return;

    function setOpen(open) {
      btn.setAttribute("aria-expanded", open ? "true" : "false");
      panel.classList.toggle("is-open", open);
      panel.setAttribute("aria-hidden", open ? "false" : "true");
      document.body.style.overflow = open ? "hidden" : "";
    }

    btn.addEventListener("click", function () {
      var open = btn.getAttribute("aria-expanded") !== "true";
      setOpen(open);
    });

    panel.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        setOpen(false);
      });
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") setOpen(false);
    });
  }

  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
      var id = anchor.getAttribute("href");
      if (!id || id === "#") return;
      anchor.addEventListener("click", function (e) {
        var target = document.querySelector(id);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: "smooth", block: "start" });
          history.pushState(null, "", id);
        }
      });
    });
  }

  function setFieldError(field, message) {
    var wrap = field.closest(".ch-field");
    if (!wrap) return;
    var err = wrap.querySelector(".ch-field-error");
    if (message) {
      wrap.classList.add("is-error");
      if (err) err.textContent = message;
    } else {
      wrap.classList.remove("is-error");
      if (err) err.textContent = "";
    }
  }

  function handleSubscribeSubmit(form, e) {
    e.preventDefault();
    var emailInput = form.querySelector('input[type="email"]');
    var submitBtn = form.querySelector('button[type="submit"]');
    var statusEl = form.querySelector(".ch-form-status");
    var email = emailInput ? emailInput.value.trim() : "";

    if (statusEl) {
      statusEl.classList.remove("is-success", "is-error");
      statusEl.textContent = "";
    }

    if (!emailInput) return;

    setFieldError(emailInput, "");

    if (!email) {
      setFieldError(emailInput, "Enter your email address.");
      return;
    }
    if (!EMAIL_RE.test(email)) {
      setFieldError(emailInput, "Use a valid email format.");
      return;
    }

    /*
     * TODO: Integrate with Beehiiv, Mailchimp, ConvertKit, or your API.
     * Example: POST to /api/subscribe with JSON { email, source: 'charged-site' }
     * On success: show confirmation; on 4xx/5xx: show statusEl with is-error.
     */
    if (statusEl) {
      statusEl.textContent = "You’re on the list. Watch your inbox for the next issue.";
      statusEl.classList.add("is-success");
    }

    emailInput.disabled = true;
    if (submitBtn) submitBtn.disabled = true;
    form.classList.add("is-submitted");
  }

  function initSubscribeForms() {
    document.querySelectorAll(".ch-subscribe-form").forEach(function (form) {
      form.addEventListener("submit", function (e) {
        handleSubscribeSubmit(form, e);
      });
      var emailInput = form.querySelector('input[type="email"]');
      if (emailInput) {
        emailInput.addEventListener("input", function () {
          setFieldError(emailInput, "");
        });
      }
    });
  }

  function initArchiveFilter() {
    var root = document.querySelector("[data-archive-filter]");
    if (!root) return;
    var chips = root.querySelectorAll(".ch-chip");
    var items = root.querySelectorAll("[data-category]");

    chips.forEach(function (chip) {
      chip.addEventListener("click", function () {
        var cat = chip.getAttribute("data-filter") || "all";
        chips.forEach(function (c) {
          c.classList.toggle("is-active", c === chip);
        });
        items.forEach(function (item) {
          var ic = item.getAttribute("data-category") || "";
          if (cat === "all" || ic === cat) item.classList.remove("is-hidden");
          else item.classList.add("is-hidden");
        });
      });
    });
  }

  function init() {
    initTheme();
    initHeaderScroll();
    initMobileNav();
    initSmoothScroll();
    initSubscribeForms();
    initArchiveFilter();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
