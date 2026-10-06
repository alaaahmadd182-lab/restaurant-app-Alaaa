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





const loginForm = document.getElementById("login-form");

if (loginForm) {
    loginForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const email =
            document.getElementById("login-email").value.trim();

        const password =
            document.getElementById("login-password").value;

        const message = document.getElementById("message");

        try {
            const response = await apiRequest("/auth/login", {
                method: "POST",

                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });

            sessionStorage.setItem("otp_email", email);

            // Temporary development-only OTP
            sessionStorage.setItem(
                "development_otp",
                response.data.development_otp
            );

            window.location.href = "otp.html";

        } catch (error) {
            message.textContent = error.message;
        }
    });
}



const otpForm = document.getElementById("otp-form");

if (otpForm) {
    const email = sessionStorage.getItem("otp_email");
    const developmentOtp =
        sessionStorage.getItem("development_otp");

    const developmentCode =
        document.getElementById("development-code");

    // Development only
    if (developmentOtp) {
        developmentCode.textContent =
            `Development OTP: ${developmentOtp}`;
    }

    otpForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const otp =
            document.getElementById("otp").value.trim();

        const message =
            document.getElementById("message");

        if (!email) {
            message.textContent =
                "Login session missing. Please login again.";
            return;
        }

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
            sessionStorage.removeItem("development_otp");

            window.location.href = "index.html";

        } catch (error) {
            message.textContent = error.message;
        }
    });
}