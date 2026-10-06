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


    
async function loadAdminCategories() {
    try {
        const response = await apiRequest("/categories");
        const categories = response.data;

        const select =
            document.getElementById("restaurant-category");

        categories.forEach((category) => {
            const option = document.createElement("option");

            option.value = category.id;
            option.textContent = category.name;

            select.appendChild(option);
        });

    } catch (error) {
        document.getElementById("restaurant-message").textContent =
            error.message;
    }
}


const restaurantForm =
    document.getElementById("restaurant-form");

restaurantForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const message =
        document.getElementById("restaurant-message");

    const latitude =
        document.getElementById("restaurant-latitude").value;

    const longitude =
        document.getElementById("restaurant-longitude").value;

    const restaurant = {
        name:
            document.getElementById("restaurant-name").value.trim(),

        description:
            document.getElementById("restaurant-description").value.trim(),

        address:
            document.getElementById("restaurant-address").value.trim(),

        category_id:
            Number(
                document.getElementById("restaurant-category").value
            ),

        latitude:
            latitude === "" ? null : Number(latitude),

        longitude:
            longitude === "" ? null : Number(longitude),

        image:
            document.getElementById("restaurant-image").value.trim() || null
    };

    try {
        await apiRequest("/restaurants", {
            method: "POST",
            body: JSON.stringify(restaurant)
        });

        message.textContent =
            "Restaurant added successfully.";

        restaurantForm.reset();

        loadAdminRestaurants();

    } catch (error) {
        message.textContent = error.message;
    }
});


loadAdminCategories();
checkAdmin();