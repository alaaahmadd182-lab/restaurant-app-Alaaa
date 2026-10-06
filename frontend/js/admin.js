async function checkAdmin() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "login.html";
        return;
    }

    try {
        const response = await apiRequest("/auth/me");
        const user = response.data;

        if (user.role !== "admin") {
            window.location.href = "index.html";
            return;
        }

        loadAdminRestaurants();

    } catch (error) {
        localStorage.removeItem("token");
        window.location.href = "login.html";
    }
}


async function loadAdminRestaurants() {
    const container =
        document.getElementById("admin-restaurants");

    try {
        const response = await apiRequest("/restaurants");
        const restaurants = response.data.restaurants;

        container.innerHTML = "";

        restaurants.forEach((restaurant) => {
            const restaurantBox = document.createElement("div");

            const name = document.createElement("h3");
            name.textContent = restaurant.name;

            const address = document.createElement("p");
            address.textContent = restaurant.address;

            restaurantBox.appendChild(name);
            restaurantBox.appendChild(address);

            container.appendChild(restaurantBox);
        });

    } catch (error) {
        document.getElementById("admin-message").textContent =
            error.message;
    }
}


document
    .getElementById("logout-button")
    .addEventListener("click", () => {
        localStorage.removeItem("token");
        window.location.href = "login.html";
    });


checkAdmin();