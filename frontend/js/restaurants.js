const restaurantsContainer = document.getElementById("restaurants");

function displayRestaurants(restaurants) {
    restaurantsContainer.innerHTML = "";

    if (restaurants.length === 0) {
        restaurantsContainer.textContent = "No restaurants found.";
        return;
    }

    restaurants.forEach((restaurant) => {
        const card = document.createElement("div");
        card.classList.add("restaurant-card");

        const name = document.createElement("h3");
        name.textContent = restaurant.name;

        const category = document.createElement("p");
        category.textContent = restaurant.category_name;

        const address = document.createElement("p");
        address.textContent = restaurant.address;

        const button = document.createElement("a");
        button.textContent = "View Restaurant";
        button.href = `restaurant.html?id=${restaurant.id}`;

        card.appendChild(name);
        card.appendChild(category);
        card.appendChild(address);
        card.appendChild(button);

        restaurantsContainer.appendChild(card);
    });
}

async function loadRestaurants() {
    try {
        const response = await apiRequest("/restaurants");

        displayRestaurants(response.data.restaurants);

    } catch (error) {
        restaurantsContainer.textContent = error.message;
    }
}

async function loadCategories() {
    try {
        const response = await apiRequest("/categories");
        const categories = response.data;

        const categorySelect = document.getElementById("category");

        categories.forEach((category) => {
            const option = document.createElement("option");

            option.value = category.id;
            option.textContent = category.name;

            categorySelect.appendChild(option);
        });

    } catch (error) {
        console.error(error.message);
    }
}

async function filterRestaurants() {
    const search = document.getElementById("search").value.trim();
    const category = document.getElementById("category").value;

    const params = new URLSearchParams();

    if (search) {
        params.append("search", search);
    }

    if (category) {
        params.append("category", category);
    }

    try {
        const response = await apiRequest(
            `/restaurants?${params.toString()}`
        );

        displayRestaurants(response.data.restaurants);

    } catch (error) {
        restaurantsContainer.textContent = error.message;
    }
}

document
    .getElementById("search")
    .addEventListener("input", filterRestaurants);

document
    .getElementById("category")
    .addEventListener("change", filterRestaurants);

loadCategories();
loadRestaurants();