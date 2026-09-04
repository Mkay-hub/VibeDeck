// Main JavaScript file for LightCast application
// Handles DOM interactions, form validation, animations, and UI toggles

document.addEventListener("DOMContentLoaded", function () {
  // Login form validation
  // Select the login form and add submit event listener
  const loginForm = document.querySelector('form[method="POST"]');
  if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
      // Get trimmed values from username and password fields
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      let errors = [];

      // Validate username and password fields
      if (!username) {
        errors.push("Username is required.");
      }
      if (!password) {
        errors.push("Password is required.");
      }

      // If errors exist, prevent form submission and display errors
      if (errors.length > 0) {
        event.preventDefault();
        showErrors(errors);
      } else {
        // Add loading state to submit button
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        submitBtn.textContent = "Logging in...";
        submitBtn.disabled = true;
      }
    });
  }

  // Function to display validation errors dynamically on the page
  // Creates or updates an error div with a list of error messages
  function showErrors(errors) {
    // Check if error div already exists
    let errorDiv = document.querySelector(".errors");
    if (!errorDiv) {
      // Create new error div if it doesn't exist
      errorDiv = document.createElement("div");
      errorDiv.className = "errors";
      // Insert error div before the form in the main card
      const main = document.querySelector("main.card");
      main.insertBefore(errorDiv, main.querySelector("form"));
    }
    // Populate error div with unordered list of errors
    errorDiv.innerHTML =
      '<ul style="margin:0; padding:0 0 0 18px;">' +
      errors.map((e) => `<li>${e}</li>`).join("") +
      "</ul>";
  }

  // Add fade-in animation to the login card on page load
  // Sets initial styles and applies transition after a short delay
  const card = document.querySelector("main.card");
  if (card) {
    // Set initial opacity and transform for animation start
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";
    // Delay to allow DOM to render, then apply fade-in effect
    setTimeout(() => {
      card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, 100);
  }

  // Hamburger menu toggle functionality
  // Toggles the visibility of the navigation menu on mobile devices
  const hamburgerBtn = document.querySelector(".hamburger-btn");
  const menu = document.querySelector(".menu");
  if (hamburgerBtn && menu) {
    // Add click event listener to hamburger button
    hamburgerBtn.addEventListener("click", function () {
      // Toggle 'show' class on menu to display/hide it
      menu.classList.toggle("show");
    });
  }

  // Fade-in animation for main content on pages other than login
  // Applies to main elements that do not have the 'card' class
  const mainContent = document.querySelector("main");
  if (mainContent && !mainContent.classList.contains("card")) {
    // Set initial opacity and transform for animation
    mainContent.style.opacity = "0";
    mainContent.style.transform = "translateY(20px)";
    // Delay to ensure DOM is ready, then animate in
    setTimeout(() => {
      mainContent.style.transition = "opacity 0.5s ease, transform 0.5s ease";
      mainContent.style.opacity = "1";
      mainContent.style.transform = "translateY(0)";
    }, 100);
  }
});
