const restaurantsContainer = document.getElementById("restaurants");
let currentPage = 1;
let totalPages = 1;
const limit = 5;

function displayRestaurants(restaurants) {
    restaurantsContainer.innerHTML = "";

    if (restaurants.length === 0) {
        restaurantsContainer.textContent = "No restaurants found.";
        return;
    }

    restaurants.forEach((restaurant) => {
        const card = document.createElement("div");
        card.classList.add("restaurant-card");

        if (restaurant.image) {
            const image = document.createElement("img");

            image.src = restaurant.image;
            image.alt = restaurant.name;

            card.appendChild(image);
        }
        
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
    await filterRestaurants(1);
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

async function filterRestaurants(page = 1) {
    const search =
        document.getElementById("search").value.trim();

    const category =
        document.getElementById("category").value;

    const params = new URLSearchParams();

    params.append("page", page);
    params.append("limit", limit);

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

        currentPage =
            response.data.pagination.current_page;

        totalPages =
            response.data.pagination.total_pages;

        updatePagination();

    } catch (error) {
        restaurantsContainer.textContent = error.message;
    }
}



function loadNearbyRestaurants() {
    if (!navigator.geolocation) {
        restaurantsContainer.textContent =
            "Geolocation is not supported by your browser.";
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            try {
                const response = await apiRequest(
                    `/restaurants/nearby?latitude=${latitude}&longitude=${longitude}&radius=10`
                );

                displayRestaurants(response.data);

            } catch (error) {
                restaurantsContainer.textContent = error.message;
            }
        },

        () => {
            restaurantsContainer.textContent =
                "Location permission is required to use Near Me.";
        }
    );
}

function updatePagination() {
    document.getElementById("page-info").textContent =
        `Page ${currentPage} of ${totalPages}`;

    document.getElementById("previous-page").disabled =
        currentPage <= 1;

    document.getElementById("next-page").disabled =
        currentPage >= totalPages;
}


document
    .getElementById("search")
    .addEventListener("input", () => {
        filterRestaurants(1);
    });

document
    .getElementById("category")
    .addEventListener("change", () => {
        filterRestaurants(1);
    });
document
    .getElementById("near-me")
    .addEventListener("click", loadNearbyRestaurants);


document
    .getElementById("previous-page")
    .addEventListener("click", () => {
        if (currentPage > 1) {
            filterRestaurants(currentPage - 1);
        }
    });

document
    .getElementById("next-page")
    .addEventListener("click", () => {
        if (currentPage < totalPages) {
            filterRestaurants(currentPage + 1);
        }
    });

loadCategories();
loadRestaurants();