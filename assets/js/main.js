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

  /**
   * Absolute URL to submit-form.php. Prefer CHARGED_SUBMIT_FORM from PHP or charged-path.js.
   */
  function getChargedSubmitUrl() {
    var configured = typeof window.CHARGED_SUBMIT_FORM === "string" ? window.CHARGED_SUBMIT_FORM : "";
    if (configured.charAt(0) === "/") {
      return window.location.origin + configured;
    }
    try {
      var u = new URL(window.location.href);
      var path = u.pathname || "/";
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
      u.pathname = dir + "submit-form.php";
      return u.href;
    } catch (err) {
      return "submit-form.php";
    }
  }

  function isChargedFormHttpOrigin() {
    var p = window.location.protocol;
    return p === "http:" || p === "https:";
  }

  var chargedFormCsrf = null;
  function fetchChargedFormCsrf() {
    if (chargedFormCsrf) return Promise.resolve(chargedFormCsrf);
    return fetch(getChargedSubmitUrl() + "?action=csrf", {
      credentials: "same-origin",
      cache: "no-store",
    })
      .then(function (res) {
        if (!res.ok) throw new Error("csrf");
        return res.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !data.token) throw new Error("csrf");
        chargedFormCsrf = data.token;
        return chargedFormCsrf;
      });
  }

  function postChargedSiteForm(payload) {
    return fetchChargedFormCsrf().then(function (token) {
      payload._csrf = token;
      return fetch(getChargedSubmitUrl(), {
        method: "POST",
        credentials: "same-origin",
        cache: "no-store",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      }).then(function (res) {
        return res.text().then(function (text) {
          var body = {};
          try {
            body = text ? JSON.parse(text) : {};
          } catch (ignore) {
            body = { ok: false, error: "Server error." };
          }
          return { httpOk: res.ok, status: res.status, body: body };
        });
      });
    });
  }

  function handleSubscribeSubmit(form, e) {
    e.preventDefault();
    var emailInput = form.querySelector('input[type="email"]');
    var submitBtn = form.querySelector('button[type="submit"]');
    var statusEl = form.querySelector(".ch-form-status");
    var honeypot = form.querySelector('input[name="website"]');
    var email = emailInput ? emailInput.value.trim() : "";

    if (statusEl) {
      statusEl.classList.remove("is-success", "is-error");
      statusEl.textContent = "";
    }

    if (!isChargedFormHttpOrigin()) {
      if (statusEl) {
        statusEl.textContent =
          "Open this site through your web server (for example http://localhost/charged/) so the form can save.";
        statusEl.classList.add("is-error");
      }
      return;
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

    if (submitBtn) submitBtn.disabled = true;

    postChargedSiteForm({
      form_type: "subscribe",
      email: email,
      website: honeypot ? honeypot.value : "",
      source_page: window.location.href,
    })
      .then(function (res) {
        if (res.body && res.body.ok) {
          chargedFormCsrf = null;
          if (statusEl) {
            statusEl.textContent = res.body.message || "You’re on the list. Watch your inbox for the next issue.";
            statusEl.classList.add("is-success");
          }
          emailInput.disabled = true;
          form.classList.add("is-submitted");
        } else {
          var msg = (res.body && res.body.error) || "Something went wrong. Please try again.";
          if (statusEl) {
            statusEl.textContent = msg;
            statusEl.classList.add("is-error");
          }
          if (submitBtn) submitBtn.disabled = false;
        }
      })
      .catch(function () {
        if (statusEl) {
          statusEl.textContent = "Network error. Check your connection and try again.";
          statusEl.classList.add("is-error");
        }
        if (submitBtn) submitBtn.disabled = false;
      });
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

  function initPartnerForm() {
    var form = document.getElementById("partner-form");
    if (!form) return;

    var nameEl = document.getElementById("co-name");
    var emailEl = document.getElementById("co-email");
    var msgEl = document.getElementById("co-msg");
    var honeypot = form.querySelector('input[name="website"]');
    var submitBtn = form.querySelector('button[type="submit"]');
    var statusEl = form.querySelector(".ch-form-status");

    [nameEl, emailEl, msgEl].forEach(function (el) {
      if (!el) return;
      el.addEventListener("input", function () {
        setFieldError(el, "");
        if (statusEl) {
          statusEl.textContent = "";
          statusEl.classList.remove("is-success", "is-error");
        }
      });
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!emailEl || !msgEl) return;

      if (statusEl) {
        statusEl.classList.remove("is-success", "is-error");
        statusEl.textContent = "";
      }
      if (!isChargedFormHttpOrigin()) {
        if (statusEl) {
          statusEl.textContent =
            "Open this site through your web server (for example http://localhost/charged/) so the form can save.";
          statusEl.classList.add("is-error");
        }
        return;
      }
      setFieldError(emailEl, "");
      setFieldError(msgEl, "");

      var name = nameEl ? nameEl.value.trim() : "";
      var email = emailEl.value.trim();
      var message = msgEl.value.trim();

      if (!email) {
        setFieldError(emailEl, "Enter your work email.");
        return;
      }
      if (!EMAIL_RE.test(email)) {
        setFieldError(emailEl, "Use a valid email format.");
        return;
      }
      if (message.length < 10) {
        setFieldError(msgEl, "Please write at least 10 characters so we can help.");
        return;
      }

      if (submitBtn) submitBtn.disabled = true;

      postChargedSiteForm({
        form_type: "partner",
        name: name,
        email: email,
        message: message,
        website: honeypot ? honeypot.value : "",
        source_page: window.location.href,
      })
        .then(function (res) {
          if (res.body && res.body.ok) {
            chargedFormCsrf = null;
            if (statusEl) {
              statusEl.textContent = res.body.message || "Thanks — we received your inquiry.";
              statusEl.classList.add("is-success");
            }
            form.classList.add("is-submitted");
            if (nameEl) nameEl.disabled = true;
            emailEl.disabled = true;
            msgEl.disabled = true;
          } else {
            var msg = (res.body && res.body.error) || "Could not send. Please try again in a moment.";
            if (statusEl) {
              statusEl.textContent = msg;
              statusEl.classList.add("is-error");
            }
            if (submitBtn) submitBtn.disabled = false;
          }
        })
        .catch(function () {
          if (statusEl) {
            statusEl.textContent = "Network error. Check your connection and try again.";
            statusEl.classList.add("is-error");
          }
          if (submitBtn) submitBtn.disabled = false;
        });
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
    initPartnerForm();
    initArchiveFilter();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
