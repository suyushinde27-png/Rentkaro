/**
 * nav-auth.js
 * Runs on every page. Calls backend/session.php to check login status,
 * then swaps the nav-actions block so logged-in users always see
 * "Dashboard" + "Logout" instead of "Log In" + "Sign Up".
 *
 * Works regardless of how deep the page is (root vs subfolder) because
 * it reads data-root from the <script> tag itself.
 */
(function () {
  // Find this script tag so we can read its data-root attribute.
  // data-root is the relative path from the current page back to the
  // project root, e.g. "" for root pages, "../" for pages one level deep.
  const scriptTag = document.currentScript ||
    document.querySelector('script[src*="nav-auth.js"]');
  const root = scriptTag ? (scriptTag.dataset.root || "") : "";

  const sessionUrl = root + "backend/session.php";
  const logoutUrl  = root + "backend/logout.php";

  fetch(sessionUrl, { credentials: "same-origin" })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      const navActions = document.querySelector(".nav-actions");
      if (!navActions) return;

      if (data.success) {
        // ── Logged in ─────────────────────────────────────────────────
        navActions.innerHTML =
          '<a class="nav-link-btn" href="' + root + 'dashboard.html">♙ &nbsp;Dashboard</a>' +
          (data.is_admin
            ? '<a class="nav-link-btn" href="' + root + 'admin-dashboard.html">⚙ Admin</a>'
            : '') +
          '<a class="login-btn" href="' + logoutUrl + '">Logout</a>';

        // Also inject Dashboard link into nav-links if not already there
        const navLinks = document.querySelector(".nav-links");
        if (navLinks && !navLinks.querySelector('a[href*="dashboard"]')) {
          const li = document.createElement("li");
          li.innerHTML = '<a href="' + root + 'dashboard.html">Dashboard</a>';
          navLinks.appendChild(li);
        }
      } else {
        // ── Not logged in ─────────────────────────────────────────────
        // Leave the existing Login/Sign Up buttons as-is,
        // but make sure the hrefs point to the right place from this page.
        const loginBtn = navActions.querySelector('a[href*="login"]');
        const signupBtn = navActions.querySelector('a[href*="signup"]');
        if (loginBtn)  loginBtn.href  = root + "login.html";
        if (signupBtn) signupBtn.href = root + "signup.html";
      }
    })
    .catch(function () {
      // Server not reachable — leave navbar as-is, don't break the page
    });
})();
