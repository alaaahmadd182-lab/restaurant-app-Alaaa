const detailsContainer =
    document.getElementById("restaurant-details");

const menuContainer =
    document.getElementById("menu");

const params = new URLSearchParams(window.location.search);
const restaurantId = params.get("id");


async function loadRestaurant() {
    if (!restaurantId) {
        detailsContainer.textContent = "Restaurant not found.";
        return;
    }

    try {
        const response = await apiRequest(
            `/restaurants/${restaurantId}`
        );

        const restaurant = response.data;

        const name = document.createElement("h2");
        name.textContent = restaurant.name;

        const category = document.createElement("p");
        category.textContent = restaurant.category_name;

        const description = document.createElement("p");
        description.textContent =
            restaurant.description || "No description available.";

        const address = document.createElement("p");
        address.textContent = restaurant.address;

        detailsContainer.appendChild(name);
        detailsContainer.appendChild(category);
        detailsContainer.appendChild(description);
        detailsContainer.appendChild(address);

    } catch (error) {
        detailsContainer.textContent = error.message;
    }
}


async function loadMenus() {
    try {
        const response = await apiRequest(
            `/restaurants/${restaurantId}/menus`
        );

        const menus = response.data;

        menuContainer.innerHTML = "";

        if (menus.length === 0) {
            menuContainer.textContent = "No menu available.";
            return;
        }

        for (const menu of menus) {
            const section = document.createElement("section");

            const title = document.createElement("h3");
            title.textContent = menu.name;

            section.appendChild(title);

            await loadMenuItems(menu.id, section);

            menuContainer.appendChild(section);
        }

    } catch (error) {
        menuContainer.textContent = error.message;
    }
}


async function loadMenuItems(menuId, section) {
    try {
        const response = await apiRequest(
            `/menus/${menuId}/items`
        );

        const items = response.data;

        if (items.length === 0) {
            const empty = document.createElement("p");
            empty.textContent = "No items available.";

            section.appendChild(empty);
            return;
        }

        items.forEach((item) => {
            const itemContainer = document.createElement("div");
            itemContainer.classList.add("menu-item");

            const name = document.createElement("h4");
            name.textContent = item.name;

            const description = document.createElement("p");
            description.textContent =
                item.description || "No description.";

            const price = document.createElement("p");
            price.textContent =
                `$${Number(item.price).toFixed(2)}`;

            itemContainer.appendChild(name);
            itemContainer.appendChild(description);
            itemContainer.appendChild(price);

            section.appendChild(itemContainer);
        });

    } catch (error) {
        const message = document.createElement("p");
        message.textContent = error.message;

        section.appendChild(message);
    }
}


loadRestaurant();
loadMenus();