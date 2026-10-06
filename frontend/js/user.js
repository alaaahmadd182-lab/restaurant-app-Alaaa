async function loadCurrentUser() {
    const token = localStorage.getItem("token");

    if (!token) {
        return;
    }

    try {
        const response = await apiRequest("/auth/me");
        const user = response.data;
        const loginLink = document.getElementById("login-link");
        const signupLink = document.getElementById("signup-link");

        if (loginLink) {
            loginLink.hidden = true;
        }

        if (signupLink) {
            signupLink.hidden = true;
        }
        const userArea = document.getElementById("user-area");

        if (!userArea) {
            return;
        }

        userArea.innerHTML = "";

        const welcome = document.createElement("span");
        welcome.textContent = `Welcome, ${user.username}`;

        const logoutButton = document.createElement("button");
        logoutButton.textContent = "Logout";

        logoutButton.addEventListener("click", logout);

        userArea.appendChild(welcome);

        if (user.role === "admin") {
            const adminLink = document.createElement("a");
            adminLink.href = "admin.html";
            adminLink.textContent = "Admin";

            userArea.appendChild(adminLink);
        }

        userArea.appendChild(logoutButton);

    } catch (error) {
        localStorage.removeItem("token");

        const loginLink = document.getElementById("login-link");
        const signupLink = document.getElementById("signup-link");

        if (loginLink) {
            loginLink.hidden = false;
        }

        if (signupLink) {
            signupLink.hidden = false;
        }
    }   
}

function logout() {
    localStorage.removeItem("token");
    window.location.href = "login.html";
}

loadCurrentUser();