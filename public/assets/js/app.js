document.querySelectorAll("form[data-validate]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    let hasError = false;

    form.querySelectorAll("[data-required], [data-email], [data-minlength]").forEach((field) => {
      const label = field.closest("label");
      const errorNode = label ? label.querySelector(".error-text") : null;
      let message = "";
      const value = (field.value || "").trim();

      if (field.hasAttribute("data-required") && value === "") {
        message = "Ez a mező kötelező.";
      } else if (field.hasAttribute("data-email")) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
          message = "Adj meg érvényes e-mail címet.";
        }
      } else if (field.hasAttribute("data-minlength")) {
        const minimum = Number(field.getAttribute("data-minlength"));
        if (value.length < minimum) {
          message = `Legalább ${minimum} karakter szükséges.`;
        }
      }

      if (errorNode && !errorNode.textContent.trim()) {
        errorNode.textContent = message;
      }

      field.classList.toggle("invalid", message !== "");
      hasError = hasError || message !== "";
    });

    if (hasError) {
      event.preventDefault();
    }
  });
});
