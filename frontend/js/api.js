const API_URL = "/backend/api";

async function apiRequest(endpoint, options = {}) {
    const token = localStorage.getItem("token");

    const headers = {
        "Content-Type": "application/json",
        ...options.headers
    };

    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_URL}${endpoint}`, {
        ...options,
        headers
    });

    const data = await response.json();
    if (response.status === 401 && token) {
        localStorage.removeItem("token");

        if (
            !window.location.pathname.endsWith("/login.html") &&
            !window.location.pathname.endsWith("/signup.html") &&
            !window.location.pathname.endsWith("/otp.html")
        ) {
            window.location.href = "login.html";
        }

        throw new Error("Your session has expired. Please log in again.");
    }
    if (!response.ok) {
        throw new Error(data.message || "Something went wrong.");
    }

    return data;
}