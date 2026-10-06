const signupForm = document.getElementById("signup-form");

if (signupForm) {
    signupForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const firstName =
            document.getElementById("first-name").value.trim();

        const lastName =
            document.getElementById("last-name").value.trim();

        const username =
            document.getElementById("username").value.trim();

        const email =
            document.getElementById("email").value.trim();

        const password =
            document.getElementById("password").value;

        const message = document.getElementById("message");

        try {
            await apiRequest("/auth/signup", {
                method: "POST",

                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    username: username,
                    email: email,
                    password: password
                })
            });

            message.textContent =
                "Account created successfully. Redirecting to login...";

            setTimeout(() => {
                window.location.href = "login.html";
            }, 1000);

        } catch (error) {
            message.textContent = error.message;
        }
    });
}