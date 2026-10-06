const restaurantsContainer = document.getElementById("restaurants");

async function loadRestaurants() {
    try {
        const response = await apiRequest("/restaurants");

        const restaurants = response.data.restaurants;

        restaurantsContainer.innerHTML = "";

        if (restaurants.length === 0) {
            restaurantsContainer.innerHTML = "<p>No restaurants found.</p>";
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

    } catch (error) {
        restaurantsContainer.textContent = error.message;
    }
}

loadRestaurants();