const signupForm = document.getElementById("signup-form");

if (signupForm) {
    signupForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        const submitButton =
            signupForm.querySelector("button[type='submit']");
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
        const originalButtonText = submitButton.textContent;

        submitButton.disabled = true;
        submitButton.textContent = "Creating account...";

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

        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
}





const loginForm = document.getElementById("login-form");

if (loginForm) {
    loginForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        const submitButton =
            loginForm.querySelector("button[type='submit']");
        const email =
            document.getElementById("login-email").value.trim();

        const password =
            document.getElementById("login-password").value;

        const message = document.getElementById("message");
        const originalButtonText = submitButton.textContent;

        submitButton.disabled = true;
        submitButton.textContent = "Logging in...";
        try {
            const response = await apiRequest("/auth/login", {
                method: "POST",

                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });

            sessionStorage.setItem("otp_email", email);

            window.location.href = "otp.html";

        } catch (error) {
            message.textContent = error.message;

        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
}



const otpForm = document.getElementById("otp-form");

if (otpForm) {
    const email = sessionStorage.getItem("otp_email");

    otpForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const submitButton =
            otpForm.querySelector("button[type='submit']");

        const otp =
            document.getElementById("otp").value.trim();

        const message =
            document.getElementById("message");

        if (!email) {
            message.textContent =
                "Login session missing. Please login again.";
            return;
        }

        if (!/^\d{6}$/.test(otp)) {
            message.textContent =
                "Please enter the 6-digit verification code.";
            return;
        }

        const originalButtonText = submitButton.textContent;

        submitButton.disabled = true;
        submitButton.textContent = "Verifying...";

        try {
            const response = await apiRequest(
                "/auth/verify-otp",
                {
                    method: "POST",
                    body: JSON.stringify({
                        email: email,
                        otp: otp
                    })
                }
            );

            localStorage.setItem(
                "token",
                response.data.token
            );

            sessionStorage.removeItem("otp_email");

            window.location.href = "index.html";

        } catch (error) {
            message.textContent = error.message;

        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
}